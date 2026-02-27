<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits;

use App\Filament\Resources\QuestionUnits\Pages\CreateQuestionUnit;
use App\Filament\Resources\QuestionUnits\Pages\EditQuestionUnit;
use App\Filament\Resources\QuestionUnits\Pages\ListQuestionUnits;
use App\Filament\Resources\QuestionUnits\Schemas\QuestionUnitForm;
use App\Filament\Resources\QuestionUnits\Tables\QuestionUnitsTable;
use App\Models\QuestionUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class QuestionUnitResource extends Resource
{
    protected static ?string $model = QuestionUnit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Unit Soal';

    protected static ?string $pluralModelLabel = 'Unit Soal';

    protected static ?string $navigationLabel = 'Unit Soal';

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return QuestionUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\IndicatorsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionUnits::route('/'),
            'create' => CreateQuestionUnit::route('/create'),
            'edit' => EditQuestionUnit::route('/{record}/edit'),
        ];
    }
}
