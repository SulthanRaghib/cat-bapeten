<?php

namespace App\Filament\Widgets;

use App\Models\ExamSession;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class GlobalPassRateChart extends ChartWidget
{
    protected ?string $heading = 'Tren Kelulusan (14 Hari Terakhir)';
    protected ?string $description = 'Jumlah peserta yang lulus per hari, berdasarkan tipe ujian';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(
            fn($d) => Carbon::today()->subDays($d)
        );

        // Load all completed sessions in window with minimal columns
        $sessions = ExamSession::where('status', 'completed')
            ->where('finished_at', '>=', Carbon::today()->subDays(13)->startOfDay())
            ->with([
                'examPackage' => fn($q) => $q->select('exam_packages.id', 'exam_packages.passing_grade', 'exam_packages.exam_type_id'),
                'examPackage.examType' => fn($q) => $q->select('exam_types.id', 'exam_types.evaluation_method'),
            ])
            ->get(['id', 'exam_participant_id', 'total_score', 'finished_at']);

        $teknisPassed    = [];
        $manposkulPassed = [];
        $labels          = [];

        foreach ($days as $day) {
            $dayStr    = $day->format('Y-m-d');
            $labels[]  = $day->format('d M');

            $daySessions = $sessions->filter(
                fn($s) => optional($s->finished_at)->format('Y-m-d') === $dayStr
            );

            $teknisPassed[] = $daySessions->filter(
                fn($s) => $s->examPackage?->examType?->evaluation_method === 'correct_wrong'
                    && ($s->total_score ?? 0) >= ($s->examPackage?->passing_grade ?? 0)
            )->count();

            $manposkulPassed[] = $daySessions->filter(
                fn($s) => $s->examPackage?->examType?->evaluation_method === 'weighted'
                    && ($s->total_score ?? 0) >= ($s->examPackage?->passing_grade ?? 0)
            )->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Teknis (Lulus)',
                    'data'            => $teknisPassed,
                    'borderColor'     => '#38bdf8',  // sky-400 (info)
                    'backgroundColor' => '#38bdf820',
                    'fill'            => true,
                    'tension'         => 0.3,
                    'pointRadius'     => 4,
                ],
                [
                    'label'           => 'Mansoskul (Lulus)',
                    'data'            => $manposkulPassed,
                    'borderColor'     => '#8b5cf6',  // violet-500 (primary mansoskul)
                    'backgroundColor' => '#8b5cf620',
                    'fill'            => true,
                    'tension'         => 0.3,
                    'pointRadius'     => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['stepSize' => 1],
                ],
            ],
        ];
    }
}
