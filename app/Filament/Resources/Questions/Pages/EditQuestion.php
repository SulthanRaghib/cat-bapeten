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

    protected static ?string $title = 'Edit Soal';

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
                ->modalHeading('Hapus Soal?')
                ->modalDescription('Soal ini beserta semua jawaban dan konfigurasi skornya akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan'),

            $this->getCancelFormAction()
                ->label('Batal'),
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
