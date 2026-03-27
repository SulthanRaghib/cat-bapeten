<?php

namespace App\Filament\Resources\ExamResults;

use App\Filament\Resources\ExamResults\Pages\CreateExamResult;
use App\Filament\Resources\ExamResults\Pages\EditExamResult;
use App\Filament\Resources\ExamResults\Pages\ListExamResults;
use App\Filament\Resources\ExamResults\Schemas\ExamResultForm;
use App\Filament\Resources\ExamResults\Tables\ExamResultsTable;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\User;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ExamResultResource extends Resource
{
    protected static ?string $model = ExamResult::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('Exam Result');
    }
    public static function getPluralModelLabel(): string
    {
        return __('Exam Results');
    }
    public static function getNavigationLabel(): string
    {
        return __('Exam Results');
    }
    public static function getNavigationGroup(): ?string
    {
        return __('Reports & Results');
    }

    /**
     * Gunakan permission ViewAny:ExamResult (terpisah dari ViewAny:ExamSession).
     * Shield UI akan menampilkan "Hasil Ujian" dan "Monitoring Ujian" sebagai
     * dua section independen — cukup centang/uncentang dari UI tanpa hardcode role.
     */
    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->can('ViewAny:ExamResult');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['completed', 'awaiting_interview'])
            ->orderBy('finished_at', 'desc')
            ->with(['examPackage.examType']); // Eager-load for exam-type detection in table & infolist
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
                    // ── KIRI (1/3) ──────────────────────────────────────────────
                    Group::make([
                        Section::make(__('Exam Information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextEntry::make('examPackage.title')
                                    ->label(__('Exam Package Name'))
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('examPackage.examType.name')
                                    ->label(__('Exam Type'))
                                    ->badge()
                                    ->color(fn(ExamSession $record): string => match ($record->examPackage?->examType?->evaluation_method) {
                                        'weighted'     => 'primary',
                                        'correct_wrong' => 'info',
                                        default        => 'gray',
                                    }),

                                TextEntry::make('exam_method_label')
                                    ->label(__('Evaluation Method'))
                                    ->badge()
                                    ->state(fn(ExamSession $record): string => match ($record->examPackage?->examType?->evaluation_method) {
                                        'weighted'     => 'Pembobotan Nilai (Mansoskul)',
                                        'correct_wrong' => 'Benar / Salah (Teknis)',
                                        default        => '-',
                                    })
                                    ->color(fn(string $state): string => str_contains($state, 'Mansoskul') ? 'primary' : 'info'),

                                TextEntry::make('examPackage.passing_grade')
                                    ->label(__('NAB (Passing Grade)'))
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('user.name')
                                    ->label(__('Participant Name')),

                                TextEntry::make('user.nip')
                                    ->label('NIP'),

                                TextEntry::make('started_at')
                                    ->label(__('Start Time'))
                                    ->dateTime('d M Y, H:i'),

                                TextEntry::make('finished_at')
                                    ->label(__('End Time'))
                                    ->dateTime('d M Y, H:i'),
                            ]),

                        Section::make(__('Participant Violations'))
                            ->icon('heroicon-o-shield-exclamation')
                            ->schema([
                                ViewEntry::make('violation_detail')
                                    ->label('')
                                    ->view('filament.resources.exam-results.infolists.violation-detail'),
                            ]),
                    ])
                        ->columnSpan(1),

                    // ── KANAN (2/3) ──────────────────────────────────────────────
                    Group::make([
                        Section::make(__('Result Statistics'))
                            ->columns(3)
                            ->schema([
                                // ── Selalu tampil ──
                                TextEntry::make('total_score')
                                    ->label(__('Total Score'))
                                    ->size('xl')
                                    ->weight('black'),

                                TextEntry::make('status_lulus')
                                    ->label(__('Pass Status'))
                                    ->badge()
                                    ->state(fn(ExamSession $record): string => $record->total_score >= ($record->examPackage->passing_grade ?? 0) ? __('Pass') : __('Fail'))
                                    ->color(fn(string $state): string => $state === __('Pass') ? 'success' : 'danger')
                                    ->icon(fn(string $state): string => $state === __('Pass') ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),

                                TextEntry::make('duration')
                                    ->label(__('Duration'))
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

                                // ── Hanya Teknis ──────────────────────────────
                                TextEntry::make('jawaban_benar')
                                    ->label(__('Correct Answers'))
                                    ->icon('heroicon-m-check-circle')
                                    ->iconColor('success')
                                    ->color('success')
                                    ->weight('bold')
                                    ->state(fn(ExamSession $record): int =>
                                    $record->answers()->where('score', '>', 0)
                                        ->whereNotNull('answer')->where('answer', '!=', '')->count())
                                    ->hidden(fn(ExamSession $record): bool =>
                                    $record->examPackage?->examType?->evaluation_method === 'weighted'),

                                TextEntry::make('jawaban_salah')
                                    ->label(__('Wrong Answers'))
                                    ->icon('heroicon-m-x-circle')
                                    ->iconColor('danger')
                                    ->color('danger')
                                    ->weight('bold')
                                    ->state(fn(ExamSession $record): int =>
                                    $record->answers()->where('score', '<=', 0)
                                        ->whereNotNull('answer')->where('answer', '!=', '')->count())
                                    ->hidden(fn(ExamSession $record): bool =>
                                    $record->examPackage?->examType?->evaluation_method === 'weighted'),

                                TextEntry::make('tidak_dijawab')
                                    ->label(__('Unanswered'))
                                    ->icon('heroicon-m-minus-circle')
                                    ->iconColor('warning')
                                    ->color('warning')
                                    ->weight('bold')
                                    ->state(function (ExamSession $record): int {
                                        $totalQ = count($record->resolveQuestionIds());
                                        if ($totalQ === 0) {
                                            $totalQ = $record->examPackage?->questions()->count() ?? 0;
                                        }
                                        $answered = $record->answers()
                                            ->whereNotNull('answer')->where('answer', '!=', '')->count();
                                        return max(0, $totalQ - $answered);
                                    })
                                    ->hidden(fn(ExamSession $record): bool =>
                                    $record->examPackage?->examType?->evaluation_method === 'weighted'),

                                // ── Hanya Mansoskul: rincian per unit ──────────
                                ViewEntry::make('mansoskul_unit_results')
                                    ->label('')
                                    ->view('filament.resources.exam-results.infolists.mansoskul-unit-results')
                                    ->columnSpanFull()
                                    ->hidden(fn(ExamSession $record): bool =>
                                    $record->examPackage?->examType?->evaluation_method !== 'weighted'),

                                // ── Rincian tahap seleksi lanjutan (Teknis multi-stage) ──
                                ViewEntry::make('stage_scores_breakdown')
                                    ->label('')
                                    ->view('filament.resources.exam-results.infolists.stage-scores-breakdown')
                                    ->columnSpanFull()
                                    ->hidden(
                                        fn(ExamSession $record): bool =>
                                        empty($record->stage_scores)
                                            || $record->examPackage?->examType?->evaluation_method === 'weighted'
                                    ),
                            ]),

                        Section::make(__('Question & Answer Detail'))
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                ViewEntry::make('answer_summary')
                                    ->label('')
                                    ->view('filament.resources.exam-results.infolists.answer-summary-grid'),
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
