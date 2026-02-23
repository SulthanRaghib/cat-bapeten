<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Pages;

use App\DTOs\Question\CreateQuestionDTO;
use App\Filament\Resources\Questions\QuestionResource;
use App\Services\QuestionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = 'Tambah Soal';

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
