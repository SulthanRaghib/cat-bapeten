<?php

namespace App\Filament\Resources\ExamPackages\Pages;

use App\Filament\Resources\ExamPackages\ExamPackageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExamPackage extends CreateRecord
{
    protected static string $resource = ExamPackageResource::class;

    protected static ?string $title = 'Tambah Paket Ujian';

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

    // UX Improvement: Redirect langsung ke halaman Edit agar bisa langsung tambah peserta
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->record,
            'relation' => 0, // <--- Filament otomatis menjadikannya ?relation=0
        ]);
    }
}
