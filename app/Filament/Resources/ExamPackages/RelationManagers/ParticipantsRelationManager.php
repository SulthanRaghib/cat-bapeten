<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Models\ExamParticipant;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Size;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = null;
    protected static ?string $modelLabel = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Exam Participants');
    }

    public static function getModelLabel(): string
    {
        return __('Participant');
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('token')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Participant Name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nip')
                    ->label(__('NIP'))
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('token')
                    ->label(__('Access Token'))
                    ->weight('bold')
                    ->color(Color::Amber)
                    ->copyable()
                    ->copyMessage(__('Token Copied!'))
                    ->description(__('Share this token with the participant')),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->getStateUsing(function ($record) {
                        // Pivot pada relasi participants sudah berupa model ExamParticipant,
                        $participant = $record->pivot instanceof ExamParticipant
                            ? $record->pivot
                            : null;

                        return $participant?->status_label ?? __('Inactive');
                    })
                    ->color(fn(string $state): string => match ($state) {
                        __('Inactive') => 'danger',
                        __('Not Started') => 'gray',
                        __('In Progress') => 'warning',
                        __('Awaiting Selection') => 'info',
                        __('Completed') => 'success',
                        __('Terminated') => 'danger',
                        default => 'gray',
                    })
                    ->icon(function ($state, $record) {
                        $participant = $record->pivot instanceof ExamParticipant
                            ? $record->pivot
                            : null;

                        return $participant?->status_icon ?? 'heroicon-m-question-mark-circle';
                    }),
            ])
            ->headerActions([
                // filter hanya role user yang bisa ditambahkan (bukan admin) dan belum menjadi peserta
                AttachAction::make()
                    ->label(__('Add Exam Participant'))
                    ->icon('heroicon-m-user-plus')
                    ->color('primary')
                    ->modalHeading(__('Select Exam Participant'))
                    ->modalSubmitActionLabel(function (array $data): string {
                        $count = count(array_filter((array) ($data['selectedPeserta'] ?? [])));

                        return "Tambahkan";
                    })
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->modalAutofocus()
                    ->fillForm(fn(): array => [
                        'selectedPeserta' => [],
                        'recordId' => [],
                    ])
                    ->schema([
                        Select::make('selectedPeserta')
                            ->label(__('Pilih Peserta Ujian'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->searchPrompt(__('Ketik untuk mencari...'))
                            ->searchDebounce(300)
                            ->placeholder(__('Pilih salah satu opsi'))
                            ->live()
                            ->options(fn(): array => $this->getAvailableParticipantOptions())
                            ->helperText(__('Pilihan tersimpan sementara sampai tombol Tambahkan diklik.')),

                        Hidden::make('recordId')
                            ->dehydrateStateUsing(fn(Get $get): array => array_map('intval', Arr::wrap($get('selectedPeserta')))),
                    ])
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('toggle_active')
                        ->label(fn($record) => $record->pivot->is_active ? __('Deactivate') : __('Activate'))
                        ->icon(fn($record) => $record->pivot->is_active ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                        ->color(fn($record) => $record->pivot->is_active ? 'danger' : 'success')
                        // Hanya tampil jika peserta belum pernah mengerjakan (tidak punya sesi)
                        ->visible(function ($record) {
                            $participant = $record->pivot instanceof ExamParticipant
                                ? $record->pivot
                                : ExamParticipant::find($record->pivot->id);

                            if (!$participant) {
                                return false;
                            }

                            return !$participant->examSessions()->exists();
                        })
                        ->action(function ($record) {
                            $record->pivot->update([
                                'is_active' => !$record->pivot->is_active
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('Change Participant Access Status'))
                        ->modalDescription(__('Are you sure you want to change the exam access status of this participant?')),

                    Action::make('reset_exam')
                        ->label(__('Reset Exam'))
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        // Tampil hanya jika sudah pernah ujian (punya sesi)
                        ->visible(function ($record) {
                            $participant = $record->pivot instanceof ExamParticipant
                                ? $record->pivot
                                : ExamParticipant::find($record->pivot->id);

                            return $participant && $participant->examSessions()->exists();
                        })
                        ->action(function ($record) {
                            $participant = ExamParticipant::find($record->pivot->id);
                            if ($participant) {
                                $participant->examSessions()->delete();
                                $record->pivot->update(['is_active' => true]); // Ensure active after reset

                                \Filament\Notifications\Notification::make()
                                    ->title(__('Exam Reset'))
                                    ->success()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('Reset Exam Data?'))
                        ->modalDescription(__('WARNING: This will delete all answers and exam history for this participant. The participant must start over. Continue?')),

                    DetachAction::make()
                        ->label(__('Remove Participant'))
                        ->icon('heroicon-m-trash')
                        ->modalHeading(__('Remove Participant from Exam'))
                        ->modalDescription(__('Are you sure you want to remove this participant from the exam package?'))
                        ->modalSubmitActionLabel(__('Yes, Remove')),
                ])
                    ->label(__('Action Group'))
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label(__('Remove Selected Participants'))
                        ->icon('heroicon-m-trash')
                        ->modalHeading(__('Remove Participants from Exam'))
                        ->modalDescription(__('Are you sure you want to remove the selected participants from the exam package?'))
                        ->modalSubmitActionLabel(__('Yes, Remove')),
                ])->label(__('Bulk Actions')),
            ]);
    }

    protected function getAvailableParticipantOptions(): array
    {
        $examPackage = $this->getOwnerRecord();
        $existingParticipantIds = $examPackage->participants()->pluck('users.id')->map(fn($id) => (int) $id)->all();

        return User::query()
            ->where('role', 'user')
            ->when($existingParticipantIds !== [], fn($query) => $query->whereNotIn('users.id', $existingParticipantIds))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->mapWithKeys(fn($name, $id) => [(int) $id => $name])
            ->all();
    }
}
