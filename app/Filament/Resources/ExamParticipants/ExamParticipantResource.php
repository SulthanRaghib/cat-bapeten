<?php

namespace App\Filament\Resources\ExamParticipants;

use App\Filament\Resources\ExamParticipants\Pages\CreateExamParticipant;
use App\Filament\Resources\ExamParticipants\Pages\EditExamParticipant;
use App\Filament\Resources\ExamParticipants\Pages\ListExamParticipants;
use App\Filament\Resources\ExamParticipants\Schemas\ExamParticipantForm;
use App\Filament\Resources\ExamParticipants\Tables\ExamParticipantsTable;
use App\Models\ExamParticipant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ExamParticipantResource extends Resource
{
    protected static ?string $model = ExamParticipant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'token';

    public static function getModelLabel(): string
    {
        return __('Exam Participant');
    }
    public static function getPluralModelLabel(): string
    {
        return __('Exam Participants');
    }
    public static function getNavigationLabel(): string
    {
        return __('Exam Participants');
    }
    protected static ?int $navigationSort = 3;
    public static function getNavigationGroup(): ?string
    {
        return __('Exam Management');
    }

    public static function form(Schema $schema): Schema
    {
        return ExamParticipantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamParticipantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamParticipants::route('/'),
            'create' => CreateExamParticipant::route('/create'),
            'edit' => EditExamParticipant::route('/{record}/edit'),
        ];
    }
}
