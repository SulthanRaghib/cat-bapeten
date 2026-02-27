<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\Pages;

use App\Filament\Resources\QuestionUnits\QuestionUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionUnit extends EditRecord
{
    protected static string $resource = QuestionUnitResource::class;

    protected static ?string $title = 'Edit Unit Soal';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // Combine form + relation managers (Indicators) as sibling tabs
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Detail Unit';
    }
}
