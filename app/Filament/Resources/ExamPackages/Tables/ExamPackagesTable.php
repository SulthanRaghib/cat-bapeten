<?php

namespace App\Filament\Resources\ExamPackages\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->recordClasses(
                fn($record): string =>
                $record->examType?->evaluation_method === 'weighted'
                    ? 'border-s-[3px] border-violet-500 dark:border-violet-400'
                    : 'border-s-[3px] border-info-400 dark:border-info-500'
            )
            ->columns([
                TextColumn::make('title')
                    ->label(__('Exam Package Title'))
                    ->description(
                        fn($record): string =>
                        'KKM: ' . ($record->passing_grade ?? '—')
                            . ' · Durasi: ' . ($record->duration_minutes ?? '—') . ' menit'
                    )
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),

                TextColumn::make('examType.name')
                    ->label(__('Exam Type'))
                    ->badge()
                    ->icon(fn($record): string => match ($record->examType?->evaluation_method) {
                        'correct_wrong' => 'heroicon-m-check-badge',
                        'weighted'      => 'heroicon-m-chart-bar',
                        default         => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn($record): string => match ($record->examType?->evaluation_method) {
                        'correct_wrong' => 'info',
                        'weighted'      => 'primary',
                        default         => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label(__('Exam Schedule'))
                    ->description(
                        fn($record): string =>
                        $record->start_time ? $record->start_time->format('H:i') . ' WIB' : '—'
                    )
                    ->date('d M Y')
                    ->icon('heroicon-m-calendar-days')
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label(__('Status'))
                    ->icon(fn(bool $state): string => $state ? 'heroicon-m-bolt' : 'heroicon-m-pause-circle')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? __('Active') : __('Inactive'))
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('Edit Exam Package'))
                        ->icon('heroicon-m-pencil-square'),

                    Action::make('edit_questions')
                        ->label(__('Edit Questions'))
                        ->icon('heroicon-m-rectangle-stack')
                        ->url(fn($record) => url("/admin/exam-packages/{$record->id}/edit?relation=0")),

                    Action::make('edit_konfigurasi_nab_dan_kelulusan')
                        ->label(__('Edit NAB & Pass Config'))
                        ->icon('heroicon-m-cog-6-tooth')
                        ->url(fn($record) => url("/admin/exam-packages/{$record->id}/edit?relation=1"))
                        ->visible(fn($record) => $record->examType?->evaluation_method === 'weighted'),

                    Action::make('edit_participants')
                        ->label(__('Edit Participants'))
                        ->icon('heroicon-m-users')
                        ->url(fn($record) => url("/admin/exam-packages/{$record->id}/edit?relation=2")),

                    Action::make('activate')
                        ->label(__('Activate Package'))
                        ->icon('heroicon-m-bolt')
                        ->visible(fn($record) => ! $record->is_active)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('Activate exam package?'))
                        ->modalDescription(__('Participants will be able to see and take this exam package once activated.'))
                        ->modalSubmitActionLabel(__('Yes, Activate'))
                        ->action(fn($record) => $record->update(['is_active' => true]))
                        ->after(fn() => Notification::make()
                            ->title(__('Exam package activated'))
                            ->success()
                            ->send()),

                    Action::make('deactivate')
                        ->label(__('Deactivate Package'))
                        ->icon('heroicon-m-pause-circle')
                        ->visible(fn($record) => $record->is_active)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('Deactivate exam package?'))
                        ->modalDescription(__('Participants will not be able to take this package until you reactivate it.'))
                        ->modalSubmitActionLabel(__('Yes, Deactivate'))
                        ->action(fn($record) => $record->update(['is_active' => false]))
                        ->after(fn() => Notification::make()
                            ->title(__('Exam package deactivated'))
                            ->warning()
                            ->send()),
                ])
                    ->label(__('Action Group'))
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected Exam Packages'))
                        ->modalHeading(__('Delete Selected Exam Packages'))
                        ->modalDescription(__('Are you sure you want to delete the selected exam packages? This action cannot be undone.'))
                        ->modalSubmitActionLabel(__('Yes, Delete')),
                ])->label(__('Bulk Actions')),
            ])
            ->emptyStateHeading(__('No exam packages yet'))
            ->emptyStateDescription(__('Create a new exam package to start managing questions and participants.'))
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }
}
