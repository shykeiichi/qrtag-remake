<?php

namespace App\Http\Controllers;

use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    function updateUser(Request $request, $userId)
    {
        // Kolla om användare är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || !$_SESSION['qrtag']['is_admin'])
        {
            return view('pages.home');
        }

        $username = $request->post('username');

        // Lite äcklig kod för att kolla om vi ska ta bort användaren istället för att uppdatera
        // HTML har en konstig spec
        if(isset($_POST['delete']))
        {
            DB::table('event_tags')
                ->where('user_id', $userId)
                ->delete();

            DB::table('event_tags')
                ->where('target_id', $userId)
                ->delete();

            DB::table('event_users')
                ->where('user_id', $userId)
                ->delete();

            DB::table('event_users')
                ->where('target_id', $userId)
                ->delete();

            DB::table('users')
                ->where('id', $userId)
                ->delete();

            return redirect("/admin/users?success=Användare $username har tagits bort");
        }

        // Ta datan och kolla så att alla gavs
        $displayName = $request->post('display_name');
        $class = $request->post('class');
        $isAdmin = boolval($request->post('is_admin') == "on");

        if(!isset($username) || !isset($displayName) || !isset($class) || !isset($isAdmin))
        {
            return redirect("/admin/users?error=Åtminstånde en nödvändig data punkt har inte getts");
        }

        $targetTimezone = new DateTimeZone('Europe/Stockholm');

        $currentTime = Date::now($targetTimezone);

        // Uppdatera användaren
        DB::table('users')
        ->where('id', $userId)
        ->update([
            'username' => $username,
            'display_name' => $displayName,
            'class' => $class,
            'is_admin' => intval($isAdmin),
            'updated_at' => $currentTime->toDateTimeString()
        ]);

        return redirect("/admin/users?success=Användare $username har uppdaterats");
    }

    function store(Request $request)
    {
        // Kolla om användare är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || !$_SESSION['qrtag']['is_admin'])
        {
            return view('pages.home');
        }

        // Ta datan och kolla så att alla gavs
        $username = $request->post('username');
        $displayName = $request->post('display_name');
        $class = $request->post('class');
        $isAdmin = boolval($request->post('is_admin') == "on");

        if(!isset($username) || !isset($displayName) || !isset($class) || !isset($isAdmin))
        {
            return redirect("/admin/users?error=Åtminstånde en nödvändig data punkt har inte getts");
        }

        // Kolla så att en användare inte redan finns
        if(!is_null(DB::table('users')->where('username', $username)->first()))
        {
            return redirect("/admin/users?error=Användare med användarnamnet finns redan");
        }

        // skaå användaren
        DB::table('users')
        ->insert([
            'username' => $username,
            'display_name' => $displayName,
            'class' => $class,
            'is_admin' => intval($isAdmin)
        ]);

        return redirect("/admin/users?success=Användare $username har skapats");
    }
    
    function tag(Request $request)
    {
        $secret = $request->post('secret') ?? $_GET['secret'] ?? null;

        if(is_null($secret))
        {
            return "Ingen kod angiven";
        }


        $ongoingEvent = DB::table('events')
            ->select('id', 'start_date', 'end_date', 'name')
            ->where('start_date', '<=', now())
            ->where('end_date', '>', now())
            ->orderBy('start_date', 'asc')
            ->first();
        
        if(is_null($ongoingEvent))
        {
            return "Inget event är igång för tillfället";
        }

        $user = DB::table('event_users')->where([
            ['user_id', $_SESSION['qrtag']['id']],
            ['event_id', $ongoingEvent->id]
        ])->first();

        if(!$user->is_alive)
        {
            return "Du är inte vid liv i spelet";
        }

        $target = DB::table('event_users')
        ->join('users', 'users.id', '=', 'user_id')
        ->where([
            ['user_id', $user->target_id],
            ['event_id', $ongoingEvent->id]
        ])->first(['display_name', 'user_id', 'target_id', 'secret']);

        if($target->secret != $secret)
        {
            return redirect('/?error=Du taggade fel person');
        }

        DB::table('event_users')->where([
            ['user_id', $target->user_id],
            ['event_id', $ongoingEvent->id]
        ])->update([
            'is_alive' => false
        ]);

        DB::table('event_users')->where([
            ['user_id', $user->user_id],
            ['event_id', $ongoingEvent->id]
        ])->update([
            'target_id' => $target->target_id
        ]);

        DB::table('event_tags')->insert([
            'user_id' => $user->user_id,
            'target_id' => $target->user_id,
            'event_id' => $ongoingEvent->id
        ]);

        $user = DB::table('event_users')
        ->join('users', 'users.id', '=', 'user_id')
        ->where([
            ['user_id', $_SESSION['qrtag']['id']],
            ['event_id', $ongoingEvent->id]
        ])->first(['display_name', 'user_id', 'target_id']);

        if(isset($_ENV['DISCORD_WEBHOOK']) && $_ENV['DISCORD_WEBHOOK'] != "")
        {
            $playersLeft = DB::table('event_users')->where('event_id', $ongoingEvent->id)->where('is_alive', true)->count();
            if($user->user_id == $user->target_id || $playersLeft === 1)
            {
                DB::table('events')->where('id', $ongoingEvent->id)->update([
                    'winner' => $user->user_id
                ]);

                $message = $user->display_name . " kullade " . $target->display_name . " och vann därmed " . $ongoingEvent->name . "! Grattis!";
            } else 
            {
                $message = $user->display_name . " kullade " . $target->display_name . "!\nNu är det $playersLeft spelare kvar.";
            }

            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded",
                    'method'  => 'POST',
                    'content' => http_build_query(array('content' => $message))
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($_ENV['DISCORD_WEBHOOK'], false, $context);
        }

        return redirect('/');
    }

    function alive(Request $request, $userId)
    {
        $ongoingEvent = DB::table('events')
            ->select('id', 'start_date', 'end_date')
            ->where('start_date', '<=', now())
            ->where('end_date', '>', now())
            ->orderBy('start_date', 'asc')
            ->first();

        if(is_null($ongoingEvent))
        {
            return "Inget event är igång för tillfället";
        }

        $user = DB::table('event_users')->where([
            ['user_id', $userId],
            ['event_id', $ongoingEvent->id]
        ])->first();

        return response()->json([
            "alive" => $user->is_alive
        ]);
    }
}
