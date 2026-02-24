<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\Pages;

use App\Filament\Resources\QuestionUnits\QuestionUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionUnit extends EditRecord
{
    protected static string $resource = QuestionUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
