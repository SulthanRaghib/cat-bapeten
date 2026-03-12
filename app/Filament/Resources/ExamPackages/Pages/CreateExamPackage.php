<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\Pages;

use App\DTOs\ExamPackage\CreateExamPackageDTO;
use App\Filament\Resources\ExamPackages\ExamPackageResource;
use App\Services\ExamPackageService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateExamPackage extends CreateRecord
{
    protected static string $resource = ExamPackageResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Add Exam Package');
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

    // UX Improvement: Redirect langsung ke halaman Edit agar bisa langsung tambah peserta
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->record,
            'relation' => 0, // <--- Filament otomatis menjadikannya ?relation=0
        ]);
    }

    /**
     * Delegate creation to ExamPackageService.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(ExamPackageService::class)->create(
            CreateExamPackageDTO::fromFormData($data),
        );
    }
}
