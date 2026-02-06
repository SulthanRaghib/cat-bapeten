<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Models\ExamParticipant;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    // Translasi Judul Tab
    protected static ?string $title = 'Peserta Ujian';
    protected static ?string $modelLabel = 'Peserta';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('token')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('token')
                    ->label('Token Akses')
                    ->weight('bold')
                    ->color(Color::Amber)
                    ->copyable()
                    ->copyMessage('Token disalin!')
                    ->description('Bagikan token ini ke peserta'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        // Pivot pada relasi participants sudah berupa model ExamParticipant,
                        $participant = $record->pivot instanceof ExamParticipant
                            ? $record->pivot
                            : null;

                        return $participant?->status_label ?? 'Nonaktif';
                    })
                    ->colors([
                        'danger' => 'Nonaktif',
                        'gray' => 'Belum Mengerjakan',
                        'warning' => 'Sedang Mengerjakan',
                        'success' => 'Selesai',
                    ])
                    ->icon(function ($state, $record) {
                        $participant = $record->pivot instanceof ExamParticipant
                            ? $record->pivot
                            : null;

                        return $participant?->status_icon ?? 'heroicon-m-question-mark-circle';
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Peserta')
                    ->color(Color::Amber)
                    ->modalHeading('Pilih Peserta Ujian')
                    ->modalSubmitActionLabel('Tambahkan')
                    ->preloadRecordSelect()
                    ->multiple() // Bisa pilih banyak sekaligus
                    ->recordSelectSearchColumns(['name', 'nip']),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('toggle_active')
                        ->label(fn($record) => $record->pivot->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn($record) => $record->pivot->is_active ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                        ->color(fn($record) => $record->pivot->is_active ? 'danger' : 'success')
                        // Hanya tampil jika peserta belum pernah mengerjakan (tidak punya sesi)
                        ->visible(function ($record) {
                            $participant = $record->pivot instanceof ExamParticipant
                                ? $record->pivot
                                : ExamParticipant::find($record->pivot->id);

                            if (!$participant) {
                                return false;
                            }

                            return !$participant->examSessions()->exists();
                        })
                        ->action(function ($record) {
                            $record->pivot->update([
                                'is_active' => !$record->pivot->is_active
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Ubah Status Akses Peserta')
                        ->modalDescription('Apakah Anda yakin ingin mengubah status akses ujian peserta ini?'),

                    Action::make('reset_exam')
                        ->label('Reset Ujian')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        // Tampil hanya jika sudah pernah ujian (punya sesi)
                        ->visible(function ($record) {
                            $participant = $record->pivot instanceof ExamParticipant
                                ? $record->pivot
                                : ExamParticipant::find($record->pivot->id);

                            return $participant && $participant->examSessions()->exists();
                        })
                        ->action(function ($record) {
                            $participant = ExamParticipant::find($record->pivot->id);
                            if ($participant) {
                                $participant->examSessions()->delete();
                                $record->pivot->update(['is_active' => true]); // Ensure active after reset

                                \Filament\Notifications\Notification::make()
                                    ->title('Ujian Direset')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Reset Data Ujian?')
                        ->modalDescription('PERHATIAN: Ini akan menghapus seluruh jawaban dan riwayat ujian peserta ini. Peserta harus memulai dari awal. Lanjutkan?'),

                    DetachAction::make()
                        ->label('Hapus Peserta')
                        ->modalHeading('Hapus Peserta dari Ujian')
                        ->modalDescription('Apakah Anda yakin ingin menghapus peserta ini dari paket ujian?')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Hapus Peserta Terpilih')
                        ->modalHeading('Hapus Peserta dari Ujian')
                        ->modalDescription('Apakah Anda yakin ingin menghapus peserta terpilih dari paket ujian?')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->label('Tindakan pada Peserta Terpilih'),
            ]);
    }
}
