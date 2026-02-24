<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionSubUnits\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionSubUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Sub Unit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('questionUnit.name')
                    ->label('Unit Soal')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('questionUnit.examType.name')
                    ->label('Tipe Ujian')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Jumlah Soal')
                    ->counts('questions')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
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
            ->defaultSort('name');
    }
}
