<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionSubUnits\Pages;

use App\Filament\Resources\QuestionSubUnits\QuestionSubUnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionSubUnit extends CreateRecord
{
    protected static string $resource = QuestionSubUnitResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Add Question Sub Unit');
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
