<?php

namespace App\Filament\Resources\ExamResults;

use App\Filament\Resources\ExamResults\Pages\CreateExamResult;
use App\Filament\Resources\ExamResults\Pages\EditExamResult;
use App\Filament\Resources\ExamResults\Pages\ListExamResults;
use App\Filament\Resources\ExamResults\Schemas\ExamResultForm;
use App\Filament\Resources\ExamResults\Tables\ExamResultsTable;
use App\Models\ExamSession;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ExamResultResource extends Resource
{
    protected static ?string $model = ExamSession::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $modelLabel = 'Hasil Ujian';
    protected static ?string $pluralModelLabel = 'Hasil Ujian';
    protected static ?string $navigationLabel = 'Hasil Ujian';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan & Hasil';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'completed')
            ->orderBy('finished_at', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return ExamResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamResultsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Statistik')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('total_score')
                            ->label('Nilai Akhir')
                            ->size('lg')
                            ->weight('bold'),
                        TextEntry::make('correct_answers_count')
                            ->label('Jawaban Benar')
                            ->state(fn(ExamSession $record) => $record->answers()->where('score', '>', 0)->count()),
                        TextEntry::make('wrong_answers_count')
                            ->label('Jawaban Salah')
                            ->state(fn(ExamSession $record) => $record->answers()->where('score', 0)->count()),
                    ]),
                // Placeholder for answers list
                Section::make('Detail Jawaban')
                    ->schema([
                        TextEntry::make('answers_list')
                            ->label('Daftar Jawaban')
                            ->placeholder('Fitur ini akan segera tersedia showing list of specific answers.'),
                    ]),
            ]);
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
            'index' => ListExamResults::route('/'),
            'create' => CreateExamResult::route('/create'),
            'edit' => EditExamResult::route('/{record}/edit'),
        ];
    }
}
