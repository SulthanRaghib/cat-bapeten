<?php

namespace App\Filament\Resources\ExamMonitors\Pages;

use App\Filament\Resources\ExamMonitors\ExamMonitorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamMonitors extends ListRecords
{
    protected static string $resource = ExamMonitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    public function todoForceFinish($recordId)
    {
        $record = \App\Models\ExamSession::find($recordId);
        if (! $record) {
            return;
        }

        $totalScore = (int) $record->answers()->sum('score');

        $record->forceFill([
            'status' => 'completed',
            'finished_at' => now(),
            'total_score' => $totalScore,
        ])->save();

        if ($record->examParticipant && $record->examParticipant->is_active) {
            $record->examParticipant->update(['is_active' => false]);
        }

        \Filament\Notifications\Notification::make()
            ->title(__('Exam session forcefully ended'))
            ->success()
            ->send();
    }
}
