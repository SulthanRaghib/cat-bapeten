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
 */
class ExamResultsPdfExportService
{
    /**
     * Build and stream a PDF download.
     */
    public function download(
        Collection $sessions,
        bool $includeStatistics = true,
        array $filterMeta = [],
    ): StreamedResponse {
        $sessions->loadMissing(['user', 'examPackage', 'examParticipant', 'answers']);

        // Build stats for the report
        $processed = $sessions->map(function (ExamSession $session) use ($includeStatistics) {
            $totalQ = count($session->answers_meta ?? []);
            if ($totalQ === 0) {
                $totalQ = $session->examPackage?->questions()->count() ?? 0;
            }

            $answeredCount = $session->answers()
                ->whereNotNull('answer')->where('answer', '!=', '')->count();
            $correctCount = $session->answers()
                ->where('score', '>', 0)
                ->whereNotNull('answer')->where('answer', '!=', '')->count();
            $wrongCount = $session->answers()
                ->where('score', '<=', 0)
                ->whereNotNull('answer')->where('answer', '!=', '')->count();
            $unansweredCount = max(0, $totalQ - $answeredCount);

            $passingGrade = $session->examPackage->passing_grade ?? 0;
            $isLulus = ($session->total_score ?? 0) >= $passingGrade;

            return [
                'nama'           => $session->user?->name ?? '-',
                'nip'            => (string) ($session->user?->nip ?? '-'),
                'paket_ujian'    => $session->examPackage?->title ?? '-',
                'tanggal'        => $session->started_at ? $session->started_at->format('d/m/Y') : '-',
                'waktu_mulai'    => $session->started_at ? $session->started_at->format('H:i') . ' WIB' : '-',
                'waktu_selesai'  => $session->finished_at ? $session->finished_at->format('H:i') . ' WIB' : '-',
                'durasi'         => $this->formatDuration($session),
                'benar'          => $correctCount,
                'salah'          => $wrongCount,
                'tidak_dijawab'  => $unansweredCount,
                'total_soal'     => $totalQ,
                'nilai'          => $session->total_score ?? 0,
                'kkm'            => $passingGrade,
                'status'         => $isLulus ? 'LULUS' : 'TIDAK LULUS',
                'is_lulus'       => $isLulus,
            ];
        });

        // Summary statistics
        $summary = [
            'total_peserta'  => $processed->count(),
            'jumlah_lulus'   => $processed->where('is_lulus', true)->count(),
            'jumlah_gagal'   => $processed->where('is_lulus', false)->count(),
            'rata_rata_nilai' => $processed->count() > 0 ? round($processed->avg('nilai'), 2) : 0,
            'nilai_tertinggi' => $processed->max('nilai') ?? 0,
            'nilai_terendah'  => $processed->min('nilai') ?? 0,
        ];

        $pdf = Pdf::loadView('exports.exam-results-pdf', [
            'results'           => $processed,
            'includeStatistics' => $includeStatistics,
            'filterMeta'        => $filterMeta,
            'summary'           => $summary,
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

        if ($h > 0) return "{$h}j {$m}m {$s}d";
        if ($m > 0) return "{$m}m {$s}d";
        return "{$s}d";
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
