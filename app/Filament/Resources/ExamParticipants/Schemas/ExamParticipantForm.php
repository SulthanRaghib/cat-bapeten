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
                Section::make(__('Exam Package'))
                    ->description(__('Step 1: Select the exam package.'))
                    ->schema([
                        Select::make('exam_package_id')
                            ->label(__('Exam Package'))
                            ->relationship('examPackage', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->placeholder(__('Select Exam Package...'))
                            ->afterStateUpdated(fn(Set $set) => $set('user_id', null)),
                    ]),

                /* ===============================
                 * STEP 2 — PESERTA
                 * =============================== */
                Section::make(__('Exam Participant'))
                    ->description(__('Step 2: Determine the exam participant.'))
                    ->visible(fn(Get $get) => filled($get('exam_package_id')))
                    ->schema([
                        Select::make('user_id')
                            ->label(__('Participant'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->multiple(fn(string $operation) => $operation === 'create')
                            ->noOptionsMessage(
                                fn(Get $get) =>
                                $get('exam_package_id')
                                    ? __('All participants for this package are already registered')
                                    : __('Select an exam package first')
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
                                    ? __('Can select more than one participant.')
                                    : __('Participant cannot be changed.')
                            ),
                    ]),

                /* ===============================
                 * STEP 3 — TOKEN & STATUS
                 * =============================== */
                Section::make(__('Token & Exam Status'))
                    ->description(__('Technical exam settings.'))
                    ->visibleOn('edit')
                    ->schema([
                        Grid::make(2)->schema([

                            TextInput::make('token')
                                ->label(__('Exam Token'))
                                ->disabled()
                                ->dehydrated()
                                ->suffixActions([

                                    /* === COPY TOKEN === */
                                    Action::make('copyToken')
                                        ->icon('heroicon-m-clipboard')
                                        ->color('gray')
                                        ->tooltip(__('Copy token to clipboard'))
                                        ->action(function ($state) {
                                            Notification::make()
                                                ->title(__('Token copied'))
                                                ->body(__('Token: :token', ['token' => $state]))
                                                ->info()
                                                ->send();
                                        }),

                                    /* === RESET TOKEN === */
                                    Action::make('resetToken')
                                        ->icon('heroicon-m-arrow-path')
                                        ->color('warning')
                                        ->tooltip(__('Reset exam token'))
                                        ->requiresConfirmation()
                                        ->modalHeading(__('Reset Exam Token'))
                                        ->modalDescription(
                                            __('The old token will be deactivated and replaced with a new token. The participant must use the new token.')
                                        )
                                        ->action(function ($record, Set $set) {
                                            $newToken = strtoupper(Str::random(6));

                                            $record->update([
                                                'token' => $newToken,
                                            ]);

                                            $set('token', $newToken);

                                            Notification::make()
                                                ->title(__('Token successfully updated'))
                                                ->body(__('New token: :token', ['token' => $newToken]))
                                                ->success()
                                                ->persistent()
                                                ->send();
                                        }),
                                ]),

                            Toggle::make('is_active')
                                ->label(
                                    fn(Get $get) =>
                                    $get('is_active')
                                        ? __('Participant ACTIVE (Can Take Exam)')
                                        : __('Participant INACTIVE (Blocked)')
                                )
                                ->live()
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText(
                                    fn(Get $get) =>
                                    $get('is_active')
                                        ? __('Participant can log in and take the exam.')
                                        : __('Participant cannot log in or take the exam.')
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
                                                ? __('Participant activated')
                                                : __('Participant deactivated')
                                        )
                                        ->body(
                                            $state
                                                ? __('Participant can now take the exam.')
                                                : __('Participant cannot take the exam.')
                                        )
                                        ->color($state ? 'success' : 'danger')
                                        ->send();
                                }),

                        ]),

                        Placeholder::make('created_at')
                            ->label(__('Created At'))
                            ->content(
                                fn($record) =>
                                $record?->created_at?->translatedFormat('d F Y, H:i') ?? '-'
                            ),
                    ]),
            ]);
    }
}
