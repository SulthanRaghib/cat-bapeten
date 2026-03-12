<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Pages;

use App\DTOs\Question\UpdateQuestionDTO;
use App\Filament\Actions\ValidateCorrectAnswerAction;
use App\Filament\Resources\Questions\QuestionResource;
use App\Services\QuestionService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Edit Question');
    }

    /**
     * Validate correct answer exists for Teknis type — delegates to the
     * shared Action so this rule is never duplicated across pages.
     */
    protected function afterValidate(): void
    {
        ValidateCorrectAnswerAction::run($this);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('Delete Question?'))
                ->modalDescription(__('This question along with all its answers and score configurations will be permanently deleted. This action cannot be undone.'))
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

    /**
     * Override default Eloquent update — delegate to QuestionService so that
     * scoring_config is re-derived whenever options change.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(QuestionService::class)->update(
            $record, // @var \App\Models\Question $record
            UpdateQuestionDTO::fromFormData($data),
        );
    }
}
