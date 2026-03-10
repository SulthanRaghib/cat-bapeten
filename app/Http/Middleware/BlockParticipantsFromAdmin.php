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

            // Hanya super_admin, admin, observer yang boleh akses panel admin
            $isPanel = in_array($user->role, ['super_admin', 'admin', 'observer'], true);

            if (!$isPanel) {
                // If they are trying to access admin panel routes
                if ($request->is('admin*')) {
                    return redirect('/ujian');
                }
            }
        }

        return $next($request);
    }
}
