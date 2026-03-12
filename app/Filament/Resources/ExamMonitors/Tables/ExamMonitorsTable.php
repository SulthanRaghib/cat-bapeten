<?php

namespace App\Filament\Resources\ExamMonitors\Tables;

use App\Models\ExamSession;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamMonitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->poll('5s')
            ->recordUrl(null)
            ->recordClasses(
                fn(ExamSession $record): string =>
                $record->examPackage?->examType?->evaluation_method === 'weighted'
                    ? 'border-s-[3px] border-violet-500 dark:border-violet-400'
                    : 'border-s-[3px] border-info-400 dark:border-info-500'
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('Participant'))
                    ->description(fn(ExamSession $record): string => 'NIP: ' . ($record->user->nip ?? '—'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('examParticipant.token')
                    ->label(__('Token'))
                    ->copyable()
                    ->copyMessage(__('Token copied!'))
                    ->copyMessageDuration(2000)
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('examPackage.title')
                    ->label(__('Exam Package'))
                    ->description(
                        fn(ExamSession $record): string =>
                        $record->examPackage?->examType?->name ?? '—'
                    )
                    ->limit(35)
                    ->wrap(),

                TextColumn::make('started_at')
                    ->label(__('Start Time'))
                    ->description(
                        fn(ExamSession $record): string =>
                        $record->started_at ? $record->started_at->format('d M Y') : '—'
                    )
                    ->dateTime('H:i')
                    ->icon('heroicon-m-clock'),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->icon(fn(string $state): string => match ($state) {
                        'ongoing'    => 'heroicon-m-play-circle',
                        'paused'     => 'heroicon-m-pause-circle',
                        'completed'  => 'heroicon-m-check-circle',
                        'terminated' => 'heroicon-m-x-circle',
                        default      => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'ongoing'    => 'success',
                        'paused'     => 'warning',
                        'completed'  => 'info',
                        'terminated' => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'ongoing'    => __('Running'),
                        'paused'     => __('Paused'),
                        'completed'  => __('Completed'),
                        'terminated' => __('Terminated'),
                        default      => $state,
                    }),

                TextColumn::make('progress')
                    ->label(__('Progress'))
                    ->state(function (ExamSession $record): string {
                        $meta     = $record->answers_meta ?? [];
                        $total    = is_array($meta) ? count($meta) : 0;
                        if ($total === 0) return '0 / 0';
                        $answered = $record->answers()->whereNotNull('answer')->count();
                        $pct      = round(($answered / $total) * 100);
                        return "{$answered}/{$total} ({$pct}%)";
                    })
                    ->description(__('questions answered'))
                    ->icon('heroicon-m-chart-bar')
                    ->color('gray'),

                TextColumn::make('violation_count')
                    ->label(__('Violations'))
                    ->state(
                        fn(ExamSession $record) =>
                        \App\Models\ExamActivityLog::where('exam_session_id', $record->id)
                            ->whereIn('severity', ['warning', 'danger', 'critical'])
                            ->count()
                    )
                    ->icon('heroicon-m-exclamation-triangle')
                    ->badge()
                    ->alignCenter()
                    ->color(fn(string $state): string => match (true) {
                        (int) $state > 5 => 'danger',
                        (int) $state > 0 => 'warning',
                        default          => 'success',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('pantau')
                    ->label(__('Monitor'))
                    ->icon('heroicon-o-eye')
                    ->modalWidth('7xl')
                    ->modalContent(fn($record) => $record ? view('filament.resources.exam-monitors.modal-content', ['record' => $record]) : null)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->slideOver(),
            ])
            ->emptyStateHeading(__('No active exam sessions'))
            ->emptyStateDescription(__('No active exam sessions currently. This page auto-refreshes every 5 seconds.'))
            ->emptyStateIcon('heroicon-o-computer-desktop');
    }
}
