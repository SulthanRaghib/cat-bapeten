<?php

declare(strict_types=1);

namespace App\Filament\Resources\SelectionStageTypes\Pages;

use App\Filament\Resources\SelectionStageTypes\SelectionStageTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSelectionStageType extends CreateRecord
{
    protected static string $resource = SelectionStageTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
