<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Pages;

use App\DTOs\Question\CreateQuestionDTO;
use App\Filament\Resources\Questions\QuestionResource;
use App\Models\ExamType;
use App\Services\QuestionService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = 'Tambah Soal';

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

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan'),

            $this->getCreateAnotherFormAction()
                ->label('Simpan & Tambah Lagi'),

            $this->getCancelFormAction()
                ->label('Batal'),
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
