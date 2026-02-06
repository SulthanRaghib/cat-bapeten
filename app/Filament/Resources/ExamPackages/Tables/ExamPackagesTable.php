<?php

namespace App\Filament\Resources\ExamPackages\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Size;
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
                        default => $state,
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
                ActionGroup::make([
                    EditAction::make()
                        ->label('Edit Paket Ujian')
                        ->icon('heroicon-m-pencil-square'),

                    Action::make('edit_questions')
                        ->label('Edit Soal Ujian')
                        ->icon('heroicon-m-rectangle-stack')
                        ->url(fn($record) => url("/admin/exam-packages/{$record->id}/edit?relation=0")),

                    Action::make('edit_participants')
                        ->label('Edit Peserta Ujian')
                        ->icon('heroicon-m-users')
                        ->url(fn($record) => url("/admin/exam-packages/{$record->id}/edit?relation=1")),
                ])
                    ->label('Aksi')
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Paket Ujian Terpilih')
                        ->modalHeading('Hapus Paket Ujian Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus paket ujian yang dipilih ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ]),
            ]);
    }
}
