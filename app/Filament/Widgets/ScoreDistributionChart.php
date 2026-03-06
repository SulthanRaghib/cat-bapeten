<?php

namespace App\Filament\Widgets;

use App\Models\ExamSession;
use Filament\Widgets\ChartWidget;

class ScoreDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Kelulusan per Tipe Ujian';
    protected ?string $description = 'Total akumulasi seluruh sesi ujian yang sudah selesai';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $sessions = ExamSession::where('status', 'completed')
            ->with([
                'examPackage' => fn($q) => $q->select('exam_packages.id', 'exam_packages.passing_grade', 'exam_packages.exam_type_id'),
                'examPackage.examType' => fn($q) => $q->select('exam_types.id', 'exam_types.evaluation_method'),
            ])
            ->get(['id', 'exam_participant_id', 'total_score']);

        $lulusTeknis       = 0;
        $gagalTeknis       = 0;
        $lulusMansoskul    = 0;
        $gagalMansoskul    = 0;

        foreach ($sessions as $s) {
            $method       = $s->examPackage?->examType?->evaluation_method;
            $passingGrade = $s->examPackage?->passing_grade ?? 0;
            $passed       = ($s->total_score ?? 0) >= $passingGrade;

            if ($method === 'correct_wrong') {
                $passed ? $lulusTeknis++ : $gagalTeknis++;
            } elseif ($method === 'weighted') {
                $passed ? $lulusMansoskul++ : $gagalMansoskul++;
            }
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Lulus',
                    'data'            => [$lulusTeknis, $lulusMansoskul],
                    'backgroundColor' => '#10b981',
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'Tidak Lulus',
                    'data'            => [$gagalTeknis, $gagalMansoskul],
                    'backgroundColor' => '#ef4444',
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => ['Teknis', 'Mansoskul'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
            'scales' => [
                'x' => ['stacked' => false],
                'y' => ['stacked' => false, 'beginAtZero' => true],
            ],
        ];
    }
}
