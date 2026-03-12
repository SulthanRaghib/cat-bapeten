<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionSubUnits\Pages;

use App\Filament\Resources\QuestionSubUnits\QuestionSubUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionSubUnit extends EditRecord
{
    protected static string $resource = QuestionSubUnitResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Edit Question Sub Unit');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('Delete Question Sub Unit?'))
                ->modalDescription(__('This question sub unit will be permanently deleted. This action cannot be undone.'))
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
