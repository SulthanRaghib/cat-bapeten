<?php

namespace App\Filament\Resources\ExamParticipants\Pages;

use App\Filament\Resources\ExamParticipants\ExamParticipantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExamParticipant extends EditRecord
{
    protected static string $resource = ExamParticipantResource::class;

    protected static ?string $title = 'Edit Peserta Ujian';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getContentTabLabel(): ?string
    {
        return 'Detail Peserta';
    }
}
