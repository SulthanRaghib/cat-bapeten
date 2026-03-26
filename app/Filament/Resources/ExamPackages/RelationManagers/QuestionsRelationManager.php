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
        /** @var ExamPackage $pkg */
        $pkg = $this->getOwnerRecord();
        $totalInPackage = $pkg->questions()->count();
        $examType = $pkg->examType;

        // Build stats for table description
        $descriptionHtml = $this->buildTableDescription($totalInPackage, $examType, $pkg);

        return $table
            ->description(new \Illuminate\Support\HtmlString($descriptionHtml))
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
                        'easy' => __('Easy'),
                        'medium' => __('Medium'),
                        'hard' => __('Hard'),
                        default => '-',
                    })
                    ->color(fn($state) => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        default => 'gray',
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

                        if (!$examTypeId) {
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

                        if (!$examTypeId) {
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
                            'record' => $record,
                            'manager' => $this,
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('Close')),
                    DetachAction::make()
                        ->label(__('Remove Question'))
                        ->icon('heroicon-m-trash')
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
                    ->icon('heroicon-m-trash')
                    ->modalHeading(__('Remove Selected Questions'))
                    ->modalDescription(__('Remove selected questions from this exam package?'))
                    ->modalSubmitActionLabel(__('Yes, Remove'))
                    ->after(fn() => $this->syncNabAfterChange()),
            ])
            ->reorderable('sort_order');
    }

    // =========================================================================
    //  TABLE DESCRIPTION (QUESTION COUNT INFO)
    // =========================================================================

    private function buildTableDescription(int $totalInPackage, ?ExamType $examType, ExamPackage $pkg): string
    {
        $totalAvailable = 0;
        $existingIds = [];
        if ($examType) {
            $existingIds = $pkg->questions()->pluck('questions.id')->toArray();
            $totalAvailable = Question::where('exam_type_id', $examType->id)
                ->whereNotIn('id', $existingIds)
                ->count();
        }

        $uid = 'qi-' . $pkg->id;

        $html = '<style>'
            . ".{$uid}{padding:10px 14px;border-radius:10px;font-size:13px;display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start;background:rgba(248,250,252,0.8);border:1px solid #e2e8f0;}"
            . ".dark .{$uid}{background:rgba(30,41,59,0.5);border-color:#334155;}"
            . ".{$uid} .qi-col{display:flex;flex-direction:column;gap:6px;}"
            . ".{$uid} .qi-head{display:flex;align-items:center;gap:6px;}"
            . ".{$uid} .qi-label{font-weight:600;color:#475569;}"
            . ".dark .{$uid} .qi-label{color:#94a3b8;}"
            . ".{$uid} .qi-num-blue{background:#dbeafe;color:#1e40af;font-weight:800;font-size:14px;padding:2px 10px;border-radius:9999px;}"
            . ".dark .{$uid} .qi-num-blue{background:#1e3a5f;color:#93c5fd;}"
            . ".{$uid} .qi-num-green{background:#dcfce7;color:#166534;font-weight:800;font-size:14px;padding:2px 10px;border-radius:9999px;}"
            . ".dark .{$uid} .qi-num-green{background:#14532d;color:#86efac;}"
            . ".{$uid} .qi-num-red{background:#fef2f2;color:#991b1b;font-weight:800;font-size:14px;padding:2px 10px;border-radius:9999px;}"
            . ".dark .{$uid} .qi-num-red{background:#450a0a;color:#fca5a5;}"
            . ".{$uid} .qi-tags{display:flex;gap:4px;flex-wrap:wrap;}"
            . ".{$uid} .qi-t{font-weight:700;font-size:10px;padding:1px 7px;border-radius:9999px;}"
            . ".{$uid} .qi-e{background:#dcfce7;color:#166534;}"
            . ".dark .{$uid} .qi-e{background:#14532d80;color:#86efac;}"
            . ".{$uid} .qi-m{background:#fef3c7;color:#92400e;}"
            . ".dark .{$uid} .qi-m{background:#78350f80;color:#fcd34d;}"
            . ".{$uid} .qi-h{background:#fee2e2;color:#991b1b;}"
            . ".dark .{$uid} .qi-h{background:#450a0a80;color:#fca5a5;}"
            . ".{$uid} .qi-sep{color:#cbd5e1;align-self:stretch;border-left:1px solid #e2e8f0;}"
            . ".dark .{$uid} .qi-sep{border-color:#334155;}"
            . '</style>';

        $html .= '<div class="' . $uid . '">';

        // Column 1: In Package
        $inPkgBadge = $totalInPackage > 0 ? 'qi-num-blue' : 'qi-num-red';
        $html .= '<div class="qi-col">'
            . '<div class="qi-head">'
            . '<span>📦</span>'
            . '<span class="qi-label">' . e(__('Total Questions in Package')) . ':</span>'
            . '<span class="' . $inPkgBadge . '">' . $totalInPackage . '</span>'
            . '</div>';

        if ($examType && $examType->isCorrectWrong()) {
            $easyInPkg = $pkg->questions()->where('category', 'easy')->count();
            $mediumInPkg = $pkg->questions()->where('category', 'medium')->count();
            $hardInPkg = $pkg->questions()->where('category', 'hard')->count();

            $html .= '<div class="qi-tags">'
                . '<span class="qi-t qi-e">' . __('Easy') . ' ' . $easyInPkg . '</span>'
                . '<span class="qi-t qi-m">' . __('Medium') . ' ' . $mediumInPkg . '</span>'
                . '<span class="qi-t qi-h">' . __('Hard') . ' ' . $hardInPkg . '</span>'
                . '</div>';
        }

        $html .= '</div>';

        // Separator
        $html .= '<div class="qi-sep"></div>';

        // Column 2: In Bank
        $availBadge = $totalAvailable > 0 ? 'qi-num-green' : 'qi-num-red';
        $html .= '<div class="qi-col">'
            . '<div class="qi-head">'
            . '<span>🏦</span>'
            . '<span class="qi-label">' . e(__('Available in Question Bank')) . ':</span>'
            . '<span class="' . $availBadge . '">' . $totalAvailable . '</span>'
            . '</div>';

        if ($examType && $examType->isCorrectWrong()) {
            $easyAvail = Question::where('exam_type_id', $examType->id)->where('category', 'easy')->whereNotIn('id', $existingIds)->count();
            $mediumAvail = Question::where('exam_type_id', $examType->id)->where('category', 'medium')->whereNotIn('id', $existingIds)->count();
            $hardAvail = Question::where('exam_type_id', $examType->id)->where('category', 'hard')->whereNotIn('id', $existingIds)->count();

            $html .= '<div class="qi-tags">'
                . '<span class="qi-t qi-e">' . __('Easy') . ' ' . $easyAvail . '</span>'
                . '<span class="qi-t qi-m">' . __('Medium') . ' ' . $mediumAvail . '</span>'
                . '<span class="qi-t qi-h">' . __('Hard') . ' ' . $hardAvail . '</span>'
                . '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
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
        $pkg = $this->getOwnerRecord();
        $examType = $pkg->examType;

        if (!$examType) {
            return [
                Placeholder::make('error')
                    ->content(__('\u26a0 This exam package does not have an Exam Type. Please set the exam type first.'))
                    ->columnSpanFull(),
            ];
        }

        $existingIds = $pkg->questions()->pluck('questions.id')->toArray();
        $units = QuestionUnit::where('exam_type_id', $examType->id)
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
                '<style>'
                . '.qg-stats{padding:8px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;font-size:13px;color:#1e40af;}'
                . '.dark .qg-stats{background:rgba(30,58,95,0.4);border-color:#1e3a5f;color:#93c5fd;}'
                . '.qg-footer{padding:12px 16px;border-radius:10px;font-size:13px;}'
                . '.qg-footer-slate{background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;}'
                . '.dark .qg-footer-slate{background:rgba(30,41,59,0.5);border-color:#334155;color:#94a3b8;}'
                . '.qg-footer-blue{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;}'
                . '.dark .qg-footer-blue{background:rgba(30,58,95,0.4);border-color:#1e3a5f;color:#93c5fd;}'
                . '.qg-footer-red{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}'
                . '.dark .qg-footer-red{background:rgba(69,10,10,0.4);border-color:#450a0a;color:#fca5a5;}'
                . '.qg-badge{font-weight:800;font-size:15px;padding:4px 14px;border-radius:9999px;letter-spacing:-0.5px;}'
                . '.qg-badge-slate{background:#94a3b8;color:white;}'
                . '.dark .qg-badge-slate{background:#475569;color:#e2e8f0;}'
                . '.qg-badge-blue{background:#2563eb;color:white;}'
                . '.dark .qg-badge-blue{background:#1d4ed8;color:#dbeafe;}'
                . '.qg-badge-red{background:#dc2626;color:white;}'
                . '.dark .qg-badge-red{background:#991b1b;color:#fecaca;}'
                . '.qg-label{font-weight:700;}'
                . '.qg-sub{font-size:12px;color:#64748b;}'
                . '.dark .qg-sub{color:#94a3b8;}'
                . '.qg-sub strong{color:#111827;}'
                . '.dark .qg-sub strong{color:#e2e8f0;}'
                . '</style>'
                . '<div class="qg-stats">'
                . '<strong>' . e($examType->name) . '</strong> &mdash; '
                . e(__('In Package')) . ': ' . $totalInPackage . ' &bull; '
                . e(__('In Bank')) . ': <strong>' . $totalAvailable . '</strong>'
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
                ->live(debounce: 500)
                ->visible(fn(Get $get): bool => !$get('use_unit_distribution'))
                ->columnSpanFull();
        } else {
            // Teknis: per category
            $catCounts = $this->getCategoryCounts($examType->id, $existingIds);

            $components[] = Section::make(__('Count per Difficulty Level'))
                ->description(__('Simple mode — specify count per difficulty level.'))
                ->visible(fn(Get $get): bool => !$get('use_unit_distribution'))
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('easy_count')
                            ->label(__('Easy'))
                            ->helperText(__('Available: :count', ['count' => $catCounts['easy']]))
                            ->numeric()->default(0)->minValue(0)->maxValue($catCounts['easy'])
                            ->live(debounce: 500),
                        TextInput::make('medium_count')
                            ->label(__('Medium'))
                            ->helperText(__('Available: :count', ['count' => $catCounts['medium']]))
                            ->numeric()->default(0)->minValue(0)->maxValue($catCounts['medium'])
                            ->live(debounce: 500),
                        TextInput::make('hard_count')
                            ->label(__('Hard'))
                            ->helperText(__('Available: :count', ['count' => $catCounts['hard']]))
                            ->numeric()->default(0)->minValue(0)->maxValue($catCounts['hard'])
                            ->live(debounce: 500),
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

        // ── FOOTER: Live summary of total selected questions ────────────
        $components[] = Placeholder::make('selection_summary')
            ->hiddenLabel()
            ->content(function (Get $get) use ($totalAvailable, $totalInPackage): \Illuminate\Support\HtmlString {
                // Determine which mode is active
                $useUnit = (bool) $get('use_unit_distribution');
                $allState = $get('.');
                $totalSelected = 0;

                // Fields to always skip
                $skipFields = ['use_unit_distribution', 'stats_overview', 'selection_summary', 'no_units_notice', 'error'];

                // Simple mode fields (easy_count, medium_count, hard_count, total_count)
                $simpleFields = ['easy_count', 'medium_count', 'hard_count', 'total_count'];

                if (is_array($allState)) {
                    foreach ($allState as $key => $value) {
                        if (in_array($key, $skipFields, true)) {
                            continue;
                        }
                        if (!is_numeric($value)) {
                            continue;
                        }

                        // Only sum fields belonging to the active mode
                        $isSimpleField = in_array($key, $simpleFields, true);

                        if ($useUnit && $isSimpleField) {
                            continue; // Skip simple fields when unit distribution is ON
                        }
                        if (!$useUnit && !$isSimpleField) {
                            continue; // Skip unit fields when simple mode is ON
                        }

                        $totalSelected += (int) $value;
                    }
                }

                $afterGenerate = $totalInPackage + $totalSelected;

                if ($totalSelected === 0) {
                    $footerClass = 'qg-footer qg-footer-slate';
                    $badgeClass = 'qg-badge qg-badge-slate';
                    $icon = '📋';
                } elseif ($totalSelected <= $totalAvailable) {
                    $footerClass = 'qg-footer qg-footer-blue';
                    $badgeClass = 'qg-badge qg-badge-blue';
                    $icon = '✅';
                } else {
                    $footerClass = 'qg-footer qg-footer-red';
                    $badgeClass = 'qg-badge qg-badge-red';
                    $icon = '⚠️';
                }

                $selectedLabel = __('Questions to Generate');
                $afterLabel = __('Total After Generate');
                $availLabel = __('Available Questions');

                return new \Illuminate\Support\HtmlString(
                    '<div class="' . $footerClass . '">'
                    . '<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">'
                    . '<div style="display:flex;align-items:center;gap:8px;">'
                    . '<span style="font-size:18px;">' . $icon . '</span>'
                    . '<span class="qg-label">' . e($selectedLabel) . '</span>'
                    . '</div>'
                    . '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">'
                    . '<span class="' . $badgeClass . '">' . $totalSelected . '</span>'
                    . '<span class="qg-sub">' . e($afterLabel) . ': <strong>' . $afterGenerate . '</strong></span>'
                    . '<span class="qg-sub">' . e($availLabel) . ': <strong>' . $totalAvailable . '</strong></span>'
                    . '</div>'
                    . '</div>'
                    . '</div>'
                );
            })
            ->live()
            ->columnSpanFull();

        return $components;
    }

    /**
     * Build form fields for each unit and its sub-units.
     */
    private function buildUnitFields(ExamType $examType, Collection $units, array $existingIds): array
    {
        $fields = [];

        foreach ($units as $unit) {
            $subUnits = $unit->subUnits;
            $unitAvail = Question::where('exam_type_id', $examType->id)
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
                                    ->numeric()->default(0)->minValue(0)->maxValue($subCats['easy'])
                                    ->live(debounce: 500),
                                TextInput::make("unit_{$unit->id}_sub_{$sub->id}_medium")
                                    ->label('Sedang')
                                    ->helperText("{$subCats['medium']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($subCats['medium'])
                                    ->live(debounce: 500),
                                TextInput::make("unit_{$unit->id}_sub_{$sub->id}_hard")
                                    ->label('Sulit')
                                    ->helperText("{$subCats['hard']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($subCats['hard'])
                                    ->live(debounce: 500),
                            ]),
                        ]);
                } else {
                    // Mansoskul: just count per sub-unit
                    $subFields[] = TextInput::make("unit_{$unit->id}_sub_{$sub->id}_count")
                        ->label($sub->name)
                        ->helperText("Tersedia: {$subAvail}")
                        ->numeric()->default(0)->minValue(0)->maxValue($subAvail)
                        ->live(debounce: 500);
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
                                    ->numeric()->default(0)->minValue(0)->maxValue($unitOnlyCats['easy'])
                                    ->live(debounce: 500),
                                TextInput::make("unit_{$unit->id}_nosub_medium")
                                    ->label('Sedang')
                                    ->helperText("{$unitOnlyCats['medium']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($unitOnlyCats['medium'])
                                    ->live(debounce: 500),
                                TextInput::make("unit_{$unit->id}_nosub_hard")
                                    ->label('Sulit')
                                    ->helperText("{$unitOnlyCats['hard']} tersedia")
                                    ->numeric()->default(0)->minValue(0)->maxValue($unitOnlyCats['hard'])
                                    ->live(debounce: 500),
                            ]),
                        ]);
                } else {
                    $subFields[] = TextInput::make("unit_{$unit->id}_nosub_count")
                        ->label('Tanpa Sub Unit')
                        ->helperText("Tersedia: {$unitOnlyAvail}")
                        ->numeric()->default(0)->minValue(0)->maxValue($unitOnlyAvail)
                        ->live(debounce: 500);
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
        $pkg = $this->getOwnerRecord();
        $examType = $pkg->examType;
        $existingIds = $pkg->questions()->pluck('questions.id')->toArray();
        $useUnit = (bool) ($data['use_unit_distribution'] ?? false);
        $idsToAttach = collect();

        if (!$useUnit) {
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
        $ids = collect();
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
            'easy' => (clone $query)->where('category', 'easy')->count(),
            'medium' => (clone $query)->where('category', 'medium')->count(),
            'hard' => (clone $query)->where('category', 'hard')->count(),
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
