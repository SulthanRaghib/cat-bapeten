<?php

namespace App\Filament\Resources\ExamResults\Pages;

use App\Filament\Resources\ExamResults\ExamResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExamResult extends EditRecord
{
    protected static string $resource = ExamResultResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Exam Result Detail');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('Delete Exam Result?'))
                ->modalDescription(__('This exam result data will be permanently deleted. This action cannot be undone.'))
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
}
