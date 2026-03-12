<?php

namespace App\Filament\Resources\Questions\Widgets;

use App\Models\ExamType;
use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuestionStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [
            Stat::make(__('Total Questions'), Question::count())
                ->description(__('All registered questions'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success'),
        ];

        // Dynamically generate a stat card for each active exam type
        $examTypes = ExamType::where('is_active', true)->get();

        $colors = ['info', 'warning', 'primary', 'danger', 'gray'];
        $icons = [
            'correct_wrong' => 'heroicon-m-cpu-chip',
            'weighted' => 'heroicon-m-building-office',
        ];

        foreach ($examTypes as $index => $examType) {
            $count = Question::where('exam_type_id', $examType->id)->count();
            $color = $colors[$index % count($colors)];
            $icon = $icons[$examType->evaluation_method] ?? 'heroicon-m-tag';
            $methodLabel = $examType->isCorrectWrong() ? __('Correct/Incorrect') : __('Score Weight');

            $stats[] = Stat::make(__('Questions: :name', ['name' => $examType->name]), $count)
                ->description(__('Type: :method', ['method' => $methodLabel]))
                ->descriptionIcon($icon)
                ->color($color);
        }

        return $stats;
    }
}
