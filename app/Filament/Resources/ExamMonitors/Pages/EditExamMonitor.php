<?php

namespace App\Filament\Resources\ExamMonitors\Pages;

use App\Filament\Resources\ExamMonitors\ExamMonitorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExamMonitor extends EditRecord
{
    protected static string $resource = ExamMonitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
