<?php

namespace App\Filament\Resources\ExamPackages\Pages;

use App\Filament\Resources\ExamPackages\ExamPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExamPackage extends EditRecord
{
    protected static string $resource = ExamPackageResource::class;

    protected static ?string $title = 'Edit Paket Ujian';

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

    // UX Improvement: Menggabungkan Form dan Relation Manager dalam Tab Sejajar
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    // Mengubah label Tab Form utama (defaultnya "Edit")
    public function getContentTabLabel(): ?string
    {
        return 'Detail Ujian';
    }
}
