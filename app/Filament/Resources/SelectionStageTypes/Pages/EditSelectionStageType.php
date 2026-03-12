<?php

declare(strict_types=1);

namespace App\Filament\Resources\SelectionStageTypes\Pages;

use App\Filament\Resources\SelectionStageTypes\SelectionStageTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSelectionStageType extends EditRecord
{
    protected static string $resource = SelectionStageTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('Delete Selection Stage Type?'))
                ->modalDescription(__('This selection stage type will be permanently deleted. This action cannot be undone.'))
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
