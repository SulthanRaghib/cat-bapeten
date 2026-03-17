<?php

namespace App\Filament\Resources\ExamMonitors\Pages;

use App\Filament\Resources\ExamMonitors\ExamMonitorResource;
use App\Models\ExamSession;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;

class ListExamMonitors extends ListRecords
{
    protected static string $resource = ExamMonitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(function (Builder $query): void {
                $mountedAction = $this->mountedActions[array_key_last($this->mountedActions)] ?? null;
                $mountedRecordKey = $mountedAction['context']['recordKey'] ?? null;

                $query->where(function (Builder $subQuery) use ($mountedRecordKey): void {
                    $subQuery->whereIn('status', ['ongoing', 'paused']);

                    if (filled($mountedRecordKey)) {
                        $subQuery->orWhere((new ExamSession)->getQualifiedKeyName(), $mountedRecordKey);
                    }
                });
            });
    }

    public function todoForceFinish($recordId)
    {
        if (! ExamMonitorResource::canForceFinish()) {
            Notification::make()
                ->title(__('Anda tidak memiliki izin untuk memaksa mengakhiri ujian.'))
                ->danger()
                ->send();

            return;
        }

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

        Notification::make()
            ->title(__('Exam session forcefully ended'))
            ->success()
            ->send();

        $this->unmountAction();
    }
}
