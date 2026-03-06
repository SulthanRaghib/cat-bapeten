<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Pages;

use App\DTOs\Question\UpdateQuestionDTO;
use App\Filament\Resources\Questions\QuestionResource;
use App\Models\ExamType;
use App\Services\QuestionService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = 'Edit Soal';

    /**
     * Validate correct answer exists for Teknis type — runs after standard
     * validation so the page does NOT scroll away on error.
     */
    protected function afterValidate(): void
    {
        $data       = $this->form->getState();
        $examTypeId = $data['exam_type_id'] ?? null;
        $method     = $examTypeId ? ExamType::find($examTypeId)?->evaluation_method : null;

        if ($method !== 'correct_wrong') {
            return;
        }

        $options    = $data['options'] ?? [];
        $hasCorrect = false;
        foreach ($options as $opt) {
            if (is_array($opt) && ! empty($opt['is_correct'])) {
                $hasCorrect = true;
                break;
            }
        }

        if (! $hasCorrect) {
            Notification::make()
                ->title('Kunci Jawaban Belum Dipilih')
                ->body('Soal tipe Teknis wajib memiliki minimal 1 jawaban yang ditandai sebagai Kunci Jawaban (Benar).')
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }
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
