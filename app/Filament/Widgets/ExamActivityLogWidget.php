<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ExamActivityLog;
use App\Filament\Resources\ExamMonitors\ExamMonitorResource;
use Filament\Widgets\Widget;

class ExamActivityLogWidget extends Widget
{
    protected string $view = 'filament.widgets.exam-activity-log';
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';

    /** Filter aktif: 'all' | 'violations' */
    public string $filter = 'all';

    private const ACTION_LABELS = [
        'tab_switch'         => 'Tab Switch',
        'window_blur'        => 'Window Blur',
        'copy_attempt'       => 'Copy Attempt',
        'paste_attempt'      => 'Paste Attempt',
        'right_click'        => 'Right Click',
        'screenshot_attempt' => 'Screenshot Attempt',
    ];

    private const SEVERITY_CONFIG = [
        'warning'  => ['color' => 'amber',   'icon' => 'heroicon-s-exclamation-triangle', 'dot' => 'bg-amber-400'],
        'danger'   => ['color' => 'red',     'icon' => 'heroicon-s-x-circle',             'dot' => 'bg-red-500'],
        'critical' => ['color' => 'purple',  'icon' => 'heroicon-s-shield-exclamation',   'dot' => 'bg-purple-600'],
        'info'     => ['color' => 'blue',    'icon' => 'heroicon-s-information-circle',   'dot' => 'bg-blue-400'],
    ];

    public function getViewData(): array
    {
        $query = ExamActivityLog::query()
            ->with([
                'examSession.examParticipant.user',
                'examSession.examParticipant.examPackage',
            ])
            ->orderByDesc('created_at')
            ->limit(25);

        if ($this->filter === 'violations') {
            $query->whereIn('severity', ['warning', 'danger', 'critical']);
        }

        $logs = $query->get()->map(function (ExamActivityLog $log): array {
            $session     = $log->examSession;
            $participant = $session?->examParticipant;
            $user        = $participant?->user;
            $package     = $participant?->examPackage;
            $severity    = $log->severity ?? 'warning';
            $config      = self::SEVERITY_CONFIG[$severity] ?? self::SEVERITY_CONFIG['warning'];
            $actionKey   = self::ACTION_LABELS[$log->action] ?? $log->action;

            return [
                'id'          => $log->id,
                'action'      => $actionKey,
                'message'     => $log->message,
                'severity'    => $severity,
                'dot'         => $config['dot'],
                'icon'        => $config['icon'],
                'color'       => $config['color'],
                'participant' => $user?->name ?? '—',
                'nip'         => $user?->nip ?? null,
                'package'     => $package?->title ?? '—',
                'time'        => $log->created_at,
                'session_id'  => $session?->id,
            ];
        });

        $monitorUrl = ExamMonitorResource::getUrl('index');

        return [
            'logs'       => $logs,
            'monitorUrl' => $monitorUrl,
            'filter'     => $this->filter,
        ];
    }
}
