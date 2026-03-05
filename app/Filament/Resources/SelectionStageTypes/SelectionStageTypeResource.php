<?php

declare(strict_types=1);

namespace App\Filament\Resources\SelectionStageTypes;

use App\Filament\Resources\SelectionStageTypes\Pages\CreateSelectionStageType;
use App\Filament\Resources\SelectionStageTypes\Pages\EditSelectionStageType;
use App\Filament\Resources\SelectionStageTypes\Pages\ListSelectionStageTypes;
use App\Filament\Resources\SelectionStageTypes\Schemas\SelectionStageTypeForm;
use App\Filament\Resources\SelectionStageTypes\Tables\SelectionStageTypesTable;
use App\Models\SelectionStageType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SelectionStageTypeResource extends Resource
{
    protected static ?string $model = SelectionStageType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel           = 'Jenis Tahap Seleksi';
    protected static ?string $pluralModelLabel     = 'Jenis Tahap Seleksi';
    protected static ?string $navigationLabel      = 'Tahap Seleksi';

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return SelectionStageTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SelectionStageTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSelectionStageTypes::route('/'),
            'create' => CreateSelectionStageType::route('/create'),
            'edit'   => EditSelectionStageType::route('/{record}/edit'),
        ];
    }
}
