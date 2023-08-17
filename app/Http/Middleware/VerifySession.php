<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerifySession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        session_start();

        // Kolla om användaren har en session
        if(!isset($_SESSION['qrtag']['id'])) 
        {
            return $next($request);
        }

        $sessionId = $_SESSION['qrtag']['id'];

        $user = DB::table('users')
        ->where('id', $sessionId)
        ->first();

        // Resetta sessionen om användaren inte finns i databasen
        // Den här händer bara om användaren tagits bort ur databasen efter att personen loggat in
        if(is_null($user))
        {
            $_SESSION = [];
            return $next($request);
        }

        // Sätt sessionen om användaren finns
        $_SESSION['qrtag']['id'] = $user->id;
        $_SESSION['qrtag']['username'] = $user->username;
        $_SESSION['qrtag']['is_admin'] = $user->is_admin;
        $_SESSION['qrtag']['name'] = $user->display_name;
        $_SESSION['qrtag']['class'] = $user->class;

        return $next($request);
    }
}
