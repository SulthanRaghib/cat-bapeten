<?php

namespace App\Filament\Resources\ExamPackages\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->recordClasses(
                fn($record): string =>
                $record->examType?->evaluation_method === 'weighted'
                    ? 'border-s-[3px] border-violet-500 dark:border-violet-400'
                    : 'border-s-[3px] border-info-400 dark:border-info-500'
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Paket Ujian')
                    ->description(
                        fn($record): string =>
                        'KKM: ' . ($record->passing_grade ?? '—')
                            . ' · Durasi: ' . ($record->duration_minutes ?? '—') . ' menit'
                    )
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),

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

                TextColumn::make('start_time')
                    ->label('Jadwal Ujian')
                    ->description(
                        fn($record): string =>
                        $record->start_time ? $record->start_time->format('H:i') . ' WIB' : '—'
                    )
                    ->date('d M Y')
                    ->icon('heroicon-m-calendar-days')
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->icon(fn(bool $state): string => $state ? 'heroicon-m-bolt' : 'heroicon-m-pause-circle')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->date('d M Y')
                    ->sortable()
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

                    Action::make('edit_konfigurasi_nab_dan_kelulusan')
                        ->label('Edit Konfigurasi NAB & Kelulusan')
                        ->icon('heroicon-m-cog-6-tooth')
                        ->url(fn($record) => url("/admin/exam-packages/{$record->id}/edit?relation=1"))
                        ->visible(fn($record) => $record->examType?->evaluation_method === 'weighted'),

                    Action::make('edit_participants')
                        ->label('Edit Peserta Ujian')
                        ->icon('heroicon-m-users')
                        ->url(fn($record) => url("/admin/exam-packages/{$record->id}/edit?relation=2")),

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
            ])
            ->emptyStateHeading('Belum ada paket ujian')
            ->emptyStateDescription('Buat paket ujian baru untuk mulai mengelola soal dan peserta.')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }
}
