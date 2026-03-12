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
            ->striped()
            ->poll('5s')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('user.name')
                    ->label('Peserta')
                    ->description(fn(ExamSession $record): string => 'NIP: ' . ($record->user->nip ?? '—'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('examParticipant.token')
                    ->label('Token')
                    ->copyable()
                    ->copyMessage('Token disalin!')
                    ->copyMessageDuration(2000)
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('examPackage.title')
                    ->label('Paket Ujian')
                    ->description(
                        fn(ExamSession $record): string =>
                        $record->examPackage?->examType?->name ?? '—'
                    )
                    ->limit(35)
                    ->wrap(),

                TextColumn::make('started_at')
                    ->label('Waktu Mulai')
                    ->description(
                        fn(ExamSession $record): string =>
                        $record->started_at ? $record->started_at->format('d M Y') : '—'
                    )
                    ->dateTime('H:i')
                    ->icon('heroicon-m-clock'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn(string $state): string => match ($state) {
                        'ongoing'    => 'heroicon-m-play-circle',
                        'paused'     => 'heroicon-m-pause-circle',
                        'completed'  => 'heroicon-m-check-circle',
                        'terminated' => 'heroicon-m-x-circle',
                        default      => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'ongoing'    => 'success',
                        'paused'     => 'warning',
                        'completed'  => 'info',
                        'terminated' => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'ongoing'    => 'Sedang Berjalan',
                        'paused'     => 'Dijeda',
                        'completed'  => 'Selesai',
                        'terminated' => 'Dihentikan',
                        default      => $state,
                    }),

                TextColumn::make('progress')
                    ->label('Progres')
                    ->state(function (ExamSession $record): string {
                        $meta     = $record->answers_meta ?? [];
                        $total    = is_array($meta) ? count($meta) : 0;
                        if ($total === 0) return '0 / 0';
                        $answered = $record->answers()->whereNotNull('answer')->count();
                        $pct      = round(($answered / $total) * 100);
                        return "{$answered}/{$total} ({$pct}%)";
                    })
                    ->description('soal dijawab')
                    ->icon('heroicon-m-chart-bar')
                    ->color('gray'),

                TextColumn::make('violation_count')
                    ->label('Pelanggaran')
                    ->state(
                        fn(ExamSession $record) =>
                        \App\Models\ExamActivityLog::where('exam_session_id', $record->id)
                            ->whereIn('severity', ['warning', 'danger', 'critical'])
                            ->count()
                    )
                    ->icon('heroicon-m-exclamation-triangle')
                    ->badge()
                    ->alignCenter()
                    ->color(fn(string $state): string => match (true) {
                        (int) $state > 5 => 'danger',
                        (int) $state > 0 => 'warning',
                        default          => 'success',
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
            ->emptyStateHeading('Tidak ada peserta yang sedang ujian')
            ->emptyStateDescription('Saat ini tidak ada sesi ujian yang aktif. Halaman ini diperbarui otomatis setiap 5 detik.')
            ->emptyStateIcon('heroicon-o-computer-desktop');
    }
}
