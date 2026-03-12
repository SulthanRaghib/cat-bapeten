<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamTypes\Pages;

use App\Filament\Resources\ExamTypes\ExamTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExamType extends CreateRecord
{
    protected static string $resource = ExamTypeResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Add Exam Type');
    }

    public function getBreadcrumb(): string
    {
        return __('Add');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label(__('Save')),

            $this->getCreateAnotherFormAction()
                ->label(__('Save & Add Another')),

            $this->getCancelFormAction()
                ->label(__('Cancel')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
