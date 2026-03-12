<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordUrl(null)
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Unit Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

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

                TextColumn::make('sub_units_count')
                    ->label(__('Sub Unit'))
                    ->counts('subUnits')
                    ->icon('heroicon-m-bars-3-bottom-left')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label(__('Questions'))
                    ->counts('questions')
                    ->icon('heroicon-m-document-text')
                    ->alignCenter()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('exam_type_id')
                    ->label(__('Exam Type'))
                    ->relationship('examType', 'name')
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('Edit Unit'))
                        ->icon('heroicon-m-pencil-square'),

                    Action::make('kelola_indikator')
                        ->label(__('Manage NAB Indicators'))
                        ->icon('heroicon-m-chart-bar')
                        ->color('primary')
                        ->url(fn($record) => url("/admin/question-units/{$record->id}/edit?relation=0"))
                        ->visible(fn($record): bool => $record->examType?->evaluation_method === 'weighted'),

                    DeleteAction::make()
                        ->label(__('Delete Unit'))
                        ->icon('heroicon-m-trash')
                        ->modalHeading(__('Delete Question Unit?'))
                        ->modalDescription(__('Deleting this unit will also delete all related sub units and questions. This action cannot be undone.'))
                        ->modalSubmitActionLabel(__('Yes, Delete')),
                ])
                    ->label(__('Action Group'))
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->emptyStateHeading(__('No question units yet'))
            ->emptyStateDescription(__('Add question units to group questions by competency.'))
            ->emptyStateIcon('heroicon-o-folder');
    }
}
