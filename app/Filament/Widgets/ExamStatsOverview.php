<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExamMonitors\ExamMonitorResource;
use App\Filament\Resources\ExamPackages\ExamPackageResource;
use App\Models\ExamPackage;
use App\Models\ExamSession;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ExamStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // 1. Live Participants
        $liveCount = ExamSession::where('status', 'ongoing')->count();

        // Real chart: completions per day for last 7 days (proxy for activity trend)
        $liveChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $liveChart[] = ExamSession::where('status', 'completed')
                ->whereDate('finished_at', Carbon::today()->subDays($i))
                ->count();
        }

        // 2. Today's Completions
        $todayCompletions = ExamSession::where('status', 'completed')
            ->whereDate('finished_at', Carbon::today())
            ->count();

        // Yesterday completions for delta
        $yesterdayCompletions = ExamSession::where('status', 'completed')
            ->whereDate('finished_at', Carbon::yesterday())
            ->count();
        $deltaToday = $todayCompletions - $yesterdayCompletions;

        // 3. Active Packages right now
        $activePackages = ExamPackage::where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->count();

        $scheduledPackages = ExamPackage::where('is_active', true)
            ->where('start_time', '>', now())
            ->count();

        // 4. Pass Rate today (%)
        $todaySessions = ExamSession::where('status', 'completed')
            ->whereDate('finished_at', Carbon::today())
            ->with([
                'examPackage' => fn($q) => $q->select('exam_packages.id', 'exam_packages.passing_grade'),
            ])
            ->get(['id', 'exam_participant_id', 'total_score']);

        $passCount = $todaySessions->filter(
            fn($s) => ($s->total_score ?? 0) >= ($s->examPackage?->passing_grade ?? 0)
        )->count();
        $passRateFormatted = $todaySessions->count() > 0
            ? round(($passCount / $todaySessions->count()) * 100) . '%'
            : '—';
        $passRateColor = $todaySessions->count() === 0
            ? 'gray'
            : ($passCount >= $todaySessions->count() * 0.7 ? 'success' : 'warning');

        return [
            Stat::make(__('Participants in Exam'), (string) $liveCount)
                ->description($liveCount > 0 ? __('Currently taking exam') : __('No active sessions'))
                ->descriptionIcon($liveCount > 0 ? 'heroicon-m-signal' : 'heroicon-m-users')
                ->color($liveCount > 0 ? 'warning' : 'gray')
                ->chart($liveChart)
                ->url(ExamMonitorResource::getUrl('index')),

            Stat::make(__('Completed Today'), (string) $todayCompletions)
                ->description(
                    $deltaToday > 0
                        ? __(':delta vs yesterday', ['delta' => "+{$deltaToday}"])
                        : ($deltaToday < 0 ? __(':delta vs yesterday', ['delta' => "{$deltaToday}"]) : __('Same as yesterday'))
                )
                ->descriptionIcon($deltaToday >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($deltaToday >= 0 ? 'success' : 'warning')
                ->url(ExamMonitorResource::getUrl('index')),

            Stat::make(__('Active Exam Packages'), (string) $activePackages)
                ->description(
                    $scheduledPackages > 0
                        ? __('Live now · :count upcoming', ['count' => $scheduledPackages])
                        : ($activePackages > 0 ? __('Live now') : __('No ongoing exams'))
                )
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color($activePackages > 0 ? 'primary' : 'gray')
                ->url(ExamPackageResource::getUrl('index')),

            Stat::make(__('Today\'s Pass Rate'), $passRateFormatted)
                ->description(
                    $todaySessions->count() > 0
                        ? __(':passed passed of :total participants', ['passed' => $passCount, 'total' => $todaySessions->count()])
                        : __('No completed exams today')
                )
                ->descriptionIcon('heroicon-m-trophy')
                ->color($passRateColor),
        ];
    }
}
