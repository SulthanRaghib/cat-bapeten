<?php

namespace App\Filament\Resources\ExamParticipants\Tables;

use App\Models\ExamParticipant;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExamParticipantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('examPackage.title')
                    ->label(__('Exam Package'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Participant Name'))
                    ->description(fn(ExamParticipant $record): string => __('NIP: :nip', ['nip' => $record->user->nip ?? '—']))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('user.nip')
                    ->label(__('NIP'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('NIP Copied!'))
                    ->copyMessageDuration(2000)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.email')
                    ->label(__('Email'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('Email Copied!'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('token')
                    ->label(__('Access Token'))
                    ->description(__('Click to copy'))
                    ->copyable()
                    ->copyMessage(__('Access Token Copied!'))
                    ->copyMessageDuration(2000)
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status_label')
                    ->label(__('Status'))
                    ->badge()
                    ->icon(fn(ExamParticipant $record): string => $record->status_icon)
                    ->color(fn(ExamParticipant $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('score')
                    ->label(__('Latest Score'))
                    ->state(fn(ExamParticipant $record) => $record->examSessions()->latest()->first()?->total_score ?? '—')
                    ->icon('heroicon-m-academic-cap')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('finished_at')
                    ->label(__('Finished At'))
                    ->state(fn(ExamParticipant $record) => $record->examSessions()->latest()->first()?->finished_at?->format('d M Y H:i') ?? '—')
                    ->icon('heroicon-m-calendar-days')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Registered At'))
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam_package_id')
                    ->relationship('examPackage', 'title')
                    ->searchable()
                    ->preload()
                    ->label(__('Exam Package')),

                Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Inactive'),
                    ])
                    ->label(__('Access Status')),

                Tables\Filters\Filter::make('finished_at')
                    ->label(__('Completion Date'))
                    ->schema([
                        DatePicker::make('finished_from')->label(__('From Date')),
                        DatePicker::make('finished_until')->label(__('Until Date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['finished_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('finished_at', '>=', $date),
                            )
                            ->when(
                                $data['finished_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('finished_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('Edit'))
                        ->icon('heroicon-m-pencil-square')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading(__('Edit Exam Participant'))
                        ->modalWidth('md'),

                    Action::make('reset_attempt')
                        ->label(__('Reset Exam'))
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (ExamParticipant $record): void {
                            // Delete all sessions which will allow the user to start fresh
                            $record->examSessions()->delete();

                            // Set status active agar bisa ujian ulang
                            $record->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('Exam Reset & Status Activated'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('Reset Exam Data'))
                        ->modalDescription(__('WARNING: This will delete all answers and exam history for this participant. The participant must start over. Continue?')),

                    Action::make('delete')
                        ->label(__('Delete Participant'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (ExamParticipant $record): void {
                            $record->delete();

                            Notification::make()
                                ->title(__('Participant Deleted'))
                                ->success()
                                ->send();
                        })
                        ->modalHeading(__('Delete Participant'))
                        ->modalDescription(__('Are you sure you want to delete this participant? This action cannot be undone.')),
                ])
                    ->label(__('Action Group'))
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected Participants'))
                        ->modalHeading(__('Delete Selected Participants'))
                        ->modalDescription(__('Are you sure you want to delete the selected participants? This action cannot be undone.'))
                        ->modalSubmitActionLabel(__('Yes, Delete')),
                ])->label(__('Bulk Actions')),
            ])
            ->emptyStateHeading(__('No participants registered yet'))
            ->emptyStateDescription(__('Add exam participants to provide access to available exam packages.'))
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
