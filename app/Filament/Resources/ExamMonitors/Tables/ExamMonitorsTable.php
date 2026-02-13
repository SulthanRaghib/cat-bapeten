<?php

namespace App\Filament\Resources\ExamMonitors\Tables;

use App\Models\ExamSession;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamMonitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Peserta')
                    ->description(fn(ExamSession $record): string => 'NIP: ' . ($record->user->nip ?? '-'))
                    ->searchable(['name', 'nip']),
                TextColumn::make('user.nip')
                    ->label('NIP')
                    ->copyable()
                    ->copyMessage('NIP disalin!')
                    ->copyMessageDuration(2000)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('examParticipant.token')
                    ->label('Token')
                    ->copyable()
                    ->copyMessage('Token disalin!')
                    ->copyMessageDuration(2000)
                    ->weight('bold')
                    ->color('success'),
                TextColumn::make('examPackage.title')
                    ->label('Paket Ujian')
                    ->limit(30),
                TextColumn::make('started_at')
                    ->label('Waktu Mulai')
                    ->dateTime('H:i'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ongoing' => 'success',
                        'paused' => 'warning',
                        'completed' => 'info',
                        'terminated' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'ongoing' => 'Berjalan',
                        'paused' => 'Dijeda',
                        'completed' => 'Selesai',
                        'terminated' => 'Dihentikan',
                        default => $state,
                    }),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (ExamSession $record): string {
                        // Total questions assigned (from metadata)
                        $meta = $record->answers_meta ?? [];
                        $total = is_array($meta) ? count($meta) : 0;

                        if ($total === 0) return '0%';

                        // Count answers that are not null/empty
                        $answeredCount = $record->answers()->whereNotNull('answer')->count();

                        $percentage = ($answeredCount / $total) * 100;
                        return round($percentage) . '%';
                    }),
                TextColumn::make('violation_count')
                    ->label('Pelanggaran')
                    ->state(fn(ExamSession $record) => \App\Models\ExamActivityLog::where('exam_session_id', $record->id)->whereIn('severity', ['warning', 'danger', 'critical'])->count())
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        $state > 5 => 'danger',
                        $state > 0 => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('pantau')
                    ->label('Pantau')
                    ->icon('heroicon-o-eye')
                    ->modalWidth('7xl')
                    ->modalContent(fn($record) => $record ? view('filament.resources.exam-monitors.modal-content', ['record' => $record]) : null)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->slideOver(),
            ])
            ->recordUrl(null)
            ->emptyStateHeading('Tidak ada peserta yang sedang ujian')
            ->emptyStateDescription('Saat ini tidak ada sesi ujian yang berstatus ongoing atau paused.');
    }
}
