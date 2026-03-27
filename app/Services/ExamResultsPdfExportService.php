<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExamSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Generates a printable PDF report from ExamSession (completed) records.
 * Supports both Teknis (correct_wrong) and Mansoskul (weighted) exam types.
 */
class ExamResultsPdfExportService
{
    public function __construct(
        private readonly ExamSessionService $examSessionService,
    ) {}

    /**
     * Build and stream a PDF download.
     */
    public function download(
        Collection $sessions,
        bool $includeStatistics = true,
        array $filterMeta = [],
    ): StreamedResponse {
        $sessions->loadMissing([
            'user',
            'examPackage.examType',
            'examParticipant',
            'answers',
            'activityLogs',
        ]);

        // Build per-session data row
        $processed = $sessions->map(function (ExamSession $session) {
            $evalMethod  = $session->examPackage?->examType?->evaluation_method ?? 'correct_wrong';
            $isWeighted  = $evalMethod === 'weighted';
            $passingGrade = $session->examPackage->passing_grade ?? 0;

            $violationCount = $session->activityLogs
                ->whereIn('severity', ['warning', 'danger', 'critical'])
                ->count();

            if ($isWeighted) {
                // ── Mansoskul: weighted per-unit scoring ─────────────
                $unitResults = $this->examSessionService->calculateWeightedResult($session);
                $allPassing  = !empty($unitResults)
                    && collect($unitResults)->every(fn($u) => $u['is_passing']);
                $isLulus = $allPassing && ($session->total_score ?? 0) >= $passingGrade;

                return [
                    'eval_method'   => 'weighted',
                    'nama'          => $session->user?->name ?? '-',
                    'nip'           => (string) ($session->user?->nip ?? '-'),
                    'paket_ujian'   => $session->examPackage?->title ?? '-',
                    'tipe_ujian'    => $session->examPackage?->examType?->name ?? '-',
                    'tanggal'       => $session->started_at ? $session->started_at->format('d/m/Y') : '-',
                    'waktu_mulai'   => $session->started_at ? $session->started_at->format('H:i') . ' WIB' : '-',
                    'waktu_selesai' => $session->finished_at ? $session->finished_at->format('H:i') . ' WIB' : '-',
                    'durasi'        => $this->formatDuration($session),
                    'pelanggaran'   => $violationCount,
                    'nilai'         => $session->total_score ?? 0,
                    'nab'           => $passingGrade,
                    'status'        => $isLulus ? 'LULUS' : 'TIDAK LULUS',
                    'is_lulus'      => $isLulus,
                    // Mansoskul-specific
                    'unit_results'       => $unitResults,
                    'unit_lulus_count'   => collect($unitResults)->filter(fn($u) => $u['is_passing'])->count(),
                    'unit_total_count'   => count($unitResults),
                ];
            }

            // ── Teknis: correct/wrong scoring ──────────────────────────
            $totalQ = count($session->resolveQuestionIds());
            if ($totalQ === 0) {
                $totalQ = $session->examPackage?->questions()->count() ?? 0;
            }
            $answeredCount = $session->answers()
                ->whereNotNull('answer')->where('answer', '!=', '')->count();
            $correctCount  = $session->answers()
                ->where('score', '>', 0)
                ->whereNotNull('answer')->where('answer', '!=', '')->count();
            $wrongCount    = $session->answers()
                ->where('score', '<=', 0)
                ->whereNotNull('answer')->where('answer', '!=', '')->count();
            $unansweredCount = max(0, $totalQ - $answeredCount);
            $isLulus = ($session->total_score ?? 0) >= $passingGrade;

            $technicalConfig = $session->examPackage?->technical_scoring_config ?? [];

            return [
                'eval_method'    => 'correct_wrong',
                'nama'           => $session->user?->name ?? '-',
                'nip'            => (string) ($session->user?->nip ?? '-'),
                'paket_ujian'    => $session->examPackage?->title ?? '-',
                'tipe_ujian'     => $session->examPackage?->examType?->name ?? '-',
                'tanggal'        => $session->started_at ? $session->started_at->format('d/m/Y') : '-',
                'waktu_mulai'    => $session->started_at ? $session->started_at->format('H:i') . ' WIB' : '-',
                'waktu_selesai'  => $session->finished_at ? $session->finished_at->format('H:i') . ' WIB' : '-',
                'durasi'         => $this->formatDuration($session),
                'benar'          => $correctCount,
                'salah'          => $wrongCount,
                'tidak_dijawab'  => $unansweredCount,
                'total_soal'     => $totalQ,
                'pelanggaran'    => $violationCount,
                'nilai'          => $session->total_score ?? 0,
                'nab'            => $passingGrade,
                'status'         => $isLulus ? 'LULUS' : 'TIDAK LULUS',
                'is_lulus'       => $isLulus,
                // Multi-stage fields (null when no stages configured)
                'cbt_score'      => $session->cbt_score,
                'stage_scores'   => $session->stage_scores ?? [],
                'has_stages'     => !empty($session->stage_scores),
                'cbt_weight'     => (float) ($technicalConfig['cbt_weight'] ?? 100),
                'stages_config'  => $technicalConfig['stages'] ?? [],
            ];
        });

        // Split by exam type for separate tables in PDF
        $teknisResults     = $processed->filter(fn($r) => ($r['eval_method'] ?? '') !== 'weighted')->values();
        $mansoskulResults  = $processed->filter(fn($r) => ($r['eval_method'] ?? '') === 'weighted')->values();

        // Summary statistics (all sessions combined)
        $summary = [
            'total_peserta'   => $processed->count(),
            'jumlah_lulus'    => $processed->where('is_lulus', true)->count(),
            'jumlah_gagal'    => $processed->where('is_lulus', false)->count(),
            'rata_rata_nilai' => $processed->count() > 0 ? round($processed->avg('nilai'), 2) : 0,
            'nilai_tertinggi' => $processed->max('nilai') ?? 0,
            'nilai_terendah'  => $processed->min('nilai') ?? 0,
        ];

        $pdf = Pdf::loadView('exports.exam-results-pdf', [
            'results'             => $processed,         // backward-compat
            'teknis_results'      => $teknisResults,
            'mansoskul_results'   => $mansoskulResults,
            'includeStatistics'   => $includeStatistics,
            'filterMeta'          => $filterMeta,
            'summary'             => $summary,
            // True when at least one teknis row has multi-stage scoring
            'has_staged_teknis'   => $teknisResults->contains('has_stages', true),
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'sans-serif')
            ->setOption('dpi', 96);

        $filename = 'Laporan_Hasil_Ujian_BAPETEN' . $this->buildFilenameSlug($filterMeta) . '_' . now()->format('Ymd-His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    private function formatDuration(ExamSession $record): string
    {
        if (! $record->started_at || ! $record->finished_at) {
            return '-';
        }
        $total = (int) $record->started_at->diffInSeconds($record->finished_at);
        $h     = intdiv($total, 3600);
        $m     = intdiv($total % 3600, 60);
        $s     = $total % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    private function buildFilenameSlug(array $filterMeta): string
    {
        if (empty($filterMeta)) {
            return '';
        }
        $parts = [];
        foreach ($filterMeta as $value) {
            $slug = Str::slug((string) $value, '-');
            if ($slug !== '') {
                $parts[] = $slug;
            }
        }
        return $parts !== [] ? '_' . implode('_', $parts) : '';
    }
}
