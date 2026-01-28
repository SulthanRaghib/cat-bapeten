<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BlockParticipantsFromAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is strictly a Participant (not Admin)
            // Logic: Not role 'admin' AND does not have official email
            $isAdmin = $user->role === 'admin' || str_ends_with($user->email ?? '', '@bapeten.go.id');

            if (!$isAdmin) {
                // If they are trying to access admin panel routes
                if ($request->is('admin*')) {
                    return redirect('/ujian');
                }
            }
        }

        return $next($request);
    }
}
