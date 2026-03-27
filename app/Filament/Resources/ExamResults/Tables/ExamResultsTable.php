<?php

namespace App\Filament\Resources\ExamResults\Tables;

use App\Filament\Actions\DownloadBapAction;
use App\Filament\Actions\ExportExamResultsHeaderAction;
use App\Models\ExamSession;
use App\Models\ExamType;
use App\Services\ExamSessionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Forms\Components\Placeholder;
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

        $parts = [];
        if ($h > 0) {
            $parts[] = "{$h} " . __('hour');
        }
        if ($m > 0) {
            $parts[] = "{$m} " . __('minute');
        }
        if ($s > 0 || empty($parts)) {
            $parts[] = "{$s} " . __('second');
        }

        return implode(' ', $parts);
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
            ->poll('5s')
            ->defaultSort('finished_at', 'desc')
            // ── Purple left-border stripe for Mansoskul, subtle blue for Teknis
            ->recordClasses(
                fn(ExamSession $record): string =>
                $record->examPackage?->examType?->evaluation_method === 'weighted'
                    ? 'border-s-[3px] border-primary-600 dark:border-primary-500'
                    : 'border-s-[3px] border-primary-400 dark:border-primary-300'
            )
            ->columns([

                // ── 1. Peserta ───────────────────────────────────────────
                TextColumn::make('user.name')
                    ->label(__('Participant Name'))
                    ->description(fn(ExamSession $record): string => 'NIP: ' . ($record->user->nip ?? '-'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                // ── 2. Paket Ujian ───────────────────────────────────────
                TextColumn::make('examPackage.title')
                    ->label(__('Exam Package'))
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                // ── 2b. Tipe Ujian badge ────────────────────────────────
                TextColumn::make('tipe_ujian')
                    ->label(__('Type'))
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
                    ->label(__('Exam Date'))
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
                    ->label(__('Duration'))
                    ->icon('heroicon-m-clock')
                    ->state(fn(ExamSession $record): string => self::formatDuration($record))
                    ->color('gray'),

                // ── 5. Statistik Jawaban / Unit Kompeten ──────────────────────
                // Teknis → Benar (hijau)
                // Mansoskul → Jumlah unit lulus (ungu)
                TextColumn::make('jawaban_benar')
                    ->label(__('Correct / Unit ✓'))
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
                            ? __('competent units')
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
                    ->label(__('Wrong / Unit ✗'))
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
                            ? __('units not yet competent')
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
                    ->label(__('Blank'))
                    ->icon('heroicon-m-minus-circle')
                    ->state(function (ExamSession $record): string {
                        if ($record->examPackage?->examType?->evaluation_method === 'weighted') {
                            return '—';
                        }
                        $totalQ = count($record->resolveQuestionIds());
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
                    ->label(__('Violations'))
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
                    ->label(__('Score'))
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
                    ->label(__('Status'))
                    ->badge()
                    ->state(function (ExamSession $record): string {
                        if ($record->status === 'awaiting_interview') {
                            return 'AWAITING_SELECTION';
                        }
                        return self::isLulus($record) ? 'PASSED' : 'FAILED';
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'PASSED'             => __('PASSED'),
                        'FAILED'             => __('FAILED'),
                        'AWAITING_SELECTION' => __('AWAITING SELECTION'),
                        default              => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'PASSED'             => 'success',
                        'FAILED'             => 'danger',
                        'AWAITING_SELECTION' => 'warning',
                        default              => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'PASSED'             => 'heroicon-m-trophy',
                        'FAILED'             => 'heroicon-m-x-circle',
                        'AWAITING_SELECTION' => 'heroicon-m-clipboard-document-check',
                        default              => 'heroicon-m-question-mark-circle',
                    })
                    ->alignCenter(),

            ])
            ->filters([

                SelectFilter::make('exam_package_id')
                    ->label(__('Exam Package'))
                    ->relationship('examPackage', 'title'),

                // ── Filter: Tipe Ujian (dari DB) ──────────────────────────────
                SelectFilter::make('tipe_ujian')
                    ->label(__('Exam Type'))
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
                    ->label(__('Exam Date Range'))
                    ->schema([
                        DatePicker::make('dari_tanggal')->label(__('From Date')),
                        DatePicker::make('sampai_tanggal')->label(__('Until Date')),
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
                    ->label(__('Pass Status'))
                    ->schema([
                        \Filament\Forms\Components\Select::make('status')
                            ->label(__('Filter Status'))
                            ->placeholder(__('All Statuses'))
                            ->options([
                                'lulus'       => __('Pass'),
                                'tidak_lulus' => __('Fail'),
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
                ActionGroup::make([
                    ViewAction::make()
                        ->label(__('View Detail')),

                    // ── Input Nilai Seleksi Lanjutan (hanya untuk sesi awaiting_interview) ────
                    Action::make('inputStageScores')
                        ->label(__('Input Selection Scores'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('warning')
                        ->visible(fn(ExamSession $record): bool => $record->status === 'awaiting_interview')
                        ->modalHeading(
                            fn(ExamSession $record): string =>
                            __('Input Selection Scores — :name', ['name' => $record->user?->name ?? '-'])
                        )
                        ->modalDescription(
                            fn(ExamSession $record): string =>
                            __('CBT Score: :score points  |  Package: :package', [
                                'score' => number_format((float) ($record->cbt_score ?? 0), 2),
                                'package' => $record->examPackage?->title ?? '-',
                            ])
                        )
                        ->modalWidth('lg')
                        ->schema(fn(ExamSession $record): array => self::buildStageScoreSchema($record))
                        ->action(fn(ExamSession $record, array $data) => self::processStageScores($record, $data)),
                ])
                    ->label(__('Action Group'))
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected Results'))
                        ->modalHeading(__('Delete Selected Exam Results'))
                        ->modalDescription(__('Are you sure you want to delete the selected exam results? This action cannot be undone.'))
                        ->modalSubmitActionLabel(__('Yes, Delete')),
                ])->label(__('Bulk Actions')),
            ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // HELPER: Build dynamic score input form for each selection stage
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Build a Filament form schema array for inputting scores per stage.
     * Supports:
     *  - New format: technical_scoring_config.stages[] with {label, weight}
     *  - Legacy format: interview_weight (single interview stage)
     */
    private static function buildStageScoreSchema(ExamSession $record): array
    {
        $config    = $record->examPackage?->technical_scoring_config ?? [];
        $cbtWeight = (float) ($config['cbt_weight'] ?? 100);
        $stages    = $config['stages'] ?? null;

        // Legacy backward-compat: old format only had interview_weight
        if (empty($stages) && isset($config['interview_weight'])) {
            $stages = [
                ['label' => 'Wawancara', 'weight' => (float) $config['interview_weight']],
            ];
        }

        $stages = (array) $stages;

        $fields = [];

        // ── CBT summary info ──────────────────────────────────────────────
        $fields[] = \Filament\Schemas\Components\Section::make()
            ->extraAttributes(['class' => 'bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl'])
            ->schema([
                \Filament\Forms\Components\Placeholder::make('cbt_summary')
                    ->label('')
                    ->content(function () use ($record, $cbtWeight): string {
                        return '📊 ' . __('CBT Score: :score points × CBT Weight: :weight% → CBT Contribution: :contribution points', [
                            'score' => number_format((float) ($record->cbt_score ?? 0), 2),
                            'weight' => $cbtWeight,
                            'contribution' => number_format((float) ($record->cbt_score ?? 0) * $cbtWeight / 100, 2),
                        ]);
                    }),
            ]);

        // ── Per-stage score inputs ────────────────────────────────────────
        if (empty($stages)) {
            $fields[] = \Filament\Forms\Components\Placeholder::make('no_stages')
                ->label('')
                ->content(__('No selection stages configured for this exam package.'));
        } else {
            foreach ($stages as $i => $stage) {
                $label  = $stage['label'] ?? ('Tahap ' . ($i + 1));
                $weight = (float) ($stage['weight'] ?? 0);
                $key    = 'stage_' . $i;

                $fields[] = TextInput::make("stage_scores.{$key}")
                    ->label("{$label}")
                    ->helperText(__('Weight: :weight% of final score', ['weight' => $weight]))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix(__('points'))
                    ->placeholder('0 – 100')
                    // Pre-fill from existing stage_scores if re-editing
                    ->default(fn() => $record->stage_scores[$key] ?? null);
            }
        }

        return $fields;
    }

    /**
     * Process stage scores: calculate final score, persist, notify.
     */
    private static function processStageScores(ExamSession $record, array $data): void
    {
        $config    = $record->examPackage?->technical_scoring_config ?? [];
        $cbtWeight = (float) ($config['cbt_weight'] ?? 100);
        $stages    = $config['stages'] ?? null;

        if (empty($stages) && isset($config['interview_weight'])) {
            $stages = [
                ['label' => 'Wawancara', 'weight' => (float) $config['interview_weight']],
            ];
        }

        $stages      = (array) $stages;
        $stageScores = $data['stage_scores'] ?? [];

        // ── Calculate final score ─────────────────────────────────────────
        $finalScore = (float) ($record->cbt_score ?? 0) * $cbtWeight / 100;
        $breakdown  = ['CBT: ' . number_format((float) $record->cbt_score, 2) . ' × ' . $cbtWeight . '%'];

        foreach ($stages as $i => $stage) {
            $label  = $stage['label'] ?? ('Tahap ' . ($i + 1));
            $weight = (float) ($stage['weight'] ?? 0);
            $key    = 'stage_' . $i;
            $score  = (float) ($stageScores[$key] ?? 0);

            $finalScore += $score * $weight / 100;
            $breakdown[] = "{$label}: " . number_format($score, 2) . " × {$weight}%";
        }

        $finalScore = round($finalScore, 2);

        // ── Backward-compat: keep interview_score for single-stage packages ──
        $legacyInterviewScore = null;
        if (count($stages) === 1) {
            $legacyInterviewScore = (float) ($stageScores['stage_0'] ?? 0);
        }

        $record->update([
            'stage_scores'    => $stageScores,
            'interview_score' => $legacyInterviewScore,
            'total_score'     => $finalScore,
            'status'          => 'completed',
        ]);

        Notification::make()
            ->title(__('Selection Scores Saved'))
            ->body(
                implode('  +  ', $breakdown)
                    . '  =  ' . __('Final Score: :score points', ['score' => number_format($finalScore, 2)])
            )
            ->success()
            ->send();
    }
}
