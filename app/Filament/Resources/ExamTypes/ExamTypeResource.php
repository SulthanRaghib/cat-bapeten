<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamTypes;

use App\Filament\Resources\ExamTypes\Pages\CreateExamType;
use App\Filament\Resources\ExamTypes\Pages\EditExamType;
use App\Filament\Resources\ExamTypes\Pages\ListExamTypes;
use App\Filament\Resources\ExamTypes\Schemas\ExamTypeForm;
use App\Filament\Resources\ExamTypes\Tables\ExamTypesTable;
use App\Models\ExamType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ExamTypeResource extends Resource
{
    protected static ?string $model = ExamType::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $recordTitleAttribute = 'name';
    public static function getModelLabel(): string
    {
        return __('Exam Type');
    }
    public static function getPluralModelLabel(): string
    {
        return __('Exam Types');
    }
    public static function getNavigationLabel(): string
    {
        return __('Exam Types');
    }
    public static function getNavigationGroup(): ?string
    {
        return __('Master Data');
    }
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ExamTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamTypes::route('/'),
            'create' => CreateExamType::route('/create'),
            'edit' => EditExamType::route('/{record}/edit'),
        ];
    }
}
