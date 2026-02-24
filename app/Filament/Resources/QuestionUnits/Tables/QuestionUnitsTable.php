<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Unit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('examType.name')
                    ->label('Tipe Ujian')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('sub_units_count')
                    ->label('Jumlah Sub Unit')
                    ->counts('subUnits')
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Jumlah Soal')
                    ->counts('questions')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
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
            ->defaultSort('name');
    }
}
