<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionSubUnits;

use App\Filament\Resources\QuestionSubUnits\Pages\CreateQuestionSubUnit;
use App\Filament\Resources\QuestionSubUnits\Pages\EditQuestionSubUnit;
use App\Filament\Resources\QuestionSubUnits\Pages\ListQuestionSubUnits;
use App\Filament\Resources\QuestionSubUnits\Schemas\QuestionSubUnitForm;
use App\Filament\Resources\QuestionSubUnits\Tables\QuestionSubUnitsTable;
use App\Models\QuestionSubUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class QuestionSubUnitResource extends Resource
{
    protected static ?string $model = QuestionSubUnit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('Question Sub Unit');
    }
    public static function getPluralModelLabel(): string
    {
        return __('Question Sub Units');
    }
    public static function getNavigationLabel(): string
    {
        return __('Question Sub Units');
    }
    public static function getNavigationGroup(): ?string
    {
        return __('Master Data');
    }
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return QuestionSubUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionSubUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionSubUnits::route('/'),
            'create' => CreateQuestionSubUnit::route('/create'),
            'edit' => EditQuestionSubUnit::route('/{record}/edit'),
        ];
    }
}
