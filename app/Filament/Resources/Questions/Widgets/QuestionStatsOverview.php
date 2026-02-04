<?php

namespace App\Filament\Resources\Questions\Widgets;

use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuestionStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Soal', Question::count())
                ->description('Semua soal yang terdaftar')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success'),
            Stat::make('Soal Teknis', Question::where('type', 'technical')->count())
                ->description('Tipe Benar/Salah')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('info'),
            Stat::make('Soal Struktural', Question::where('type', 'structural')->count())
                ->description('Tipe Bobot Nilai')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('warning'),
        ];
    }
}
