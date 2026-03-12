<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Actions\ExportQuestionsHeaderAction;
use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\Questions\Widgets\QuestionStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Question Bank');
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportQuestionsHeaderAction::make(),
            CreateAction::make()
                ->label(__('Add Question'))
                ->icon('heroicon-o-document-plus'),
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
