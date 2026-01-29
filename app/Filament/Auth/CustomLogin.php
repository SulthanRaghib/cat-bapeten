<?php

namespace App\Filament\Auth;

use App\Models\ExamParticipant;
use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Component;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    protected string $view = 'filament.auth.custom-login';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                $this->getLoginIdFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getLoginIdFormComponent(): Component
    {
        return TextInput::make('login_id')
            ->label('Email / NIP')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password / Token Ujian')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label(__('Ingat Saya'));
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $data = $this->form->getState();
        } catch (ValidationException $e) {
            $this->form->validate();
            throw $e;
        }

        $loginId = $data['login_id'];
        $password = $data['password'];
        $remember = $data['remember'] ?? false;

        // Check if login_id looks like an email
        $isEmail = filter_var($loginId, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            // LOGIC A: Admin/Standard Login (Email + Password)
            if (!Filament::auth()->attempt([
                'email' => $loginId,
                'password' => $password,
            ], $remember)) {
                $this->throwFailureValidationException();
            }
        } else {
            // LOGIC B: Participant Login (NIP + Token)
            $user = User::where('nip', $loginId)->first();
            $isAuthenticated = false;

            if ($user) {
                // Check if there is an active exam participation with this token
                $participant = ExamParticipant::where('user_id', $user->id)
                    ->where('token', $password) // Token is case-sensitive usually, matches database
                    ->where('is_active', true)
                    ->first();

                if ($participant) {
                    Filament::auth()->login($user, $remember);
                    session(['auth_mode' => 'participant']); // Flag session for redirection
                    $isAuthenticated = true;
                }
            }

            if (! $isAuthenticated) {
                $this->throwFailureValidationException();
            }
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['login_id'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login_id' => __('Mohon periksa kembali kredensial Anda dan coba lagi.'),
        ]);
    }
}
