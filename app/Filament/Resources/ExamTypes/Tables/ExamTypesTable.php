<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamTypes\Tables;

use App\Models\ExamType;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordUrl(null)
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Exam Type Name'))
                    ->description(fn(ExamType $record): string => __('Code: :code', ['code' => $record->code]))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('evaluation_method')
                    ->label(__('Evaluation Method'))
                    ->badge()
                    ->icon(fn(string $state): string => match ($state) {
                        'correct_wrong' => 'heroicon-m-check-badge',
                        'weighted'      => 'heroicon-m-chart-bar',
                        default         => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'correct_wrong' => 'info',
                        'weighted'      => 'primary',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'correct_wrong' => __('Correct / Incorrect'),
                        'weighted'      => __('Weighted'),
                        default         => $state,
                    })
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label(__('Questions Count'))
                    ->counts('questions')
                    ->icon('heroicon-m-document-text')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('exam_packages_count')
                    ->label(__('Packages Count'))
                    ->counts('examPackages')
                    ->icon('heroicon-m-rectangle-stack')
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
            ->recordActions([
                EditAction::make()
                    ->label(__('Edit Exam Type'))
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->emptyStateHeading(__('No exam types yet'))
            ->emptyStateDescription(__('Add an exam type such as Technical or Mansoskul to start managing exam packages.'))
            ->emptyStateIcon('heroicon-o-academic-cap');
    }
}
