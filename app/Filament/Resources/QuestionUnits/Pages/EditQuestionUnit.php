<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\Pages;

use App\Filament\Resources\QuestionUnits\QuestionUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionUnit extends EditRecord
{
    protected static string $resource = QuestionUnitResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Edit Question Unit');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('Delete Question Unit?'))
                ->modalDescription(__('The question unit along with all related sub units and questions will be permanently deleted. This action cannot be undone.'))
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
