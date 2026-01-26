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
                    ->label('Judul Ujian')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'technical' => 'Teknis (Benar/Salah)',
                        'structural' => 'Struktural (Bobot Nilai)',
                    })
                    ->colors([
                        'info' => 'technical',
                        'warning' => 'structural',
                    ]),

                TextColumn::make('passing_grade')
                    ->label('Nilai Kelulusan')
                    ->sortable(),

                TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->formatStateUsing(fn(int $state): string => "{$state} Menit")
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Status Aktif'),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
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
