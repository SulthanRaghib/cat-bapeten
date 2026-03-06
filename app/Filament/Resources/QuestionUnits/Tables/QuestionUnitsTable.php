<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\Tables;

use Filament\Actions\EditAction;
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
            ->recordClasses(
                fn($record): string =>
                $record->examType?->evaluation_method === 'weighted'
                    ? 'border-s-[3px] border-violet-500 dark:border-violet-400'
                    : 'border-s-[3px] border-info-400 dark:border-info-500'
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Unit')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('examType.name')
                    ->label('Tipe Ujian')
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
                    ->label('Sub Unit')
                    ->counts('subUnits')
                    ->icon('heroicon-m-bars-3-bottom-left')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Soal')
                    ->counts('questions')
                    ->icon('heroicon-m-document-text')
                    ->alignCenter()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('exam_type_id')
                    ->label('Tipe Ujian')
                    ->relationship('examType', 'name')
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit Unit')
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->emptyStateHeading('Belum ada unit soal')
            ->emptyStateDescription('Tambahkan unit soal untuk mengelompokkan soal-soal berdasarkan kompetensi.')
            ->emptyStateIcon('heroicon-o-folder');
    }
}
