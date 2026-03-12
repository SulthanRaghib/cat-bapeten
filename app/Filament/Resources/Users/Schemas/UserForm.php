<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('User Information'))
                    ->description(__('Complete the user data below. Password only needs to be filled when adding a new user or when changing it.'))
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Full Name'))
                            ->validationAttribute('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('Email Address'))
                            ->validationAttribute('Alamat Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('nip')
                            ->label('NIP')
                            ->validationAttribute('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Employee ID Number — must be unique.')),

                        Select::make('role')
                            ->label(__('Role'))
                            ->validationAttribute('Peran')
                            ->options(static function (): array {
                                $labels = [
                                    'super_admin' => 'Super Admin',
                                    'admin'       => 'Administrator',
                                    'observer'    => __('Exam Observer'),
                                    'user'        => __('Exam Participant'),
                                ];

                                /** @var \App\Models\User|null $currentUser */
                                $currentUser = Auth::user();
                                $isSuperAdmin = $currentUser instanceof \App\Models\User
                                    && $currentUser->hasRole('super_admin');

                                // Load Spatie roles, filter super_admin jika bukan super_admin
                                $options = Role::query()
                                    ->where('guard_name', 'web')
                                    ->when(! $isSuperAdmin, fn($q) => $q->where('name', '!=', 'super_admin'))
                                    ->pluck('name')
                                    ->mapWithKeys(fn(string $name): array => [
                                        $name => $labels[$name] ?? ucwords(str_replace('_', ' ', $name)),
                                    ])
                                    ->toArray();

                                // 'user' bukan Spatie role, tambahkan manual di akhir
                                $options['user'] = __('Exam Participant');

                                return $options;
                            })
                            ->default('user')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->helperText(__('Super Admin: full access. Administrator: manage exams. Observer: monitoring only. Participant: take exams.')),

                        DateTimePicker::make('email_verified_at')
                            ->label(__('Email Verification Date'))
                            ->nullable()
                            ->helperText(__('Leave empty if email is not yet verified.')),

                        TextInput::make('password')
                            ->label('Password')
                            ->validationAttribute('Kata Sandi')
                            ->validationMessages([
                                'required' => 'Kata sandi wajib diisi saat menambahkan pengguna baru.',
                            ])
                            ->password()
                            ->revealable()
                            ->dehydrated(fn(?string $state) => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->helperText(__('Leave empty if you do not want to change the password.')),
                    ])->columns(2),
            ]);
    }
}
