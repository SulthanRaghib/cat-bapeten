<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\ExamPackage;
use App\Models\ExamType;
use App\Models\Question;
use App\Models\QuestionSubUnit;
use App\Models\QuestionUnit;
use App\Services\NabSyncService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Support\Enums\Size;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    protected static ?string $title = null;
    protected static ?string $modelLabel = null;

    // ── Helpers to detect exam type of the parent package ──

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Exam Questions');
    }

    public static function getModelLabel(): string
    {
        return __('Question');
    }

    protected function getPackageExamType(): ?ExamType
    {
        /** @var ExamPackage $pkg */
        $pkg = $this->getOwnerRecord();

        return $pkg->examType;
    }

    protected function isTeknis(): bool
    {
        return $this->getPackageExamType()?->isCorrectWrong() ?? false;
    }

    protected function isMansoskul(): bool
    {
        return $this->getPackageExamType()?->isWeighted() ?? false;
    }

    public function form(Schema $form): Schema
    {
        return QuestionResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->columns([
                TextColumn::make('questionUnit.name')
                    ->label(__('Unit'))
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('questionSubUnit.name')
                    ->label(__('Sub Unit'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                // TK. Kesulitan — hanya tampil untuk Teknis (correct_wrong)
                TextColumn::make('category')
                    ->label(__('Difficulty'))
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'easy'   => __('Easy'),
                        'medium' => __('Medium'),
                        'hard'   => __('Hard'),
                        default  => '-',
                    })
                    ->color(fn($state) => match ($state) {
                        'easy'   => 'success',
                        'medium' => 'warning',
                        'hard'   => 'danger',
                        default  => 'gray',
                    })
                    ->visible(fn(): bool => $this->isTeknis())
                    ->toggleable(),

                TextColumn::make('question_text')
                    ->label(__('Question'))
                    ->html()
                    ->formatStateUsing(fn($state) => Str::limit(strip_tags((string) $state), 80)),
            ])
            ->filters([
                // Unit filter — hanya tampilkan unit yang terkait dengan tipe ujian paket ini
                SelectFilter::make('question_unit_id')
                    ->label(__('Unit'))
                    ->options(function (): array {
                        $examTypeId = $this->getPackageExamType()?->id;

                        if (! $examTypeId) {
                            return QuestionUnit::where('is_active', true)->pluck('name', 'id')->toArray();
                        }

                        return QuestionUnit::where('is_active', true)
                            ->where('exam_type_id', $examTypeId)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->placeholder(__('All Units')),

                // Sub-unit filter
                SelectFilter::make('question_sub_unit_id')
                    ->label(__('Sub Unit'))
                    ->options(function (): array {
                        $examTypeId = $this->getPackageExamType()?->id;

                        if (! $examTypeId) {
                            return [];
                        }

                        return QuestionSubUnit::query()
                            ->whereHas('questionUnit', fn(Builder $q) => $q->where('exam_type_id', $examTypeId))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->placeholder(__('All Sub Units')),

                // TK. Kesulitan — hanya tampil untuk Teknis
                SelectFilter::make('category')
                    ->label(__('Difficulty'))
                    ->options(['easy' => __('Easy'), 'medium' => __('Medium'), 'hard' => __('Hard')])
                    ->placeholder(__('All'))
                    ->visible(fn(): bool => $this->isTeknis()),
            ])
            ->headerActions([
                $this->makeGenerateRandomAction(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-m-eye')
                        ->color('gray')
                        ->label(__('View Detail'))
                        ->modalHeading(__('Question Detail'))
                        ->modalContent(fn($record) => view('filament.modals.question-detail', [
                            'record'  => $record,
                            'manager' => $this,
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('Close')),
                    DetachAction::make()
                        ->label(__('Remove Question'))
                        ->modalHeading(__('Remove Question from Package'))
                        ->modalDescription(__('This question will be removed from the exam package but will remain in the Question Bank.'))
                        ->modalSubmitActionLabel(__('Yes, Remove'))
                        ->after(fn() => $this->syncNabAfterChange()),
                ])
                    ->label(__('Action Group'))
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                DetachBulkAction::make()
                    ->label(__('Remove Selected Questions'))
                    ->modalHeading(__('Remove Selected Questions'))
                    ->modalDescription(__('Remove selected questions from this exam package?'))
                    ->modalSubmitActionLabel(__('Yes, Remove'))
                    ->after(fn() => $this->syncNabAfterChange()),
            ])
            ->reorderable('sort_order');
    }

    // =========================================================================
    //  GENERATE ACAK ACTION
    // =========================================================================

    private function makeGenerateRandomAction(): Action
    {
        return Action::make('generate_random')
            ->label(__('Random Questions'))
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->color('primary')
            ->modalHeading(__('Auto-Generate Random Questions'))
            ->modalDescription(__('Specify the number of questions to generate from each unit & sub unit.'))
            ->modalWidth('3xl')
            ->modalSubmitActionLabel(__('Generate Questions'))
            ->schema(fn() => $this->buildGenerateForm())
            ->action(fn(array $data) => $this->executeGenerate($data));
    }

    /**
     * Build the dynamic generation form based on exam type.
     */
    private function buildGenerateForm(): array
    {
        /** @var ExamPackage $pkg */
        $pkg      = $this->getOwnerRecord();
        $examType = $pkg->examType;

        if (! $examType) {
            return [
                Placeholder::make('error')
                    ->content(__('\u26a0 This exam package does not have an Exam Type. Please set the exam type first.'))
                    ->columnSpanFull(),
            ];
        }

        $existingIds = $pkg->questions()->pluck('questions.id')->toArray();
        $units       = QuestionUnit::where('exam_type_id', $examType->id)
            ->where('is_active', true)
            ->with('subUnits')
            ->orderBy('name')
            ->get();

        // ── Stats overview ──────────────────────────────────────────────
        $totalAvailable = Question::where('exam_type_id', $examType->id)
            ->whereNotIn('id', $existingIds)
            ->count();
        $totalInPackage = count($existingIds);

        $statsText = "**{$examType->name}** — "
            . "{$totalInPackage} soal sudah di paket · "
            . "{$totalAvailable} soal tersedia";

        $components = [];

        $components[] = Placeholder::make('stats_overview')
            ->label(__('Summary'))
            ->content(new \Illuminate\Support\HtmlString(
                '<div style="padding:8px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;font-size:13px;color:#1e40af;">'
                    . '<strong>' . e($examType->name) . '</strong> &mdash; '
                    . $totalInPackage . ' soal sudah di paket &bull; '
                    . '<strong>' . $totalAvailable . ' soal tersedia</strong>'
                    . '</div>'
            ))
            ->columnSpanFull();

        // ── Mode toggle ─────────────────────────────────────────────────
        $components[] = Toggle::make('use_unit_distribution')
            ->label(__('Distribute by Unit & Sub Unit'))
            ->helperText(__('Enable to set question count per unit. Disable for a simple total count.'))
            ->default($units->isNotEmpty())
            ->live()
            ->columnSpanFull();

        // ── SIMPLE MODE (flat count) ────────────────────────────────────
        if ($examType->isWeighted()) {
            // Mansoskul: just total count
            $components[] = TextInput::make('total_count')
                ->label(__('Question Count'))
                ->helperText(__('Available: :count questions', ['count' => $totalAvailable]))
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->maxValue($totalAvailable)
                ->visible(fn(Get $get): bool => ! $get('use_unit_distribution'))
                ->columnSpanFull();
        } else {
            // Teknis: per category
            $catCounts = $this->getCategoryCounts($examType->id, $existingIds);

            $components[] = Section::make(__('Count per Difficulty Level'))
                ->description(__('Simple mode — specify count per difficulty level.'))
                ->visible(fn(Get $get): bool => ! $get('use_unit_distribution'))
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('easy_count')
                            ->label(__('Easy'))
                            ->helperText(__('Available: :count', ['count' => $catCounts['easy']]))
                            ->numeric()->default(0)->minValue(0)->maxValue($catCounts['easy']),
                        TextInput::make('medium_count')
                            ->label(__('Medium'))
                            ->helperText(__('Available: :count', ['count' => $catCounts['medium']]))
                            ->numeric()->default(0)->minValue(0)->maxValue($catCounts['medium']),
                        TextInput::make('hard_count')
                            ->label(__('Hard'))
                            ->helperText(__('Available: :count', ['count' => $catCounts['hard']]))
                            ->numeric()->default(0)->minValue(0)->maxValue($catCounts['hard']),
                    ]),
                ]);
        }

        // ── UNIT DISTRIBUTION MODE ──────────────────────────────────────
        if ($units->isEmpty()) {
            $components[] = Placeholder::make('no_units_notice')
                ->content(__('\u2139 No units found for this exam type. Please create units in Master Data first.'))
                ->visible(fn(Get $get): bool => (bool) $get('use_unit_distribution'))
                ->columnSpanFull();
        } else {
            $components[] = Section::make(__('Distribute by Unit'))
                ->description(__('Set the number of questions to draw from each unit & sub unit.'))
                ->visible(fn(Get $get): bool => (bool) $get('use_unit_distribution'))
                ->schema($this->buildUnitFields($examType, $units, $existingIds));
        }

        // ── Soal tanpa unit ─────────────────────────────────────────────
        $noUnitCount = Question::where('exam_type_id', $examType->id)
            ->whereNull('question_unit_id')
            ->whereNotIn('id', $existingIds)
            ->count();

        if ($noUnitCount > 0) {
            $components[] = Section::make(__('Questions Without Unit'))
                ->description(__('There are :count questions without an assigned unit.', ['count' => $noUnitCount]))
                ->visible(fn(Get $get): bool => (bool) $get('use_unit_distribution'))
                ->collapsed()
                ->schema(
                    $this->buildNoUnitFields($examType, $existingIds, $noUnitCount)
                );
        }

        return $components;
    }

    /**
     * Build form fields for each unit and its sub-units.
     */
    private function buildUnitFields(ExamType $examType, Collection $units, array $existingIds): array
    {
        $fields = [];

        foreach ($units as $unit) {
            $subUnits    = $unit->subUnits;
            $unitAvail   = Question::where('exam_type_id', $examType->id)
                ->where('question_unit_id', $unit->id)
                ->whereNotIn('id', $existingIds)
                ->count();

            // Sub unit fields
            $subFields = [];
            foreach ($subUnits as $sub) {
                $subAvail = Question::where('exam_type_id', $examType->id)
                    ->where('question_sub_unit_id', $sub->id)
                    ->whereNotIn('id', $existingIds)
                    ->count();

                if ($examType->isCorrectWrong()) {
                    // Teknis: per category per sub-unit
                    $subCats = $this->getCategoryCounts($examType->id, $existingIds, null, $sub->id);
                    $subFields[] = Section::make($sub->name)
                        ->description("Tersedia: {$subAvail} soal")
                        ->compact()
                        ->collapsed()
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make("unit_{$unit->id}_sub_{$sub->id}_easy")
                                    ->label('Mudah')
                                    ->helperText("{$subCats['easy']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($subCats['easy']),
                                TextInput::make("unit_{$unit->id}_sub_{$sub->id}_medium")
                                    ->label('Sedang')
                                    ->helperText("{$subCats['medium']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($subCats['medium']),
                                TextInput::make("unit_{$unit->id}_sub_{$sub->id}_hard")
                                    ->label('Sulit')
                                    ->helperText("{$subCats['hard']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($subCats['hard']),
                            ]),
                        ]);
                } else {
                    // Mansoskul: just count per sub-unit
                    $subFields[] = TextInput::make("unit_{$unit->id}_sub_{$sub->id}_count")
                        ->label($sub->name)
                        ->helperText("Tersedia: {$subAvail}")
                        ->numeric()->default(0)->minValue(0)->maxValue($subAvail);
                }
            }

            // Questions in this unit but no sub-unit
            $unitOnlyAvail = Question::where('exam_type_id', $examType->id)
                ->where('question_unit_id', $unit->id)
                ->whereNull('question_sub_unit_id')
                ->whereNotIn('id', $existingIds)
                ->count();

            if ($unitOnlyAvail > 0) {
                if ($examType->isCorrectWrong()) {
                    $unitOnlyCats = $this->getCategoryCounts($examType->id, $existingIds, $unit->id, null, true);
                    $subFields[] = Section::make('Tanpa Sub Unit')
                        ->description("{$unitOnlyAvail} soal tanpa sub unit")
                        ->compact()
                        ->collapsed()
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make("unit_{$unit->id}_nosub_easy")
                                    ->label('Mudah')
                                    ->helperText("{$unitOnlyCats['easy']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($unitOnlyCats['easy']),
                                TextInput::make("unit_{$unit->id}_nosub_medium")
                                    ->label('Sedang')
                                    ->helperText("{$unitOnlyCats['medium']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($unitOnlyCats['medium']),
                                TextInput::make("unit_{$unit->id}_nosub_hard")
                                    ->label('Sulit')
                                    ->helperText("{$unitOnlyCats['hard']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($unitOnlyCats['hard']),
                            ]),
                        ]);
                } else {
                    $subFields[] = TextInput::make("unit_{$unit->id}_nosub_count")
                        ->label('Tanpa Sub Unit')
                        ->helperText("Tersedia: {$unitOnlyAvail}")
                        ->numeric()->default(0)->minValue(0)->maxValue($unitOnlyAvail);
                }
            }

            $fields[] = Section::make($unit->name)
                ->description("Total tersedia: {$unitAvail} soal")
                ->icon('heroicon-o-folder')
                ->collapsible()
                ->schema($subFields);
        }

        return $fields;
    }

    /**
     * Build fields for questions that have no unit assigned.
     */
    private function buildNoUnitFields(ExamType $examType, array $existingIds, int $noUnitCount): array
    {
        if ($examType->isCorrectWrong()) {
            $cats = $this->getCategoryCounts($examType->id, $existingIds, null, null, false, true);
            return [
                Grid::make(3)->schema([
                    TextInput::make('nounit_easy')
                        ->label('Mudah')
                        ->helperText("{$cats['easy']} tersedia")
                        ->numeric()->default(0)->minValue(0)->maxValue($cats['easy']),
                    TextInput::make('nounit_medium')
                        ->label('Sedang')
                        ->helperText("{$cats['medium']} tersedia")
                        ->numeric()->default(0)->minValue(0)->maxValue($cats['medium']),
                    TextInput::make('nounit_hard')
                        ->label('Sulit')
                        ->helperText("{$cats['hard']} tersedia")
                        ->numeric()->default(0)->minValue(0)->maxValue($cats['hard']),
                ]),
            ];
        }

        return [
            TextInput::make('nounit_count')
                ->label('Jumlah')
                ->helperText("Tersedia: {$noUnitCount}")
                ->numeric()->default(0)->minValue(0)->maxValue($noUnitCount),
        ];
    }

    // =========================================================================
    //  EXECUTE GENERATION
    // =========================================================================

    private function executeGenerate(array $data): void
    {
        /** @var ExamPackage $pkg */
        $pkg         = $this->getOwnerRecord();
        $examType    = $pkg->examType;
        $existingIds = $pkg->questions()->pluck('questions.id')->toArray();
        $useUnit     = (bool) ($data['use_unit_distribution'] ?? false);
        $idsToAttach = collect();

        if (! $useUnit) {
            // ── SIMPLE MODE ─────────────────────────────────────────────
            $idsToAttach = $this->collectSimple($data, $examType, $existingIds);
        } else {
            // ── UNIT DISTRIBUTION MODE ──────────────────────────────────
            $idsToAttach = $this->collectFromUnits($data, $examType, $existingIds);
        }

        if ($idsToAttach->isEmpty()) {
            Notification::make()
                ->title('Tidak ada perubahan')
                ->body('Tidak ada soal yang ditambahkan (jumlah 0 atau stok habis).')
                ->warning()
                ->send();
            return;
        }

        $pkg->questions()->attach($idsToAttach->toArray());

        // Auto-sync NAB config after adding questions
        $this->syncNabAfterChange();

        Notification::make()
            ->title(__('Success'))
            ->body(__(':count questions successfully added randomly to the exam package.', ['count' => $idsToAttach->count()]))
            ->success()
            ->send();
    }

    /**
     * Simple mode: flat count (Mansoskul) or per-category (Teknis).
     */
    private function collectSimple(array $data, ExamType $examType, array $existingIds): Collection
    {
        $ids = collect();
        $base = Question::where('exam_type_id', $examType->id)->whereNotIn('id', $existingIds);

        if ($examType->isWeighted()) {
            $count = (int) ($data['total_count'] ?? 0);
            if ($count > 0) {
                $ids = $ids->merge(
                    (clone $base)->inRandomOrder()->limit($count)->pluck('id')
                );
            }
        } else {
            foreach (['easy', 'medium', 'hard'] as $cat) {
                $count = (int) ($data["{$cat}_count"] ?? 0);
                if ($count > 0) {
                    $ids = $ids->merge(
                        (clone $base)->where('category', $cat)->inRandomOrder()->limit($count)->pluck('id')
                    );
                }
            }
        }

        return $ids;
    }

    /**
     * Unit distribution mode: collect IDs per unit/sub-unit/category.
     */
    private function collectFromUnits(array $data, ExamType $examType, array $existingIds): Collection
    {
        $ids  = collect();
        $base = Question::where('exam_type_id', $examType->id)->whereNotIn('id', $existingIds);

        $units = QuestionUnit::where('exam_type_id', $examType->id)
            ->where('is_active', true)
            ->with('subUnits')
            ->get();

        foreach ($units as $unit) {
            // Sub units
            foreach ($unit->subUnits as $sub) {
                $ids = $ids->merge(
                    $this->pickQuestions($data, $base, $examType, "unit_{$unit->id}_sub_{$sub->id}", $unit->id, $sub->id)
                );
            }

            // Unit-only (no sub unit)
            $ids = $ids->merge(
                $this->pickQuestions($data, $base, $examType, "unit_{$unit->id}_nosub", $unit->id, null, true)
            );
        }

        // No unit at all
        $ids = $ids->merge(
            $this->pickQuestions($data, $base, $examType, 'nounit', null, null, false, true)
        );

        // Deduplicate (safety)
        return $ids->unique()->values();
    }

    /**
     * Pick random questions based on the form field prefix and query scope.
     */
    private function pickQuestions(
        array $data,
        Builder $base,
        ExamType $examType,
        string $prefix,
        ?int $unitId,
        ?int $subUnitId,
        bool $nullSubUnit = false,
        bool $nullUnit = false,
    ): Collection {
        $ids = collect();

        $scoped = clone $base;
        if ($nullUnit) {
            $scoped->whereNull('question_unit_id');
        } elseif ($unitId) {
            $scoped->where('question_unit_id', $unitId);
        }
        if ($nullSubUnit) {
            $scoped->whereNull('question_sub_unit_id');
        } elseif ($subUnitId) {
            $scoped->where('question_sub_unit_id', $subUnitId);
        }

        if ($examType->isCorrectWrong()) {
            foreach (['easy', 'medium', 'hard'] as $cat) {
                $count = (int) ($data["{$prefix}_{$cat}"] ?? 0);
                if ($count > 0) {
                    $ids = $ids->merge(
                        (clone $scoped)->where('category', $cat)->inRandomOrder()->limit($count)->pluck('id')
                    );
                }
            }
        } else {
            $count = (int) ($data["{$prefix}_count"] ?? 0);
            if ($count > 0) {
                $ids = $ids->merge(
                    (clone $scoped)->inRandomOrder()->limit($count)->pluck('id')
                );
            }
        }

        return $ids;
    }

    // =========================================================================
    //  HELPERS
    // =========================================================================

    /**
     * Count available questions per category.
     */
    private function getCategoryCounts(
        int $examTypeId,
        array $existingIds,
        ?int $unitId = null,
        ?int $subUnitId = null,
        bool $nullSubUnit = false,
        bool $nullUnit = false,
    ): array {
        $query = Question::where('exam_type_id', $examTypeId)
            ->whereNotIn('id', $existingIds);

        if ($nullUnit) {
            $query->whereNull('question_unit_id');
        } elseif ($unitId) {
            $query->where('question_unit_id', $unitId);
        }
        if ($nullSubUnit) {
            $query->whereNull('question_sub_unit_id');
        } elseif ($subUnitId) {
            $query->where('question_sub_unit_id', $subUnitId);
        }

        return [
            'easy'   => (clone $query)->where('category', 'easy')->count(),
            'medium' => (clone $query)->where('category', 'medium')->count(),
            'hard'   => (clone $query)->where('category', 'hard')->count(),
        ];
    }

    /**
     * Auto-sync `unit_scoring_configs` after any question attach/detach.
     * Only runs for Mansoskul (weighted) exam packages.
     */
    protected function syncNabAfterChange(): void
    {
        /** @var ExamPackage $pkg */
        $pkg = $this->getOwnerRecord();

        if ($pkg->examType?->evaluation_method !== 'weighted') {
            return;
        }

        app(NabSyncService::class)->smartSync($pkg);
    }

    /**
     * Format the scoring configuration for display.
     */
    public function formatScoringConfig(Question $question): string
    {
        return \App\Helpers\ScoringConfigFormatter::format($question);
    }
}
