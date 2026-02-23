<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Pages;

use App\DTOs\Question\UpdateQuestionDTO;
use App\Filament\Resources\Questions\QuestionResource;
use App\Services\QuestionService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = 'Edit Soal';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
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
