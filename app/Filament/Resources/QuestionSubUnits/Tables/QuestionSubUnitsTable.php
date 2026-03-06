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
                    ->label('Nama Sub Unit')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('questionUnit.name')
                    ->label('Unit Soal')
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
                    ->label('Soal')
                    ->counts('questions')
                    ->icon('heroicon-m-document-text')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('question_unit_id')
                    ->label('Unit Soal')
                    ->relationship('questionUnit', 'name')
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Edit Sub Unit')
                        ->icon('heroicon-m-pencil-square'),
                    DeleteAction::make()
                        ->label('Hapus Sub Unit')
                        ->icon('heroicon-m-trash')
                        ->modalHeading('Hapus Sub Unit Soal?')
                        ->modalDescription('Menghapus sub unit ini juga akan menghapus seluruh soal yang terkait. Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->label('Aksi')
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->emptyStateHeading('Belum ada sub unit soal')
            ->emptyStateDescription('Tambahkan sub unit sebagai klasifikasi lebih rinci dalam unit soal.')
            ->emptyStateIcon('heroicon-o-folder-open');
    }
}
