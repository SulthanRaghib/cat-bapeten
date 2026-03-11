<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 403 / AuthorizationException for non-Livewire (direct URL) requests
        // Livewire AJAX requests are handled by FilamentExceptionHandlerServiceProvider
        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            if (! $request->is('admin*') || $request->header('X-Livewire')) {
                return null;
            }

            $fallback = url()->previous() ?: filament()->getUrl();

            // Avoid infinite redirect loop
            if (rtrim($fallback, '/') === rtrim($request->url(), '/')) {
                $fallback = filament()->getUrl();
            }

            // Push notification into session using the exact format Filament expects
            session()->push('filament.notifications', [
                'id'        => uniqid('notif_', true),
                'actions'   => [],
                'body'      => 'Anda tidak memiliki izin untuk mengakses halaman tersebut.',
                'color'     => 'warning',
                'duration'  => 8000,
                'icon'      => 'heroicon-o-shield-exclamation',
                'iconColor' => 'warning',
                'status'    => 'warning',
                'title'     => 'Akses Ditolak',
                'view'      => null,
                'viewData'  => [],
            ]);

            return redirect($fallback);
        });
    })->create();
