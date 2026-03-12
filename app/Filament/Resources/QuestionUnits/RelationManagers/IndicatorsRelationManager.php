<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Manages the default/template indicator levels (NAB) for each QuestionUnit.
 *
 * Admins use this to set up the master data that will be synced
 * into ExamPackage JSON snapshots via the "Sync dari Soal" action.
 */
class IndicatorsRelationManager extends RelationManager
{
    protected static string $relationship = 'indicators';

    protected static ?string $title = null;
    protected static ?string $modelLabel = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('NAB Indicators (Template)');
    }

    public static function getModelLabel(): string
    {
        return __('Indicator');
    }

    // Hanya tampilkan tab ini untuk unit bertipe Mansoskul (weighted)
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->examType?->evaluation_method === 'weighted';
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('Indicator Level Name'))
                    ->validationAttribute(__('Indicator Level Name'))
                    ->placeholder(__('e.g. Meets Standard'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('min_score')
                    ->label(__('Minimum Score'))
                    ->validationAttribute(__('Minimum Score'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('max_score')
                    ->label(__('Maximum Score'))
                    ->validationAttribute(__('Maximum Score'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                Toggle::make('is_passing')
                    ->label(__('Passes NAB?'))
                    ->helperText(__('Mark if participants with this indicator are considered to have passed.'))
                    ->default(false)
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label(__('Order'))
                    ->validationAttribute(__('Order'))
                    ->numeric()
                    ->default(0)
                    ->helperText(__('Display order — smaller values appear first.')),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label(__('#'))
                    ->sortable()
                    ->width('50px'),

                TextColumn::make('name')
                    ->label(__('Indicator Name'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('min_score')
                    ->label(__('Min'))
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('max_score')
                    ->label(__('Max'))
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('range')
                    ->label(__('Score Range'))
                    ->getStateUsing(fn($record): string => "{$record->min_score} – {$record->max_score}")
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_passing')
                    ->label(__('Pass?'))
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Add Indicator'))
                    ->icon('heroicon-o-plus')
                    ->modalHeading(__('Add NAB Indicator'))
                    ->modalSubmitActionLabel(__('Save Indicator')),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalHeading(__('Edit Indicator Data'))
                    ->modalSubmitActionLabel(__('Save Changes')),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading(__('Delete Indicator?'))
                    ->modalDescription(__('Are you sure you want to delete this indicator? This action cannot be undone.'))
                    ->modalSubmitActionLabel(__('Yes, Delete')),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label(__('Delete Selected'))
                    ->modalHeading(__('Delete Selected Indicators?'))
                    ->modalDescription(__('Are you sure you want to delete the selected indicators? This action cannot be undone.'))
                    ->modalSubmitActionLabel(__('Yes, Delete')),
            ])
            ->emptyStateHeading(__('No indicators yet'))
            ->emptyStateDescription(__('Add NAB indicator levels for this unit. This data will be the default template when synced to Exam Packages.'))
            ->emptyStateIcon('heroicon-o-chart-bar');
    }
}
