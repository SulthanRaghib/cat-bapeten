<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Schema $form): Schema
    {
        // Reuse the form from QuestionResource for consistency
        return QuestionResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->colors([
                        'primary' => 'technical',
                        'warning' => 'structural',
                    ]),

                TextColumn::make('question_text')
                    ->label('Soal')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        return Str::limit(strip_tags((string)$state), 80);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->multiple()
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        // Critical: Filter questions by the same type as the ExamPackage
                        $examType = $this->getOwnerRecord()->type;
                        return $query->where('type', $examType);
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ])
            ->reorderable('sort_order');
    }
}
