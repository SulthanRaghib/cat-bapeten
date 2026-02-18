<?php

namespace App\Filament\Resources\ExamParticipants\Schemas;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ExamParticipantForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->columns(1)
            ->components([

                /* ===============================
                 * STEP 1 — PAKET UJIAN
                 * =============================== */
                Section::make('Paket Ujian')
                    ->description('Langkah 1: Pilih paket ujian.')
                    ->schema([
                        Select::make('exam_package_id')
                            ->label('Paket Ujian')
                            ->relationship('examPackage', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->placeholder('Pilih Paket Ujian...')
                            ->afterStateUpdated(fn(Set $set) => $set('user_id', null)),
                    ]),

                /* ===============================
                 * STEP 2 — PESERTA
                 * =============================== */
                Section::make('Peserta Ujian')
                    ->description('Langkah 2: Tentukan peserta ujian.')
                    ->visible(fn(Get $get) => filled($get('exam_package_id')))
                    ->schema([
                        Select::make('user_id')
                            ->label('Peserta')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->multiple(fn(string $operation) => $operation === 'create')
                            ->noOptionsMessage(
                                fn(Get $get) =>
                                $get('exam_package_id')
                                    ? 'Semua peserta untuk paket ini sudah terdaftar'
                                    : 'Pilih paket ujian terlebih dahulu'
                            )

                            /* 🔑 PENTING: SUPAYA EDIT TAMPIL NAMA */
                            ->getOptionLabelUsing(
                                fn($value): ?string =>
                                User::find($value)?->name
                            )

                            ->options(function (Get $get) {
                                $examPackageId = $get('exam_package_id');

                                if (! $examPackageId) {
                                    return [];
                                }

                                return User::query()
                                    ->where('role', 'user')
                                    ->whereDoesntHave(
                                        'examPackages',
                                        fn($q) =>
                                        $q->where('exam_packages.id', $examPackageId)
                                    )
                                    ->pluck('name', 'id');
                            })

                            ->helperText(
                                fn(string $operation) =>
                                $operation === 'create'
                                    ? 'Bisa memilih lebih dari satu peserta.'
                                    : 'Peserta tidak dapat diubah.'
                            ),
                    ]),

                /* ===============================
                 * STEP 3 — TOKEN & STATUS
                 * =============================== */
                Section::make('Token & Status Ujian')
                    ->description('Pengaturan teknis ujian.')
                    ->visibleOn('edit')
                    ->schema([
                        Grid::make(2)->schema([

                            TextInput::make('token')
                                ->label('Token Ujian')
                                ->disabled()
                                ->dehydrated()
                                ->suffixActions([

                                    /* === COPY TOKEN === */
                                    Action::make('copyToken')
                                        ->icon('heroicon-m-clipboard')
                                        ->color('gray')
                                        ->tooltip('Salin token ke clipboard')
                                        ->action(function ($state) {
                                            Notification::make()
                                                ->title('Token disalin')
                                                ->body("Token: {$state}")
                                                ->info()
                                                ->send();
                                        }),

                                    /* === RESET TOKEN === */
                                    Action::make('resetToken')
                                        ->icon('heroicon-m-arrow-path')
                                        ->color('warning')
                                        ->tooltip('Reset token ujian')
                                        ->requiresConfirmation()
                                        ->modalHeading('Reset Token Ujian')
                                        ->modalDescription(
                                            'Token lama akan dinonaktifkan dan diganti token baru. Peserta harus menggunakan token baru.'
                                        )
                                        ->action(function ($record, Set $set) {
                                            $newToken = strtoupper(Str::random(6));

                                            $record->update([
                                                'token' => $newToken,
                                            ]);

                                            $set('token', $newToken);

                                            Notification::make()
                                                ->title('Token berhasil diperbarui')
                                                ->body("Token baru: {$newToken}")
                                                ->success()
                                                ->persistent()
                                                ->send();
                                        }),
                                ]),

                            Toggle::make('is_active')
                                ->label(
                                    fn(Get $get) =>
                                    $get('is_active')
                                        ? 'Peserta AKTIF (Boleh Ikut Ujian)'
                                        : 'Peserta NONAKTIF (Diblokir)'
                                )
                                ->live() // ⬅️ WAJIB agar reactive
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText(
                                    fn(Get $get) =>
                                    $get('is_active')
                                        ? 'Peserta dapat login dan mengerjakan ujian.'
                                        : 'Peserta tidak dapat login atau mengerjakan ujian.'
                                )
                                ->afterStateUpdated(function (bool $state, $record) {
                                    // Update langsung ke database
                                    $record->update([
                                        'is_active' => $state,
                                    ]);

                                    // Feedback ke admin
                                    Notification::make()
                                        ->title(
                                            $state
                                                ? 'Peserta diaktifkan'
                                                : 'Peserta dinonaktifkan'
                                        )
                                        ->body(
                                            $state
                                                ? 'Peserta sekarang bisa mengikuti ujian.'
                                                : 'Peserta tidak dapat mengikuti ujian.'
                                        )
                                        ->color($state ? 'success' : 'danger')
                                        ->send();
                                }),

                        ]),

                        Placeholder::make('created_at')
                            ->label('Dibuat Pada')
                            ->content(
                                fn($record) =>
                                $record?->created_at?->translatedFormat('d F Y, H:i') ?? '-'
                            ),
                    ]),
            ]);
    }
}
