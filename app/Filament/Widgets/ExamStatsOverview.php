<?php

namespace App\Filament\Widgets;

use App\Models\ExamSession;
use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ExamStatsOverview extends BaseWidget
{
    // Enable polling (optional, set to null to disable)
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // 1. Live Participants
        $liveCount = ExamSession::where('status', 'ongoing')->count();

        // 2. Today's Completions
        $todayCompletions = ExamSession::where('status', 'completed')
            ->whereDate('finished_at', Carbon::today())
            ->count();

        // 3. Question Bank
        $totalQuestions = Question::count();

        // 4. Average Score (Today)
        $avgScore = ExamSession::where('status', 'completed')
            ->whereDate('finished_at', Carbon::today())
            ->avg('total_score');

        // Round to 1 decimal place
        $avgScoreFormatted = $avgScore ? number_format($avgScore, 1) : '-';

        return [
            Stat::make('Peserta Live', (string) $liveCount)
                ->description('Sedang mengerjakan ujian saat ini')
                ->descriptionIcon('heroicon-m-users')
                ->color('danger')
                ->chart([$liveCount, $liveCount + 2, $liveCount - 1, $liveCount]) // visual candy
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50',
                ]),

            Stat::make('Selesai Hari Ini', (string) $todayCompletions)
                ->description('Ujian diselesaikan hari ini')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Bank Soal', (string) $totalQuestions)
                ->description('Total soal di seluruh kategori')
                ->descriptionIcon('heroicon-m-server')
                ->color('info'),

            Stat::make('Rata-rata Nilai', $avgScoreFormatted)
                ->description('Rerata nilai ujian hari ini')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
