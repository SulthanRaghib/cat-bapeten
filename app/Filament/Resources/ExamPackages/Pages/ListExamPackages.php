<?php

namespace App\Filament\Resources\ExamPackages\Pages;

use App\Filament\Resources\ExamPackages\ExamPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamPackages extends ListRecords
{
    protected static string $resource = ExamPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Paket Ujian'),
        ];
    }
}
