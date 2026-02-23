<?php

namespace App\Filament\Resources\ExamPackages\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
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

                TextColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(
                        fn(bool $state): string => $state
                            ? 'Aktif — Peserta bisa mengikuti ujian'
                            : 'Nonaktif — Paket ditutup untuk peserta'
                    )
                    ->icon(fn(bool $state): string => $state ? 'heroicon-m-bolt' : 'heroicon-m-pause-circle')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray'),

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

                    Action::make('activate')
                        ->label('Aktifkan Paket')
                        ->icon('heroicon-m-bolt')
                        ->visible(fn($record) => ! $record->is_active)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan paket ujian?')
                        ->modalDescription('Peserta akan dapat melihat dan mengerjakan paket ujian ini setelah diaktifkan.')
                        ->modalSubmitActionLabel('Ya, Aktifkan')
                        ->action(fn($record) => $record->update(['is_active' => true]))
                        ->after(fn() => Notification::make()
                            ->title('Paket ujian diaktifkan')
                            ->success()
                            ->send()),

                    Action::make('deactivate')
                        ->label('Nonaktifkan Paket')
                        ->icon('heroicon-m-pause-circle')
                        ->visible(fn($record) => $record->is_active)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan paket ujian?')
                        ->modalDescription('Peserta tidak akan dapat mengerjakan paket ini sampai Anda mengaktifkannya kembali.')
                        ->modalSubmitActionLabel('Ya, Nonaktifkan')
                        ->action(fn($record) => $record->update(['is_active' => false]))
                        ->after(fn() => Notification::make()
                            ->title('Paket ujian dinonaktifkan')
                            ->warning()
                            ->send()),
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
                ])->label('Tindakan Massal'),
            ]);
    }
}
