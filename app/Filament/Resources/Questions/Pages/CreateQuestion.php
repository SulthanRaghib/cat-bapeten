<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Pages;

use App\DTOs\Question\CreateQuestionDTO;
use App\Filament\Actions\ValidateCorrectAnswerAction;
use App\Filament\Resources\Questions\QuestionResource;
use App\Services\QuestionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Add Question');
    }

    /**
     * Validate correct answer exists for Teknis type — delegates to the
     * shared Action so this rule is never duplicated across pages.
     */
    protected function afterValidate(): void
    {
        ValidateCorrectAnswerAction::run($this);
    }

    public function getBreadcrumb(): string
    {
        return __('Add');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label(__('Save')),

            $this->getCreateAnotherFormAction()
                ->label(__('Save & Add Another')),

            $this->getCancelFormAction()
                ->label(__('Cancel')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Override default Eloquent create — delegate to QuestionService so that
     * scoring_config is always auto-generated after the options are saved.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(QuestionService::class)->create(
            CreateQuestionDTO::fromFormData($data),
        );
    }
}
