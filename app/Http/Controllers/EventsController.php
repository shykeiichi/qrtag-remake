<?php

namespace App\Http\Controllers;

use DateTimeZone;
use Illuminate\Support\Facades\Date;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

function generateSecret() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = substr(str_shuffle($characters), 0, 5);
 
    return $randomString;
}

class EventsController extends Controller
{
    function store(Request $request) // Skapa ett evenet
    {
        // Kolla om eleven är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || !$_SESSION['qrtag']['is_admin'])
        {
            return view('pages.home');
        }


        // Få nödvändig data från post requesten
        $name = $request->post('name');
        $startDateText = $request->post('start_date');
        $endDateText = $request->post('end_date');

        // Skicka en error om ett event med namnet redan finns
        if(!is_null(DB::table('events')->where('name', $name)->first()))
        {
            return redirect('/admin/events?error=Det finns redan ett event med det här namnet');
        }

        // Skapa laravel carbon objekt av html datum strängen som gavs av requesten
        $targetTimezone = new DateTimeZone('Europe/Stockholm');

        $startDate = Date::createFromFormat('Y-m-d\TH:i', $startDateText, $targetTimezone);
        $endDate = Date::createFromFormat('Y-m-d\TH:i', $endDateText, $targetTimezone);

        // Kolla om det finns ett event som överskrider eventet som försöks skapas
        $overlapExists = DB::table('events')
        ->whereRaw(':start_date < end_date AND :end_date > start_date', [
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString(),
        ])
        ->exists();

        if($overlapExists)
        {
            return redirect('/admin/events?error=Ett event kan inte överskrida ett annat event');
        }

        // Skapa event
        DB::table('events')->insert([
            'name' => $name,
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString()
        ]);

        return redirect('/admin/events?success=Skapade ett event');
    }

    function delete(Request $request, $eventId)
    {
          // Kolla om eleven är admin
          if(!isset($_SESSION['qrtag']['is_admin']) || !$_SESSION['qrtag']['is_admin'])
          {
              return view('pages.home');
          }

          DB::table('event_users')->where('event_id', $eventId)->delete();
          DB::table('events')->delete($eventId);

          return redirect('/admin/events?success=Tog bort ett event');
    }

    function giveTargets(Request $request, $eventId)
    {
        // Kolla om eleven är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || !$_SESSION['qrtag']['is_admin'])
        {
            return view('pages.home');
        }
        
        $isShuffledCorrectly = false;

        $entries = DB::table('event_users')
            ->where('event_id', $eventId)
            ->count();

        if($entries < 2)
        {
            return redirect("/admin/events/$eventId?error=Du kan inte tilldela mål till ett event med mindre än 2 spelare.");
        }

        do {
            $userIds = DB::table('event_users')
                ->where('event_id', $eventId)
                ->pluck('user_id')
                ->toArray();

            $shuffledUserIds = $userIds;
            shuffle($shuffledUserIds);

            $targetIds = array_combine($userIds, $shuffledUserIds);

            $updatedCount = 0;
            foreach ($targetIds as $userId => $targetId) {
                DB::table('event_users')
                    ->where('event_id', $eventId)
                    ->where('user_id', $userId)
                    ->update(['target_id' => $targetId, 'secret' => generateSecret()]);

                $updatedCount++;
            }

            $isShuffledCorrectly = true;
            foreach(DB::table('event_users')->where('event_id', $eventId)->get() as $user)
            {
                if($user->target_id == $user->user_id)
                {
                    $isShuffledCorrectly = false;
                }
            }
        } while(!$isShuffledCorrectly);

        DB::table('events')->where('id', $eventId)->update(['targets_assigned' => true]);

        return redirect("/admin/events/$eventId?success=Tilldelade alla mål");
    }

    function join(Request $request)
    {
        $nearestEvent = DB::table('events')
            ->select('start_date', 'id')
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->first();

        if(is_null($nearestEvent))
        {
            return "Inget event är igång för tillfället";
        }

        $ongoingEvent = DB::table('events')
            ->select('id', 'start_date', 'end_date')
            ->where('start_date', '<=', now())
            ->where('end_date', '>', now())
            ->orderBy('start_date', 'asc')
            ->first();

        if(!is_null($ongoingEvent))
        {
            return "Du kan inte gå med ett event som är igång";
        }

        $user = DB::table('event_users')->where([
            ['user_id', $_SESSION['qrtag']['id']],
            ['event_id', $nearestEvent->id]
        ])->first();

        if(!is_null($user))
        {
            return "Du har redan gått med i eventet";
        }

        DB::table('event_users')->insert([
            'user_id' => $_SESSION['qrtag']['id'],
            'event_id' => $nearestEvent->id,
            'is_alive' => 1,
            'secret' => '',
            'target_id' => 1
        ]);

        return redirect('/');
    }

    function leave(Request $request)
    {
        $nearestEvent = DB::table('events')
            ->select('start_date', 'id')
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->first();

        if(is_null($nearestEvent))
        {
            return "Inget event är igång för tillfället";
        }

        $ongoingEvent = DB::table('events')
            ->select('id', 'start_date', 'end_date')
            ->where('start_date', '<=', now())
            ->where('end_date', '>', now())
            ->orderBy('start_date', 'asc')
            ->first();

        if(!is_null($ongoingEvent))
        {
            return "Du kan inte lämna ett event som är igång";
        }

        $user = DB::table('event_users')->where([
            ['user_id', $_SESSION['qrtag']['id']],
            ['event_id', $nearestEvent->id]
        ])->first();

        if(is_null($user))
        {
            return "Du är inte med i eventet";
        }

        DB::table('event_users')->where([
            ['user_id', $_SESSION['qrtag']['id']],
            ['event_id', $nearestEvent->id]
        ])->delete();

        return redirect('/');
    }

    function update(Request $request, $eventId)
    {
        // Kolla om eleven är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || !$_SESSION['qrtag']['is_admin'])
        {
            return view('pages.home');
        }


        // Få nödvändig data från post requesten
        $name = $request->post('name');
        $startDateText = $request->post('start_date');
        $endDateText = $request->post('end_date');

        // Skicka en error om ett event med namnet inte finns
        if(is_null(DB::table('events')->where('id', $eventId)->first()))
        {
            return redirect('/admin/events?error=Eventet du försöker redigera finns inte');
        }

        // Skapa laravel carbon objekt av html datum strängen som gavs av requesten
        $targetTimezone = new DateTimeZone('Europe/Stockholm');

        $startDate = Date::createFromFormat('Y-m-d\TH:i', $startDateText, $targetTimezone);
        $endDate = Date::createFromFormat('Y-m-d\TH:i', $endDateText, $targetTimezone);

        // Kolla om det finns ett event som överskrider eventet som försöks skapas
        $overlapExists = DB::table('events')
            ->whereRaw('? < end_date AND ? > start_date', [
                $startDate->toDateTimeString(),
                $endDate->toDateTimeString(),
            ])
            ->where('id', '!=', $eventId)
            ->exists();

        if($overlapExists)
        {
            return redirect('/admin/events?error=Redigeringen överskrider ett annat event');
        }

        // Skapa event
        DB::table('events')->update([
            'name' => $name,
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString()
        ]);

        return redirect('/admin/events?success=Redigerade ' . $name);
    }

    function addPlayer(Request $request, $eventId)
    {
        // Kolla om eleven är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || !$_SESSION['qrtag']['is_admin'])
        {
            return view('pages.home');
        }

        $username = $request->post('username');

        // Kolla så att användaren finns
        $user = DB::table('users')->where('username', $username)->first();

        if(is_null($user))
        {
            return redirect("/admin/events/$eventId?error=Användarnamnet du försökte lägga till finns inte");
        }

        // Kolla så att man inte lägger in en användare som redan finns
        $alreadyJoined = DB::table('event_users')->where([['user_id', $user->id], ['event_id', $eventId]])->first();

        if(!is_null($alreadyJoined))
        {
            return redirect("/admin/events/$eventId?error=Användaren du försöker lägga till finns redan");
        }

        // Kolla så att det finns en användare att stoppa in vid
        $randomUser = DB::table('event_users')
            ->where('is_alive', true)
            ->where('event_id', $eventId)
            ->orderBy(DB::raw('RAND()'))
            ->take(1)
            ->first();

        if(is_null($randomUser))
        {
            return redirect("/admin/events/$eventId?error=Det finns ingen stans att lägga till användaren. Försök igen senare");
        }

        // Stoppa in användaren vid randomuser
        DB::table('event_users')->insert([
            'user_id' => $user->id,
            'event_id' => $eventId,
            'is_alive' => 1,
            'target_id' => $randomUser->target_id,
            'secret' => generateSecret()
        ]);

        DB::table('event_users')
            ->where('id', $randomUser->id)
            ->update([
                'target_id' => $user->id
            ]);

        return redirect("/admin/events/$eventId?success=Du la till $username");
    }

    function reviveUser(Request $request, $eventId)
    {
        // Kolla om eleven är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || !$_SESSION['qrtag']['is_admin'])
        {
            return view('pages.home');
        }

        // Kolla så att useridt gavs
        $userId = $request->post('user_id');

        if(is_null($userId))
        {
            return redirect("/admin/events/$eventId?error=Inget userid gavs.");
        }

        // Kolla så att eventet finns och är igång
        $event = DB::table('events')
            ->select('id', 'start_date', 'end_date', 'name', 'winner')
            ->where('start_date', '<=', now())
            ->where('end_date', '>', now())
            ->where('id', $eventId)
            ->orderBy('start_date', 'asc')
            ->first();

        if(is_null($event))
        {
            return redirect("/admin/events/$eventId?error=Eventet måste existera och vara igång för att man ska kunna återuppliva.");
        }

        // Kolla så att spelaren finns
        $eventUser = DB::table('event_users')
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->first();

        if(is_null($eventUser))
        {
            return redirect("/admin/events/$eventId?error=Kunde $userId inte hitta en spelare med id:t i eventet.");
        }

        // Kolla så att man inte återupplivar en levande användaren
        if($eventUser->is_alive)
        {
            return redirect("/admin/events/$eventId?error=Spelaren är redan vid liv.");
        }

        // Hitta en spelare där man ska stoppa in den ny upplivade spelaren
        $randomUser = DB::table('event_users')
            ->where('is_alive', true)
            ->where('event_id', $eventId)
            ->where('user_id', '!=', $userId)
            ->orderBy(DB::raw('RAND()'))
            ->take(1)
            ->first();

        if(is_null($randomUser))
        {
            return redirect("/admin/events/$eventId?error=Det finns ingen stans att lägga till användaren. Försök igen senare");
        }

        // Återuppliva användaren i databasen
        DB::table('event_users')
            ->where([
                ['event_id', $eventId],
                ['user_id', $userId]
            ])
            ->update([
                'is_alive' => 1,
                'target_id' => $randomUser->target_id,
                'secret' => generateSecret()
            ]);

        // Sätt den random spelaren att ha den ny upplivade spelaren som target
        DB::table('event_users')
            ->where('id', $randomUser->id)
            ->update([
                'target_id' => $userId
            ]);

        // Eventuellt att man stänger av att någon har vunnit
        DB::table('events')->update([
            'winner' => null
        ]);

        return redirect("/admin/events/$eventId?success=Återupplivade användaren");
    }

    function reviveAll(Request $request, $eventId) {
        // Kolla om eleven är admin
        if(!isset($_SESSION['qrtag']['is_admin']) || !$_SESSION['qrtag']['is_admin'])
        {
            return view('pages.home');
        }
        
        $isShuffledCorrectly = false;

        $entries = DB::table('event_users')
            ->where('event_id', $eventId)
            ->count();

        if($entries < 2)
        {
            return redirect("/admin/events/$eventId?error=Du kan inte återuppliva alla användare till ett event med mindre än 2 spelare.");
        }

        // En loop som körs till alla spelare har en target som inte är dem själv
        // Jag vet att det här kan teoretiskt sätt krasha hela hemsidan men ibland är 100% säker att ge resultat bättre än 100% inte krasha
        do {
            // Få alla spelare och gör en kopia som är i en random ordning
            $userIds = DB::table('event_users')
                ->where('event_id', $eventId)
                ->pluck('user_id')
                ->toArray();

            $shuffledUserIds = $userIds;
            shuffle($shuffledUserIds);

            $targetIds = array_combine($userIds, $shuffledUserIds);

            // Sätt alla target ids och annan nödvändig daata
            $updatedCount = 0;
            foreach ($targetIds as $userId => $targetId) {
                DB::table('event_users')
                    ->where('event_id', $eventId)
                    ->where('user_id', $userId)
                    ->update(['target_id' => $targetId, 'secret' => generateSecret(), 'is_alive' => true]);

                $updatedCount++;
            }

            // Kolla om det inte finns några spelare där user_id == target_id annars kör om loopen
            $isShuffledCorrectly = true;
            foreach(DB::table('event_users')->where('event_id', $eventId)->get() as $user)
            {
                if($user->target_id == $user->user_id)
                {
                    $isShuffledCorrectly = false;
                }
            }
        } while(!$isShuffledCorrectly);

        // Skicka meddelande till discord webhooken om alla spelare har återupplivats för att inte skapa frågor senare om varför numrena ändras
        DB::table('events')->where('id', $eventId)->update(['winner' => null]);

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded",
                'method'  => 'POST',
                'content' => http_build_query(array('content' => "Alla elever har återupplivats!"))
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($_ENV['DISCORD_WEBHOOK'], false, $context);

        return redirect("/admin/events/$eventId?success=Återupplivade alla spelare");
    }
}
