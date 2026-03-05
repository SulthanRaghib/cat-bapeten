<?php

declare(strict_types=1);

namespace App\Filament\Resources\SelectionStageTypes\Pages;

use App\Filament\Resources\SelectionStageTypes\SelectionStageTypeResource;
use Filament\Resources\Pages\ListRecords;

class ListSelectionStageTypes extends ListRecords
{
    protected static string $resource = SelectionStageTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
