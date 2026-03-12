<?php

namespace App\Filament\Resources\ExamParticipants\Pages;

use App\Filament\Resources\ExamParticipants\ExamParticipantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExamParticipant extends EditRecord
{
    protected static string $resource = ExamParticipantResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Edit Exam Participant');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('Delete Exam Participant?'))
                ->modalDescription(__('This exam participant data along with all their exam sessions and answers will be permanently deleted. This action cannot be undone.'))
                ->modalSubmitActionLabel(__('Yes, Delete')),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label(__('Save Changes')),

            $this->getCancelFormAction()
                ->label(__('Cancel')),
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
