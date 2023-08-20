<?php

use App\Http\Middleware\VerifySession;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/a', function () {
    session_start();

    $_SESSION['qrtag']['username'] = "22widi";
    $_SESSION['qrtag']['id'] = 1;
    $_SESSION['qrtag']['is_admin'] = 1;
    $_SESSION['qrtag']['name'] = "Willem Dinkelspiel";
    $_SESSION['qrtag']['class'] = "TE22B";
    
    return redirect("/");
});

Route::get('/b', function () {
    session_start();

    $_SESSION['qrtag']['username'] = "22hust";
    $_SESSION['qrtag']['id'] = 2;
    $_SESSION['qrtag']['is_admin'] = 0;
    $_SESSION['qrtag']['name'] = "Hugo Stjerngren";
    $_SESSION['qrtag']['class'] = "TE22D";
    
    return redirect("/");
});


Route::get('/', function () {
    if(!isset($_SESSION['qrtag']))
    {
        return view('pages.login');
    }

    $nearestEvent = DB::table('events')
        ->select('start_date', 'id', 'name')
        ->where('start_date', '>=', now())
        ->orderBy('start_date', 'asc')
        ->first();

    $ongoingEvent = DB::table('events')
        ->select('id', 'start_date', 'end_date', 'name', 'winner')
        ->where('start_date', '<=', now())
        ->where('end_date', '>', now())
        ->orderBy('start_date', 'asc')
        ->first();

    if(!is_null($ongoingEvent))
    {
        $user = DB::table('event_users')->where([
            ['event_id', $ongoingEvent->id],
            ['user_id', $_SESSION['qrtag']['id']]
        ])->first();

        if(is_null($user))
        {
            return view('pages.loggedin.ongoingviewer');
        } else 
        {
            if(!is_null($ongoingEvent->winner))
            {
                $winnerName = DB::table('users')->where('id', $ongoingEvent->winner)->first();
                return view('pages.loggedin.win', [
                    "user" => $user,
                    "eventName" => $ongoingEvent->name,
                    "winner" => $winnerName->display_name,
                    'error' => $_GET['error'] ?? null
                ]);
            } else 
            {
                $target = DB::selectOne("
                SELECT *
                FROM event_users
                LEFT JOIN users ON users.id = event_users.user_id
                WHERE
                event_users.user_id = $user->target_id
                ");
                return view('pages.loggedin.ongoingplayer', [
                    "target" => $target,
                    "user" => $user,
                    'error' => $_GET['error'] ?? null
                ]);
            }
        }
    } else if(!is_null($nearestEvent))
    {
        $user = DB::table('event_users')->where([
            ['event_id', $nearestEvent->id],
            ['user_id', $_SESSION['qrtag']['id']]
        ])->first();

        $targetTimezone = new DateTimeZone('Europe/Stockholm');

        // return var_dump($nearestEvent);

        $startDate = Date::createFromFormat('Y-m-d H:i:s', $nearestEvent->start_date, $targetTimezone);

        if(is_null($user))
        {
            return view('pages.loggedin.waitforstart', [
                'startDate' => $startDate->timestamp,
                'eventName' => $nearestEvent->name,
                'joined' => false
            ]);
        } else
        {
            return view('pages.loggedin.waitforstart', [
                'startDate' => $startDate->timestamp,
                'eventName' => $nearestEvent->name,
                'joined' => true
            ]);
        }
    } else 
    {
        return view('pages.loggedin.noevent');
    }
})->middleware(VerifySession::class);

Route::get('/scoreboard', function () {
    $ongoingEvent = DB::table('events')
        ->select('id', 'start_date', 'end_date', 'name')
        ->where('start_date', '<=', now())
        ->where('end_date', '>', now())
        ->orderBy('start_date', 'asc')
        ->first();

    if(is_null($ongoingEvent))
    {
        return view('pages.scoreboard', [
            'ongoing' => false
        ]);
    }

    // Jag vet at man borde använda prepared statments istället för att lägga datan i koden men orkar inte för det funka inte första gången
    $players = DB::select("SELECT
            `users`.`display_name`,
            `event_tags`.`user_id`,
            COALESCE(COUNT(`event_tags`.`event_id`), 0) AS tag_count,
            `event_users`.`is_alive`
        FROM
            `users`
        INNER JOIN `event_users` ON `users`.`id` = `event_users`.`user_id`
        LEFT JOIN `event_tags` ON `users`.`id` = `event_tags`.`user_id` AND `event_tags`.`event_id` = " . $ongoingEvent->id . "
        WHERE
            `event_users`.`event_id` = " . $ongoingEvent->id . "
        GROUP BY
            `users`.`id`,
            `users`.`display_name`,
            `event_users`.`is_alive`,
            `event_tags`.`user_id`
        ORDER BY
            tag_count DESC
        LIMIT 15;
    ");

    $tags = DB::select("SELECT
        user.display_name AS user,
        target.display_name AS target,
        event_tags.timestamp
        FROM event_tags
        LEFT JOIN users user ON user.id = event_tags.user_id
        LEFT JOIN users target ON target.id = event_tags.target_id
        WHERE event_tags.event_id = " . $ongoingEvent->id . "
        ORDER BY event_tags.timestamp DESC
        LIMIT 20
        ");

    return view('pages.scoreboard', [
        'eventName' => $ongoingEvent->name,
        'ongoing' => true,
        'players' => $players,
        'tags' => $tags
    ]);
})->middleware(VerifySession::class);;

Route::prefix('admin')->group(function() {
    Route::get('/', function () {
        return redirect('/admin/events');
    })->middleware(VerifySession::class);

    Route::get('/events', function () {
        // Kolla om användaren inte är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || $_SESSION['qrtag']['is_admin'] == 0)
        {
            return redirect('/');
        }

        $events = DB::table('events')
            ->select('events.id', 'events.name', 'events.start_date', 'events.end_date', DB::raw('COUNT(event_users.event_id) as user_count'))
            ->leftJoin('event_users', 'events.id', '=', 'event_users.event_id')
            ->groupBy('events.id', 'events.name', 'events.start_date', 'events.end_date')
            ->get();

        return view('pages.admin.events', [
            'events' => $events,
            'error' => $_GET['error'] ?? null,
            'success' => $_GET['success'] ?? null
        ]);
    })->middleware(VerifySession::class);

    Route::get('/events/{eventId}', function (Request $request, $eventId) {
        $participants = DB::select("
            SELECT 
                event_users.id as id,
                users.display_name as display_name,
                users.class as class,
                event_users.target_id,
                event_users.user_id,
                (SELECT display_name FROM users WHERE users.id = event_users.target_id) as target_display_name,
                event_users.secret,
                event_users.is_alive,
                COALESCE(COUNT(`event_tags`.`event_id`), 0) AS tag_count
            FROM event_users
            LEFT JOIN users ON event_users.user_id = users.id
            LEFT JOIN `event_tags` ON `users`.`id` = `event_tags`.`user_id` AND `event_tags`.`event_id` = " . $eventId . "
            WHERE event_users.event_id = " . $eventId . "
            GROUP BY id, display_name, class, target_id, user_id, target_display_name, secret, is_alive
        ");

        $event = DB::table('events')
            ->where('id', $eventId)
            ->first();

        return view('pages.admin.event', [
            'participants' => $participants,
            'eventId' => $eventId,
            'event' => $event,
            'error' => $_GET['error'] ?? null,
            'success' => $_GET['success'] ?? null
        ]);
    })->where('eventId', '[0-9]+')->middleware(VerifySession::class);

    Route::get('/users', function () {
        // Kolla om användaren inte är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || $_SESSION['qrtag']['is_admin'] === '0')
        {
            return redirect('/');
        }

        $users = DB::table('users')->select('id', 'username', 'display_name', 'class', 'is_admin', 'created_at')->get();

        return view('pages.admin.users', [
            'users' => $users,
            'error' => $_GET['error'] ?? null,
            'success' => $_GET['success'] ?? null
        ]);
    })->middleware(VerifySession::class);
});