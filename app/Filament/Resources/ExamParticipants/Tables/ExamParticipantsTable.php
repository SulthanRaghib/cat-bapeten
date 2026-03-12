<?php

namespace App\Filament\Resources\ExamParticipants\Tables;

use App\Models\ExamParticipant;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExamParticipantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('examPackage.title')
                    ->label('Paket Ujian')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->description(fn(ExamParticipant $record): string => 'NIP: ' . ($record->user->nip ?? '—'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('user.nip')
                    ->label('NIP')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('NIP Disalin!')
                    ->copyMessageDuration(2000)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email Disalin!')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('token')
                    ->label('Token Akses')
                    ->description('Klik untuk menyalin')
                    ->copyable()
                    ->copyMessage('Token Akses Disalin!')
                    ->copyMessageDuration(2000)
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->icon(fn(ExamParticipant $record): string => $record->status_icon)
                    ->color(fn(ExamParticipant $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('score')
                    ->label('Nilai Terakhir')
                    ->state(fn(ExamParticipant $record) => $record->examSessions()->latest()->first()?->total_score ?? '—')
                    ->icon('heroicon-m-academic-cap')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('finished_at')
                    ->label('Selesai Pada')
                    ->state(fn(ExamParticipant $record) => $record->examSessions()->latest()->first()?->finished_at?->format('d M Y H:i') ?? '—')
                    ->icon('heroicon-m-calendar-days')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam_package_id')
                    ->relationship('examPackage', 'title')
                    ->searchable()
                    ->preload()
                    ->label('Paket Ujian'),

                Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Nonaktif',
                    ])
                    ->label('Status Akses'),

                Tables\Filters\Filter::make('finished_at')
                    ->label('Tanggal Selesai')
                    ->schema([
                        DatePicker::make('finished_from')->label('Dari Tanggal'),
                        DatePicker::make('finished_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['finished_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('finished_at', '>=', $date),
                            )
                            ->when(
                                $data['finished_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('finished_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Edit')
                        ->icon('heroicon-m-pencil-square')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Edit Peserta Ujian')
                        ->modalWidth('md'),

                    Action::make('reset_attempt')
                        ->label('Reset Ujian')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (ExamParticipant $record): void {
                            // Delete all sessions which will allow the user to start fresh
                            $record->examSessions()->delete();

                            // Set status active agar bisa ujian ulang
                            $record->update(['is_active' => true]);

                            Notification::make()
                                ->title('Ujian Direset & Status Diaktifkan')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Reset Data Ujian')
                        ->modalDescription('PERHATIAN: Ini akan menghapus seluruh jawaban dan riwayat ujian peserta ini. Peserta harus memulai dari awal. Lanjutkan?'),

                    Action::make('delete')
                        ->label('Hapus Peserta')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (ExamParticipant $record): void {
                            $record->delete();

                            Notification::make()
                                ->title('Peserta Dihapus')
                                ->success()
                                ->send();
                        })
                        ->modalHeading('Hapus Peserta')
                        ->modalDescription('Apakah Anda yakin ingin menghapus peserta ini? Tindakan ini tidak dapat dibatalkan.'),
                ])
                    ->label('Aksi')
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Peserta Terpilih')
                        ->modalHeading('Hapus Peserta Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus peserta yang dipilih ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])->label('Tindakan Massal'),
            ])
            ->emptyStateHeading('Belum ada peserta terdaftar')
            ->emptyStateDescription('Tambahkan peserta ujian untuk memberikan akses ke paket ujian yang tersedia.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
