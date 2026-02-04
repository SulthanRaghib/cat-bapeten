<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\Questions\Widgets\QuestionStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = 'Bank Soal';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Soal'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            QuestionStatsOverview::class,
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
