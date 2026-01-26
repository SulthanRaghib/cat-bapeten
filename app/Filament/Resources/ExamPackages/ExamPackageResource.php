<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages;

use App\Filament\Resources\ExamPackages\Pages\CreateExamPackage;
use App\Filament\Resources\ExamPackages\Pages\EditExamPackage;
use App\Filament\Resources\ExamPackages\Pages\ListExamPackages;
use App\Filament\Resources\ExamPackages\Schemas\ExamPackageForm;
use App\Filament\Resources\ExamPackages\Tables\ExamPackagesTable;
use App\Models\ExamPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ExamPackageResource extends Resource
{
    protected static ?string $model = ExamPackage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Paket Ujian';
    protected static ?string $pluralModelLabel = 'Paket Ujian';
    protected static ?string $navigationLabel = 'Paket Ujian';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Ujian';

    public static function form(Schema $schema): Schema
    {
        return ExamPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamPackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ParticipantsRelationManager::class,
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamPackages::route('/'),
            'create' => CreateExamPackage::route('/create'),
            'edit' => EditExamPackage::route('/{record}/edit'),
        ];
    }
}
