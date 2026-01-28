<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): \Symfony\Component\HttpFoundation\Response|RedirectResponse|Redirector
    {
        // Redirection logic based on session flag or user role
        if (session('auth_mode') === 'participant') {
            return redirect()->to('/ujian');
        }

        // Default Filament redirection (Dashboard)
        return redirect()->intended(filament()->getUrl());
    }
}
