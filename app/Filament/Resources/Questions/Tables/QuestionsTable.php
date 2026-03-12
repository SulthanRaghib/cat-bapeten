<?php

namespace App\Filament\Resources\Questions\Tables;

use App\Filament\Actions\ExportQuestionsBulkAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('examType.name')
                    ->label(__('Exam Type'))
                    ->badge()
                    ->icon(fn($record): string => match ($record->examType?->evaluation_method) {
                        'correct_wrong' => 'heroicon-m-check-badge',
                        'weighted'      => 'heroicon-m-chart-bar',
                        default         => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn($record): string => match ($record->examType?->evaluation_method) {
                        'correct_wrong' => 'info',
                        'weighted'      => 'primary',
                        default         => 'gray',
                    }),

                TextColumn::make('question_text')
                    ->label(__('Question Text'))
                    ->html()
                    ->limit(60)
                    ->wrap()
                    ->tooltip(function (TextColumn $column): ?string {
                        try {
                            $state = $column->getState();
                            if (strlen($state) <= 60) {
                                return null;
                            }
                            return strip_tags($state);
                        } catch (\Exception $e) {
                            return null;
                        }
                    }),

                TextColumn::make('category')
                    ->label(__('Difficulty Level'))
                    ->badge()
                    ->icon(fn(?string $state): string => match ($state) {
                        'easy'   => 'heroicon-m-face-smile',
                        'medium' => 'heroicon-m-minus-circle',
                        'hard'   => 'heroicon-m-fire',
                        default  => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'easy'   => 'success',
                        'medium' => 'warning',
                        'hard'   => 'danger',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'easy'   => __('Easy'),
                        'medium' => __('Medium'),
                        'hard'   => __('Hard'),
                        default  => '\u2014',
                    })
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('exam_type_id')
                    ->label(__('Exam Type'))
                    ->relationship('examType', 'name'),
                SelectFilter::make('category')
                    ->label(__('Difficulty Level'))
                    ->options([
                        'easy'   => __('Easy'),
                        'medium' => __('Medium'),
                        'hard'   => __('Hard'),
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-m-eye')
                        ->color('gray')
                        ->label(__('View Detail'))
                        ->modalHeading(__('Question Detail'))
                        ->modalContent(fn($record) => view('filament.modals.question-detail', [
                            'record' => $record,
                            'manager' => new \App\Helpers\ScoringConfigFormatter(),
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('Close')),
                    EditAction::make()
                        ->label(__('Edit Question'))
                        ->icon('heroicon-m-pencil-square'),
                ])
                    ->label(__('Action Group'))
                    ->button()
                    ->size(\Filament\Support\Enums\Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportQuestionsBulkAction::make(),
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected Questions'))
                        ->modalHeading(__('Delete Selected Questions'))
                        ->modalDescription(__('Are you sure you want to delete the selected questions? This action cannot be undone.'))
                        ->modalSubmitActionLabel(__('Yes, Delete')),
                ])->label(__('Bulk Actions')),
            ])
            ->emptyStateHeading(__('No questions yet'))
            ->emptyStateDescription(__('Add new questions to the question bank to be used in exam packages.'))
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
