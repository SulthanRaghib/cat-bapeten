<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Filament\Exports\QuestionExcelExport;
use App\Models\ExamType;
use App\Models\Question;
use App\Models\QuestionSubUnit;
use App\Models\QuestionUnit;
use App\Services\QuestionPdfExportService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;

/**
 * Header Action for exporting all / filtered questions.
 *
 * Displays a modal with filter controls and format selector,
 * then delegates to the appropriate export strategy.
 */
class ExportQuestionsHeaderAction
{
    public static function make(): Action
    {
        return Action::make('exportQuestions')
            ->label('Export Soal')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->modalHeading('Export Bank Soal')
            ->modalDescription('Atur filter dan format export yang diinginkan.')
            ->modalSubmitActionLabel('Download')
            ->modalWidth('lg')
            ->schema([
                Section::make('Filter Data')
                    ->description('Kosongkan filter untuk mengekspor semua data.')
                    ->icon('heroicon-o-funnel')
                    ->collapsible()
                    ->schema([
                        Select::make('filter_exam_type_id')
                            ->label('Tipe Soal')
                            ->options(fn() => ExamType::where('is_active', true)->pluck('name', 'id'))
                            ->placeholder('Semua Tipe Soal')
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('filter_question_unit_id', null);
                                $set('filter_question_sub_unit_id', null);
                            })
                            ->noOptionsMessage('Tidak ada tipe soal aktif')
                            ->native(false),

                        Select::make('filter_question_unit_id')
                            ->label('Unit (Materi/Bab)')
                            ->options(function (Get $get) {
                                $examTypeId = $get('filter_exam_type_id');
                                if (! $examTypeId) {
                                    return QuestionUnit::where('is_active', true)
                                        ->with('examType')
                                        ->get()
                                        ->mapWithKeys(fn($u) => [$u->id => "{$u->name} ({$u->examType?->name})"]);
                                }
                                return QuestionUnit::where('exam_type_id', $examTypeId)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->placeholder('Semua Unit')
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('filter_question_sub_unit_id', null))
                            ->noOptionsMessage('Tidak ada unit untuk tipe soal ini')
                            ->native(false),

                        Select::make('filter_question_sub_unit_id')
                            ->label('Sub Unit (Sub-Bab)')
                            ->options(function (Get $get) {
                                $unitId = $get('filter_question_unit_id');
                                if (! $unitId) {
                                    return [];
                                }
                                return QuestionSubUnit::where('question_unit_id', $unitId)->pluck('name', 'id');
                            })
                            ->placeholder('Semua Sub Unit')
                            ->searchable()
                            ->noOptionsMessage('Tidak ada sub unit untuk unit ini')
                            ->native(false),

                        // buat tingkat kesulitan ini muncul berdasarkan tipe soal yang dipilih, karena tidak semua tipe soal punya kategori
                        Select::make('filter_category')
                            ->label('Tingkat Kesulitan')
                            ->options([
                                'easy' => 'Mudah',
                                'medium' => 'Sedang',
                                'hard' => 'Sulit',
                            ])
                            ->placeholder('Semua Kategori')
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                // Jika kategori dipilih, pastikan tipe soal juga dipilih karena kategori terkait dengan tipe soal
                                if ($get('filter_category') && ! $get('filter_exam_type_id')) {
                                    $set('filter_exam_type_id', Question::where('category', $get('filter_category'))->value('exam_type_id'));
                                }
                            })
                            ->visible(fn(Get $get) => ! empty(Question::where('exam_type_id', $get('filter_exam_type_id'))->whereNotNull('category')->first()))
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Opsi Export')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Select::make('export_format')
                            ->label('Format File')
                            ->options([
                                'excel' => 'Excel (.xlsx) — Data tabular',
                                'pdf'   => 'PDF (.pdf) — Cetak dokumen',
                            ])
                            ->default('excel')
                            ->required()
                            ->native(false),

                        Toggle::make('include_answer_key')
                            ->label('Sertakan kunci jawaban & skor')
                            ->default(true)
                            ->helperText('Jika dinonaktifkan, kunci jawaban tidak ditampilkan pada hasil export.'),
                    ]),
            ])
            ->action(function (array $data, Action $action) {
                $query = self::buildFilteredQuery($data);
                $format = $data['export_format'];
                $includeAnswerKey = (bool) ($data['include_answer_key'] ?? true);
                $filterMeta = self::buildFilterMeta($data);

                if ($format === 'pdf') {
                    return self::handlePdfExport($query, $includeAnswerKey, $filterMeta);
                }

                return self::handleExcelExport($action, $data, $includeAnswerKey);
            });
    }

    /**
     * Build a filtered query from the modal form data.
     */
    private static function buildFilteredQuery(array $data): Builder
    {
        return Question::query()
            ->with(['examType', 'questionUnit', 'questionSubUnit'])
            ->when($data['filter_exam_type_id'] ?? null, fn(Builder $q, $v) => $q->where('exam_type_id', $v))
            ->when($data['filter_question_unit_id'] ?? null, fn(Builder $q, $v) => $q->where('question_unit_id', $v))
            ->when($data['filter_question_sub_unit_id'] ?? null, fn(Builder $q, $v) => $q->where('question_sub_unit_id', $v))
            ->when($data['filter_category'] ?? null, fn(Builder $q, $v) => $q->where('category', $v))
            ->latest();
    }

    /**
     * Build human-readable filter summary for PDF header.
     */
    private static function buildFilterMeta(array $data): array
    {
        $meta = [];

        if ($id = $data['filter_exam_type_id'] ?? null) {
            $meta['Tipe Soal'] = ExamType::find($id)?->name ?? '-';
        }
        if ($id = $data['filter_question_unit_id'] ?? null) {
            $meta['Unit'] = QuestionUnit::find($id)?->name ?? '-';
        }
        if ($id = $data['filter_question_sub_unit_id'] ?? null) {
            $meta['Sub Unit'] = QuestionSubUnit::find($id)?->name ?? '-';
        }
        if ($cat = $data['filter_category'] ?? null) {
            $meta['Kategori'] = match ($cat) {
                'easy'   => 'Mudah',
                'medium' => 'Sedang',
                'hard'   => 'Sulit',
                default  => $cat,
            };
        }

        return $meta;
    }

    private static function handleExcelExport(Action $action, array $data, bool $includeAnswerKey): mixed
    {
        $export = QuestionExcelExport::make('bank-soal');
        $export->setFilterData($data);
        $export->setIncludeAnswerKey($includeAnswerKey);

        return app()->call([$export, 'hydrate'], [
            'livewire' => $action->getLivewire(),
            'records'  => null,
            'formData' => [],
        ])->export();
    }

    private static function handlePdfExport(Builder $query, bool $includeAnswerKey, array $filterMeta): mixed
    {
        $questions = $query->get();

        return app(QuestionPdfExportService::class)->download($questions, $includeAnswerKey, $filterMeta);
    }
}
