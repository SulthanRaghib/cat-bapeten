<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionSubUnits\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionSubUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordUrl(null)
            ->defaultSort('name')
            ->recordClasses(
                fn($record): string =>
                $record->questionUnit?->examType?->evaluation_method === 'weighted'
                    ? 'border-s-[3px] border-violet-500 dark:border-violet-400'
                    : 'border-s-[3px] border-info-400 dark:border-info-500'
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('Sub Unit Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('questionUnit.name')
                    ->label(__('Question Unit'))
                    ->description(
                        fn($record): string =>
                        $record->questionUnit?->examType?->name ?? '\u2014'
                    )
                    ->badge()
                    ->color(fn($record): string => match ($record->questionUnit?->examType?->evaluation_method) {
                        'correct_wrong' => 'info',
                        'weighted'      => 'primary',
                        default         => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label(__('Questions'))
                    ->counts('questions')
                    ->icon('heroicon-m-document-text')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('question_unit_id')
                    ->label(__('Question Unit'))
                    ->relationship('questionUnit', 'name')
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('Edit Sub Unit'))
                        ->icon('heroicon-m-pencil-square'),
                    DeleteAction::make()
                        ->label(__('Delete Sub Unit'))
                        ->icon('heroicon-m-trash')
                        ->modalHeading(__('Delete Question Sub Unit?'))
                        ->modalDescription(__('Deleting this sub unit will also delete all related questions. This action cannot be undone.'))
                        ->modalSubmitActionLabel(__('Yes, Delete')),
                ])
                    ->label(__('Action Group'))
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->emptyStateHeading(__('No question sub units yet'))
            ->emptyStateDescription(__('Add sub units as a more detailed classification within a question unit.'))
            ->emptyStateIcon('heroicon-o-folder-open');
    }
}
