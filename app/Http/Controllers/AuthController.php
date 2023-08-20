<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    function login(Request $request)
    {
        session_start();

        // Skickar användaren till redan inloggad sidan om personen redan är inloggad 
        if(isset($_SESSION['qrtag']['id']))
        {
            return view('pages.auth.already_logged_in');
        }

        // Logga in eleven med skolans ldap server
        $username = explode('@', $request->post('username'))[0];
        $ldap = ldap_connect('ldaps://ad.ssis.nu');
        if($ldap === false) // Skicka personen till hem sidan om ldapen är nere
        {
            return view('pages.home', ['error' => 'Elev servern verkar vara nere. Kontakta ' . $_ENV['MAINTAINER_NAME'] . '!']);
        }
        $bind = ldap_bind($ldap, $username . "@ad.ssis.nu", $request->post('password'));

        if(!$bind)
        {
            return view('pages.home', ['error' => 'Ditt användarnamn eller lösenord var fel.']);
        }

        // Kolla om användaren redan finns i databasen och om den finns sätt sessionen till databas raden och återgå till hem
        $user = DB::table('users')->where('username', $username)->first(['id', 'display_name', 'is_admin', 'class']);

        if($user)
        {
            $_SESSION['qrtag']['username'] = $username;
            $_SESSION['qrtag']['id'] = $user['id'];
            $_SESSION['qrtag']['is_admin'] = $user['is_admin'];
            $_SESSION['qrtag']['name'] = $user['name'];
            $_SESSION['qrtag']['class'] = $user['class'];

            return redirect('/');
        }

        // Om användaren inte finns i databasen sök efter elev's elev information i ldapen 
        $search = ldap_search($ldap, "DC=ad,DC=ssis,DC=nu", "(sAMAccountName=" . $username . ")", array("cn", "givenName", "sn", "memberOf")) or die('ldap_search failed');
        $userInfo = ldap_get_entries($ldap, $search);

        if($userInfo['count'] == 0)
        {
            return view('pages.home', ['error' => 'Kunde inte hitta dig i elev servern. Är du inte en elev? Kontakta ' . $_ENV['MAINTAINER_NAME'] . ' om du vill ha tillgång.']);
        }
        $userInfo = $userInfo[0];

        // Skaffa elevens fulla namn och få elvens klass
        $name = $userInfo['givenname'][0] . ' ' . $userInfo['sn'][0];
        $class = 'Lärare';

        foreach($userInfo['memberof'] as $sg)
        {
            if(strpos($sg, 'OU=Klass') !== false) 
            {
                $class = substr($sg, 3, 5);
                break;
            }
        }

        // Skapa eleven i databasen        
        $userId = DB::table('users')->insertGetId([
            'username' => $username,
            'name' => $name,
            'class' => $class,
            'is_admin' => 0
        ]);

        // Skapa sessionen
        $_SESSION['qrtag']['id'] = $userId;
        $_SESSION['qrtag']['username'] = $username;
        $_SESSION['qrtag']['is_admin'] = 0;
        $_SESSION['qrtag']['name'] = $name;
        $_SESSION['qrtag']['class'] = $class;

        return redirect('/');
    }

    function logout(Request $request)
    {
        session_start();
        $_SESSION = [];
        return redirect('/');
    }
}
