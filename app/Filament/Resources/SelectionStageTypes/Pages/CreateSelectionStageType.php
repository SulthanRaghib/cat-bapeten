<?php

declare(strict_types=1);

namespace App\Filament\Resources\SelectionStageTypes\Pages;

use App\Filament\Resources\SelectionStageTypes\SelectionStageTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSelectionStageType extends CreateRecord
{
    protected static string $resource = SelectionStageTypeResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Add Selection Stage Type');
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
