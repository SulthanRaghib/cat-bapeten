<?php

namespace App\Filament\Resources\ExamResults\Tables;

use App\Filament\Actions\DownloadBapAction;
use App\Filament\Actions\ExportExamResultsHeaderAction;
use App\Models\ExamSession;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class ExamResultsTable
{
    /** Hitung durasi ujian (detik) menjadi string human-readable */
    private static function formatDuration(ExamSession $record): string
    {
        if (! $record->started_at || ! $record->finished_at) {
            return '-';
        }
        $total = (int) $record->started_at->diffInSeconds($record->finished_at);
        $h     = intdiv($total, 3600);
        $m     = intdiv($total % 3600, 60);
        $s     = $total % 60;

        if ($h > 0) return "{$h} jam {$m} menit {$s} detik";
        if ($m > 0) return "{$m} menit {$s} detik";
        return "{$s} detik";
    }

    /** Apakah peserta lulus? */
    private static function isLulus(ExamSession $record): bool
    {
        return ($record->total_score ?? 0) >= ($record->examPackage->passing_grade ?? 0);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultSort('finished_at', 'desc')
            ->columns([

                // ── 1. Peserta ───────────────────────────────────────────
                TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->description(fn(ExamSession $record): string => 'NIP: ' . ($record->user->nip ?? '-'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                // ── 2. Paket Ujian ───────────────────────────────────────
                TextColumn::make('examPackage.title')
                    ->label('Paket Ujian')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                // ── 3. Tanggal & Waktu Ujian ─────────────────────────────
                TextColumn::make('started_at')
                    ->label('Tanggal Ujian')
                    ->description(
                        fn(ExamSession $record): string => ($record->started_at ? $record->started_at->format('H:i') : '-')
                            . ' – '
                            . ($record->finished_at ? $record->finished_at->format('H:i') : '-')
                            . ' WIB'
                    )
                    ->date('d M Y')
                    ->sortable(),

                // ── 4. Durasi ────────────────────────────────────────────
                TextColumn::make('durasi')
                    ->label('Durasi')
                    ->icon('heroicon-m-clock')
                    ->state(fn(ExamSession $record): string => self::formatDuration($record))
                    ->color('gray'),

                // ── 5. Statistik Jawaban: Benar ──────────────────────────
                TextColumn::make('jawaban_benar')
                    ->label('Benar')
                    ->icon('heroicon-m-check-circle')
                    ->state(
                        fn(ExamSession $record): int =>
                        $record->answers()->where('score', '>', 0)
                            ->whereNotNull('answer')->where('answer', '!=', '')->count()
                    )
                    ->color('success')
                    ->alignCenter()
                    ->toggleable(),

                // ── 6. Statistik Jawaban: Salah ──────────────────────────
                TextColumn::make('jawaban_salah')
                    ->label('Salah')
                    ->icon('heroicon-m-x-circle')
                    ->state(
                        fn(ExamSession $record): int =>
                        $record->answers()->where('score', '<=', 0)
                            ->whereNotNull('answer')->where('answer', '!=', '')->count()
                    )
                    ->color('danger')
                    ->alignCenter()
                    ->toggleable(),

                // ── 7. Statistik Jawaban: Tidak Dijawab ──────────────────
                TextColumn::make('tidak_dijawab')
                    ->label('Kosong')
                    ->icon('heroicon-m-minus-circle')
                    ->state(function (ExamSession $record): int {
                        $totalQ = count($record->answers_meta ?? []);
                        if ($totalQ === 0) {
                            $totalQ = $record->examPackage?->questions()->count() ?? 0;
                        }
                        $answered = $record->answers()
                            ->whereNotNull('answer')->where('answer', '!=', '')->count();
                        return max(0, $totalQ - $answered);
                    })
                    ->color('warning')
                    ->alignCenter()
                    ->toggleable(),

                // ── 8. Pelanggaran ───────────────────────────────────────
                TextColumn::make('pelanggaran')
                    ->label('Pelanggaran')
                    ->icon('heroicon-m-exclamation-triangle')
                    ->state(
                        fn(ExamSession $record): int =>
                        $record->activityLogs()
                            ->whereIn('severity', ['warning', 'danger', 'critical'])
                            ->count()
                    )
                    ->color(
                        fn(ExamSession $record): string =>
                        $record->activityLogs()
                            ->whereIn('severity', ['warning', 'danger', 'critical'])
                            ->count() > 0 ? 'danger' : 'gray'
                    )
                    ->alignCenter()
                    ->toggleable(),

                // ── 9. Nilai Akhir ───────────────────────────────────────
                TextColumn::make('total_score')
                    ->label('Nilai')
                    ->badge()
                    ->color(
                        fn(ExamSession $record): string =>
                        self::isLulus($record) ? 'success' : 'danger'
                    )
                    ->icon(
                        fn(ExamSession $record): string =>
                        self::isLulus($record) ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'
                    )
                    ->description(
                        fn(ExamSession $record): string =>
                        'KKM: ' . ($record->examPackage->passing_grade ?? '-')
                    )
                    ->sortable()
                    ->alignCenter(),

                // ── 10. Status Kelulusan ──────────────────────────────────
                TextColumn::make('kelulusan')
                    ->label('Status')
                    ->badge()
                    ->state(
                        fn(ExamSession $record): string =>
                        self::isLulus($record) ? 'LULUS' : 'TIDAK LULUS'
                    )
                    ->color(
                        fn(string $state): string =>
                        $state === 'LULUS' ? 'success' : 'danger'
                    )
                    ->icon(
                        fn(string $state): string =>
                        $state === 'LULUS' ? 'heroicon-m-trophy' : 'heroicon-m-x-circle'
                    )
                    ->alignCenter(),

            ])
            ->filters([

                SelectFilter::make('exam_package_id')
                    ->label('Paket Ujian')
                    ->relationship('examPackage', 'title'),

                Filter::make('rentang_waktu')
                    ->label('Rentang Tanggal Ujian')
                    ->schema([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $q, $date) => $q->whereDate('started_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $q, $date) => $q->whereDate('started_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Dari: ' . $data['dari_tanggal'];
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Sampai: ' . $data['sampai_tanggal'];
                        }
                        return $indicators;
                    }),

                Filter::make('status_kelulusan')
                    ->label('Status Kelulusan')
                    ->schema([
                        \Filament\Forms\Components\Select::make('status')
                            ->label('Filter Status')
                            ->placeholder('Semua Status')
                            ->options([
                                'lulus'       => 'Lulus',
                                'tidak_lulus' => 'Tidak Lulus',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['status'] ?? null, function (Builder $q, $status) {
                            $operator = $status === 'lulus' ? '>=' : '<';
                            $q->whereRaw("total_score {$operator} (
                                SELECT ep.passing_grade
                                FROM exam_packages ep
                                JOIN exam_participants part ON part.exam_package_id = ep.id
                                WHERE part.id = exam_sessions.exam_participant_id
                                LIMIT 1
                            )");
                        });
                    })
                    ->indicateUsing(fn(array $data): ?string => match ($data['status'] ?? null) {
                        'lulus'       => 'Status: Lulus',
                        'tidak_lulus' => 'Status: Tidak Lulus',
                        default       => null,
                    }),

            ])
            ->headerActions([
                DownloadBapAction::make(),
                ExportExamResultsHeaderAction::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat Detail'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Hasil Terpilih')
                        ->modalHeading('Hapus Hasil Ujian Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus hasil ujian terpilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])->label('Tindakan Massal'),
            ]);
    }
}
