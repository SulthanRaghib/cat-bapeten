<?php

namespace App\Filament\Resources\ExamResults\Tables;

use App\Filament\Actions\DownloadBapAction;
use App\Filament\Actions\ExportExamResultsHeaderAction;
use App\Models\ExamSession;
use App\Models\ExamType;
use App\Services\ExamSessionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
            // ── Purple left-border stripe for Mansoskul, subtle blue for Teknis
            ->recordClasses(
                fn(ExamSession $record): string =>
                $record->examPackage?->examType?->evaluation_method === 'weighted'
                    ? 'border-s-[3px] border-violet-500 dark:border-violet-400'
                    : 'border-s-[3px] border-info-400 dark:border-info-500'
            )
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

                // ── 2b. Tipe Ujian badge ────────────────────────────────
                TextColumn::make('tipe_ujian')
                    ->label('Tipe')
                    ->badge()
                    ->state(fn(ExamSession $record): string => match ($record->examPackage?->examType?->evaluation_method) {
                        'weighted'      => 'Mansoskul',
                        'correct_wrong' => 'Teknis',
                        default         => 'Lainnya',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Mansoskul' => 'primary',   // purple
                        'Teknis'    => 'info',       // blue
                        default     => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'Mansoskul' => 'heroicon-m-chart-bar',
                        'Teknis'    => 'heroicon-m-check-badge',
                        default     => 'heroicon-m-question-mark-circle',
                    })
                    ->alignCenter(),

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

                // ── 5. Statistik Jawaban / Unit Kompeten ──────────────────────
                // Teknis → Benar (hijau)
                // Mansoskul → Jumlah unit lulus (ungu)
                TextColumn::make('jawaban_benar')
                    ->label('Benar / Unit ✓')
                    ->icon(fn(ExamSession $record): string =>
                    $record->examPackage?->examType?->evaluation_method === 'weighted'
                        ? 'heroicon-m-squares-2x2'
                        : 'heroicon-m-check-circle')
                    ->state(function (ExamSession $record): string {
                        if ($record->examPackage?->examType?->evaluation_method === 'weighted') {
                            $units    = app(ExamSessionService::class)->calculateWeightedResult($record);
                            $lulus    = collect($units)->filter(fn($u) => $u['is_passing'])->count();
                            $total    = count($units);
                            return "{$lulus}/{$total}";
                        }
                        return (string) $record->answers()->where('score', '>', 0)
                            ->whereNotNull('answer')->where('answer', '!=', '')->count();
                    })
                    ->description(
                        fn(ExamSession $record): ?string =>
                        $record->examPackage?->examType?->evaluation_method === 'weighted'
                            ? 'unit kompeten'
                            : null
                    )
                    ->color(function (ExamSession $record, string $state): string {
                        if ($record->examPackage?->examType?->evaluation_method === 'weighted') {
                            [$l, $t] = explode('/', $state . '/0');
                            return ((int) $l === (int) $t && (int) $t > 0) ? 'success' : 'warning';
                        }
                        return 'success';
                    })
                    ->weight(
                        fn(ExamSession $record): string =>
                        $record->examPackage?->examType?->evaluation_method === 'weighted' ? 'bold' : 'medium'
                    )
                    ->alignCenter()
                    ->toggleable(),

                // ── 6. Statistik Jawaban: Salah / Unit ✕ ──────────────────────
                TextColumn::make('jawaban_salah')
                    ->label('Salah / Unit ✗')
                    ->icon(fn(ExamSession $record): string =>
                    $record->examPackage?->examType?->evaluation_method === 'weighted'
                        ? 'heroicon-m-x-circle'
                        : 'heroicon-m-x-circle')
                    ->state(function (ExamSession $record): string {
                        if ($record->examPackage?->examType?->evaluation_method === 'weighted') {
                            $units  = app(ExamSessionService::class)->calculateWeightedResult($record);
                            $gagal  = collect($units)->filter(fn($u) => !$u['is_passing'])->count();
                            return $gagal > 0 ? (string) $gagal : '—';
                        }
                        return (string) $record->answers()->where('score', '<=', 0)
                            ->whereNotNull('answer')->where('answer', '!=', '')->count();
                    })
                    ->description(
                        fn(ExamSession $record): ?string =>
                        $record->examPackage?->examType?->evaluation_method === 'weighted'
                            ? 'unit belum kompeten'
                            : null
                    )
                    ->color(function (ExamSession $record, string $state): string {
                        if ($state === '—') return 'gray';
                        if ($record->examPackage?->examType?->evaluation_method === 'weighted') return 'danger';
                        return 'danger';
                    })
                    ->alignCenter()
                    ->toggleable(),

                // ── 7. Statistik Jawaban: Tidak Dijawab ──────────────────
                TextColumn::make('tidak_dijawab')
                    ->label('Kosong')
                    ->icon('heroicon-m-minus-circle')
                    ->state(function (ExamSession $record): string {
                        if ($record->examPackage?->examType?->evaluation_method === 'weighted') {
                            return '—';
                        }
                        $totalQ = count($record->answers_meta ?? []);
                        if ($totalQ === 0) {
                            $totalQ = $record->examPackage?->questions()->count() ?? 0;
                        }
                        $answered = $record->answers()
                            ->whereNotNull('answer')->where('answer', '!=', '')->count();
                        return (string) max(0, $totalQ - $answered);
                    })
                    ->color(fn(string $state): string => $state === '—' ? 'gray' : 'warning')
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
                    ->description(function (ExamSession $record): string {
                        $nab = 'NAB: ' . ($record->examPackage->passing_grade ?? '-');
                        if ($record->examPackage?->examType?->evaluation_method === 'weighted') {
                            $unitCount = is_array($record->examPackage?->unit_scoring_configs)
                                ? count($record->examPackage->unit_scoring_configs)
                                : 0;
                            return $nab . ' · ' . $unitCount . ' unit';
                        }
                        return $nab;
                    })
                    ->sortable()
                    ->alignCenter(),

                // ── 10. Status Kelulusan ──────────────────────────────────
                TextColumn::make('kelulusan')
                    ->label('Status')
                    ->badge()
                    ->state(function (ExamSession $record): string {
                        if ($record->status === 'awaiting_interview') {
                            return 'MENUNGGU WAWANCARA';
                        }
                        return self::isLulus($record) ? 'LULUS' : 'TIDAK LULUS';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'LULUS'               => 'success',
                        'TIDAK LULUS'         => 'danger',
                        'MENUNGGU WAWANCARA'  => 'warning',
                        default               => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'LULUS'               => 'heroicon-m-trophy',
                        'TIDAK LULUS'         => 'heroicon-m-x-circle',
                        'MENUNGGU WAWANCARA'  => 'heroicon-m-microphone',
                        default               => 'heroicon-m-question-mark-circle',
                    })
                    ->alignCenter(),

            ])
            ->filters([

                SelectFilter::make('exam_package_id')
                    ->label('Paket Ujian')
                    ->relationship('examPackage', 'title'),

                // ── Filter: Tipe Ujian (dari DB) ──────────────────────────────
                SelectFilter::make('tipe_ujian')
                    ->label('Tipe Ujian')
                    ->options(
                        fn(): array =>
                        ExamType::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('examPackage.examType', function (Builder $q) use ($data) {
                            $q->where('id', $data['value']);
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['value'])) {
                            return null;
                        }
                        $name = ExamType::find($data['value'])?->name;
                        return $name ? 'Tipe: ' . $name : null;
                    }),

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

                // ── Input Nilai Wawancara (hanya untuk sesi awaiting_interview) ────
                Action::make('inputInterviewScore')
                    ->label('Input Nilai Wawancara')
                    ->icon('heroicon-o-microphone')
                    ->color('warning')
                    ->visible(fn(ExamSession $record): bool => $record->status === 'awaiting_interview')
                    ->modalHeading('Input Nilai Wawancara')
                    ->modalDescription(
                        fn(ExamSession $record): string =>
                        'Peserta: ' . ($record->user?->name ?? '-')
                            . ' | CBT Score: ' . number_format((float) ($record->cbt_score ?? 0), 2)
                    )
                    ->modalWidth('md')
                    ->schema([
                        TextInput::make('interview_score')
                            ->label('Nilai Wawancara (0 – 100)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('poin')
                            ->helperText('Masukkan nilai wawancara peserta pada skala 0–100.'),
                    ])
                    ->action(function (ExamSession $record, array $data): void {
                        $config       = $record->examPackage?->technical_scoring_config ?? [];
                        $cbtWeight    = (float) ($config['cbt_weight'] ?? 100);
                        $intWeight    = (float) ($config['interview_weight'] ?? 0);
                        $interviewScore = (float) $data['interview_score'];

                        $finalScore = round(
                            ($record->cbt_score * $cbtWeight / 100)
                                + ($interviewScore   * $intWeight  / 100),
                            2
                        );

                        $record->update([
                            'interview_score' => $interviewScore,
                            'total_score'     => $finalScore,
                            'status'          => 'completed',
                        ]);

                        Notification::make()
                            ->title('Nilai Wawancara Tersimpan')
                            ->body(
                                'CBT: ' . number_format((float) $record->cbt_score, 2)
                                    . ' × ' . $cbtWeight . '%'
                                    . ' + Wawancara: ' . number_format($interviewScore, 2)
                                    . ' × ' . $intWeight . '%'
                                    . ' = Nilai Akhir: ' . number_format($finalScore, 2)
                            )
                            ->success()
                            ->send();
                    }),
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
