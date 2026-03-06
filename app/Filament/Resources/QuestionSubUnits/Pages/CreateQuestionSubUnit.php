<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionSubUnits\Pages;

use App\Filament\Resources\QuestionSubUnits\QuestionSubUnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionSubUnit extends CreateRecord
{
    protected static string $resource = QuestionSubUnitResource::class;

    protected static ?string $title = 'Tambah Sub Unit Soal';

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
}
