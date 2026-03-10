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
                Section::make('Informasi Pengguna')
                    ->description('Lengkapi data pengguna berikut. Password hanya perlu diisi saat menambahkan pengguna baru atau ingin menggantinya.')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Nomor Induk Pegawai — harus unik.'),

                        Select::make('role')
                            ->label('Peran')
                            ->options(static function (): array {
                                $labels = [
                                    'super_admin' => 'Super Admin',
                                    'admin'       => 'Administrator',
                                    'observer'    => 'Pengawas Ujian',
                                    'user'        => 'Peserta Ujian',
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
                                $options['user'] = 'Peserta Ujian';

                                return $options;
                            })
                            ->default('user')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->helperText('Super Admin: akses penuh. Administrator: kelola ujian. Pengawas: hanya monitoring. Peserta: ikut ujian.'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Tanggal Verifikasi Email')
                            ->nullable()
                            ->helperText('Kosongkan jika email belum diverifikasi.'),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn(?string $state) => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->helperText('Kosongkan jika tidak ingin mengubah password.'),
                    ])->columns(2),
            ]);
    }
}
