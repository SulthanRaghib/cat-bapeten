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
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
