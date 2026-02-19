<?php

namespace App\Filament\Resources\ExamResults;

use App\Filament\Resources\ExamResults\Pages\CreateExamResult;
use App\Filament\Resources\ExamResults\Pages\EditExamResult;
use App\Filament\Resources\ExamResults\Pages\ListExamResults;
use App\Filament\Resources\ExamResults\Schemas\ExamResultForm;
use App\Filament\Resources\ExamResults\Tables\ExamResultsTable;
use App\Models\ExamSession;
use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ExamResultResource extends Resource
{
    protected static ?string $model = ExamSession::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $recordTitleAttribute = 'name';

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
        return $schema->schema([
            Grid::make(['default' => 1, 'lg' => 3])
                ->columnSpanFull()
                ->schema([
                    // KIRI (1/3)
                    Section::make('Informasi Ujian')
                        ->icon('heroicon-o-information-circle')
                        ->columnSpan(1)
                        ->schema([
                            TextEntry::make('examPackage.title')
                                ->label('Nama Paket Ujian')
                                ->weight('bold')
                                ->size('lg'),

                            TextEntry::make('examPackage.type')
                                ->label('Tipe Ujian')
                                ->badge(),

                            TextEntry::make('examPackage.passing_grade')
                                ->label('Passing Grade')
                                ->badge()
                                ->color('success'),

                            TextEntry::make('user.name')
                                ->label('Nama Peserta'),

                            TextEntry::make('user.nip')
                                ->label('NIP'),

                            TextEntry::make('started_at')
                                ->label('Waktu Mulai')
                                ->dateTime('d M Y, H:i'),

                            TextEntry::make('finished_at')
                                ->label('Waktu Selesai')
                                ->dateTime('d M Y, H:i'),
                        ]),

                    // KANAN (2/3)
                    Group::make([
                        Section::make('Statistik Hasil')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('total_score')
                                    ->label('Total Nilai')
                                    ->size('xl')
                                    ->weight('black'),

                                TextEntry::make('status_lulus')
                                    ->label('Status Kelulusan')
                                    ->badge()
                                    ->state(fn(ExamSession $record): string => $record->total_score >= ($record->examPackage->passing_grade ?? 0) ? 'Lulus' : 'Tidak Lulus')
                                    ->color(fn(string $state): string => $state === 'Lulus' ? 'success' : 'danger')
                                    ->icon(fn(string $state): string => $state === 'Lulus' ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),

                                TextEntry::make('duration')
                                    ->label('Durasi')
                                    ->icon('heroicon-m-clock')
                                    ->state(function (ExamSession $record): string {
                                        if (! $record->started_at || ! $record->finished_at) {
                                            return '-';
                                        }
                                        $total = (int) $record->started_at->diffInSeconds($record->finished_at);
                                        $h = intdiv($total, 3600);
                                        $m = intdiv($total % 3600, 60);
                                        $s = $total % 60;
                                        if ($h > 0) {
                                            return "{$h} jam {$m} menit {$s} detik";
                                        }
                                        if ($m > 0) {
                                            return "{$m} menit {$s} detik";
                                        }
                                        return "{$s} detik";
                                    }),
                            ]),

                        Section::make('Detail Soal & Jawaban')
                            ->schema([
                                RepeatableEntry::make('answers')
                                    ->label('')
                                    ->contained(false)
                                    ->schema([
                                        ViewEntry::make('detail')
                                            ->view('filament.resources.exam-results.infolists.question-answer-item'),
                                    ]),
                            ])
                            ->extraAttributes([
                                'class' => 'max-h-[600px] overflow-y-auto',
                            ]),
                    ])
                        ->columnSpan(2),
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
