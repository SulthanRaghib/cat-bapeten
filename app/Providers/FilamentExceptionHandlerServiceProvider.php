<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FilamentExceptionHandlerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * Registers a Livewire exception handler so that when a Filament action,
     * resource page, or relation manager throws AuthorizationException (403),
     * the user sees a friendly Indonesian warning notification instead of
     * a raw 403 error screen. The current page stays visible (no redirect)
     * since Livewire handles this as an AJAX update.
     */
    public function boot(): void
    {
        // Livewire v3 exception event: ($target, $e, $stopPropagation)
        // $target          = the Livewire component that threw
        // $e               = the Throwable
        // $stopPropagation = call this to prevent the exception from being re-thrown
        Livewire::listen('exception', function (mixed $target, \Throwable $e, \Closure $stopPropagation): void {
            if (! ($e instanceof AuthorizationException)) {
                return;
            }

            $stopPropagation(); // Suppress — do not bubble up to a 403 page

            Notification::make()
                ->warning()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk melakukan tindakan tersebut.')
                ->icon('heroicon-o-shield-exclamation')
                ->duration(8000)
                ->send();
        });
    }
}
