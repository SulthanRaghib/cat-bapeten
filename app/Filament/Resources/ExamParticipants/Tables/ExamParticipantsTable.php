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
use Illuminate\Support\HtmlString;

class ExamParticipantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('examPackage.title')
                    ->searchable()
                    ->sortable()
                    ->label('Paket Ujian'),

                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Peserta'),

                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.nip')
                    ->searchable()
                    ->label(new HtmlString(
                        'NIP<br><span class="text-xs text-gray-500">Klik NIP untuk menyalin</span>'
                    ))
                    ->copyable()
                    ->copyMessage('NIP Disalin!'),

                Tables\Columns\TextColumn::make('token')
                    ->label(new HtmlString(
                        'Token Akses<br><span class="text-xs text-gray-500">Klik token untuk menyalin</span>'
                    ))
                    ->copyable()
                    ->copyMessage('Token Akses Disalin!')
                    ->copyMessageDuration(2000)
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status_label')
                    ->badge()
                    ->color(fn(ExamParticipant $record): string => $record->status_color)
                    ->icon(fn(ExamParticipant $record): string => $record->status_icon)
                    ->label('Status'),

                Tables\Columns\TextColumn::make('score')
                    ->state(fn(ExamParticipant $record) => $record->examSessions()->latest()->first()?->total_score ?? '-')
                    ->label('Skor')
                    ->sortable(),

                Tables\Columns\TextColumn::make('finished_at')
                    ->state(fn(ExamParticipant $record) => $record->examSessions()->latest()->first()?->finished_at?->format('d M Y H:i') ?? '-')
                    ->label('Selesai Pada')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam_package_id')
                    ->relationship('examPackage', 'title')
                    ->searchable()
                    ->preload()
                    ->label('Filter berdasarkan Paket Ujian'),

                Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ])
                    ->label('Status Active'),

                Tables\Filters\Filter::make('finished_at')
                    ->schema([
                        DatePicker::make('finished_from'),
                        DatePicker::make('finished_until'),
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
                        ->icon('heroicon-m-pencil')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Edit Peserta Ujian')
                        ->modalWidth('md'),

                    Action::make('reset_attempt')
                        ->label('Reset Ujian')
                        ->icon('heroicon-o-arrow-path')
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
                        ->icon('heroicon-o-trash')
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
            ]);
    }
}
