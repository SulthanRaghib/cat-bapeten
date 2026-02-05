<?php

namespace App\Filament\Widgets;

use App\Models\ExamSession;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ScoreDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Nilai Peserta';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Get generic distribution of scores for completed sessions
        // Ranges: 0-40 (Low), 41-70 (Mid), 71-100 (High)

        // Using raw cases for clean categorization
        $ranges = ExamSession::query()
            ->where('status', 'completed')
            ->selectRaw("
                CASE
                    WHEN total_score < 40 THEN 'Rendah (0-40)'
                    WHEN total_score between 41 and 70 THEN 'Menengah (41-70)'
                    WHEN total_score > 70 THEN 'Tinggi (71-100)'
                    ELSE 'Tidak Diketahui'
                END as score_range,
                COUNT(*) as count
            ")
            ->groupBy('score_range')
            ->pluck('count', 'score_range')
            ->toArray();

        // Ensure keys exist even if 0
        $data = [
            'Rendah (0-40)' => $ranges['Rendah (0-40)'] ?? 0,
            'Menengah (41-70)' => $ranges['Menengah (41-70)'] ?? 0,
            'Tinggi (71-100)' => $ranges['Tinggi (71-100)'] ?? 0,
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Peserta',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#ef4444', // Red-500
                        '#f59e0b', // Amber-500
                        '#10b981', // Emerald-500
                    ],
                    'borderWidth' => 0,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
