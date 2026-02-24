<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionSubUnits\Pages;

use App\Filament\Resources\QuestionSubUnits\QuestionSubUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionSubUnit extends EditRecord
{
    protected static string $resource = QuestionSubUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
