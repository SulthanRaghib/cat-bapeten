<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamTypes\Tables;

use App\Models\ExamType;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Tipe')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('evaluation_method')
                    ->label('Metode Evaluasi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'correct_wrong' => 'info',
                        'weighted' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'correct_wrong' => 'Benar / Salah',
                        'weighted' => 'Berbobot',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Jumlah Soal')
                    ->counts('questions')
                    ->sortable(),

                TextColumn::make('exam_packages_count')
                    ->label('Jumlah Paket')
                    ->counts('examPackages')
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
            ->defaultSort('name');
    }
}
