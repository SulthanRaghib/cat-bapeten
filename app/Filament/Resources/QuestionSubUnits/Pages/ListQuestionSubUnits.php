<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionSubUnits\Pages;

use App\Filament\Resources\QuestionSubUnits\QuestionSubUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestionSubUnits extends ListRecords
{
    protected static string $resource = QuestionSubUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
