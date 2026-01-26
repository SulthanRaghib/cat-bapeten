<?php

namespace App\Filament\Resources\ExamPackages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ExamPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Paket Ujian')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe Paket Ujian')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'technical' => 'info',
                        'structural' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('passing_grade')
                    ->label('Nilai Kelulusan')
                    ->sortable(),

                TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->formatStateUsing(fn(int $state): string => "{$state} Menit")
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
