<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\Pages;

use App\Filament\Resources\QuestionUnits\QuestionUnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionUnit extends CreateRecord
{
    protected static string $resource = QuestionUnitResource::class;
}
