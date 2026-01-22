<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class CustomLogin extends BaseLogin
{
    protected string $view = 'filament.auth.custom-login';
}
