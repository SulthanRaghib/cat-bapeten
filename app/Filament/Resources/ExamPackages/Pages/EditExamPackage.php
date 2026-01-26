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
}
