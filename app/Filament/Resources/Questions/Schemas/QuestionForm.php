<?php

declare(strict_types=1);

namespace App\Filament\Resources\Questions\Schemas;

use App\Models\ExamType;
use App\Models\QuestionSubUnit;
use App\Models\QuestionUnit;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class QuestionForm
{
    /**
     * Cached map of exam-type evaluation methods to avoid N+1 queries.
     *
     * @var array<int, string|null>
     */
    private static array $evaluationMethodCache = [];

    /**
     * Resolve evaluation method for the currently selected exam type (cached).
     */
    private static function getEvaluationMethod(Get $get, string $prefix = ''): ?string
    {
        $examTypeId = $get($prefix . 'exam_type_id');

        if (! $examTypeId) {
            return null;
        }

        return self::$evaluationMethodCache[$examTypeId]
            ??= ExamType::find($examTypeId)?->evaluation_method;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Question Details'))
                    ->description(__('Fill in the question information. Select the type, unit, and sub unit first before filling in the question text and answer choices.'))
                    ->icon('heroicon-o-document-text')
                    ->columns(12)
                    ->schema([
                        Select::make('exam_type_id')
                            ->label(__('Question Type'))
                            ->validationAttribute('Tipe Soal')
                            ->options(fn() => ExamType::query()
                                ->where('is_active', true)
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(4)
                            ->live()
                            ->native(false)
                            ->afterStateUpdated(function (Set $set): void {
                                $set('question_unit_id', null);
                                $set('question_sub_unit_id', null);
                            }),

                        // ── Dynamic Unit / Sub-Unit (master-data driven) ────────

                        Select::make('question_unit_id')
                            ->label(__('Unit (Material/Chapter)'))
                            ->validationAttribute('Unit Soal')
                            ->options(fn(Get $get) => QuestionUnit::query()
                                ->where('exam_type_id', $get('exam_type_id'))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->columnSpan(4)
                            ->native(false)
                            ->required(fn(Get $get) => filled($get('exam_type_id')))
                            ->visible(fn(Get $get): bool => filled($get('exam_type_id')))
                            ->afterStateUpdated(fn(Set $set) => $set('question_sub_unit_id', null))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('New Unit Name'))
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data, Get $get): int {
                                $examTypeId = $get('exam_type_id');

                                if (! $examTypeId) {
                                    throw new \RuntimeException(__('Select Question Type first before creating a new Unit.'));
                                }

                                return QuestionUnit::create([
                                    'exam_type_id' => $examTypeId,
                                    'name'         => $data['name'],
                                    'is_active'    => true,
                                ])->getKey();
                            })
                            ->createOptionAction(function ($action) {
                                return $action
                                    ->label(__('Add New Unit'))
                                    ->modalHeading(__('Create New Unit'))
                                    ->modalSubmitActionLabel(__('Save Unit'))
                                    ->modalWidth('md')
                                    ->icon('heroicon-o-plus')
                                    ->color('primary');
                            })
                            ->editOptionForm([
                                TextInput::make('name')
                                    ->label(__('Unit Name'))
                                    ->required()
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->hidden()
                                    ->default(true),
                            ])
                            ->editOptionAction(function ($action) {
                                return $action
                                    ->label(__('Edit Unit'))
                                    ->modalHeading(__('Edit Unit Data'))
                                    ->modalSubmitActionLabel(__('Update'))
                                    ->modalWidth('md')
                                    ->icon('heroicon-o-pencil')
                                    ->color('warning');
                            })
                            ->fillEditOptionActionFormUsing(function (Select $component): ?array {
                                $unit = QuestionUnit::find($component->getState());
                                if (! $unit) {
                                    return null;
                                }
                                return [
                                    'name'      => $unit->name,
                                    'is_active' => $unit->is_active,
                                ];
                            })
                            ->updateOptionUsing(function (array $data, Select $component): void {
                                QuestionUnit::findOrFail((int) $component->getState())->update([
                                    'name'      => $data['name'],
                                    'is_active' => $data['is_active'] ?? true,
                                ]);
                            })
                            ->noOptionsMessage(function (Get $get): string {
                                if (! filled($get('exam_type_id'))) {
                                    return __('Select Question Type first');
                                }
                                return __('No active units for this question type. Please create a unit first.');
                            }),

                        Select::make('question_sub_unit_id')
                            ->label(__('Sub Unit (Sub-Chapter)'))
                            ->validationAttribute('Sub Unit Soal')
                            ->options(fn(Get $get) => QuestionSubUnit::query()
                                ->where('question_unit_id', $get('question_unit_id'))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->columnSpan(4)
                            ->native(false)
                            ->required(fn(Get $get) => filled($get('question_unit_id')))
                            ->visible(fn(Get $get): bool => filled($get('exam_type_id')))
                            ->disabled(fn(Get $get): bool => ! filled($get('question_unit_id')))
                            ->helperText(fn(Get $get): ?string => ! filled($get('question_unit_id')) ? __('Select Unit first') : null)
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('New Sub Unit Name'))
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionAction(function ($action) {
                                return $action
                                    ->label(__('Add New Sub Unit'))
                                    ->modalHeading(__('Create New Sub Unit'))
                                    ->modalSubmitActionLabel(__('Save Sub Unit'))
                                    ->modalWidth('md')
                                    ->icon('heroicon-o-plus')
                                    ->color('primary');
                            })
                            ->createOptionUsing(function (array $data, Get $get): int {
                                $questionUnitId = $get('question_unit_id');

                                if (! $questionUnitId) {
                                    throw new \RuntimeException(__('Select Unit first before creating a new Sub Unit.'));
                                }

                                return QuestionSubUnit::create([
                                    'question_unit_id' => $questionUnitId,
                                    'name'             => $data['name'],
                                ])->getKey();
                            })
                            ->editOptionForm([
                                TextInput::make('name')
                                    ->label(__('Sub Unit Name'))
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->editOptionAction(function ($action) {
                                return $action
                                    ->label(__('Edit Sub Unit'))
                                    ->modalHeading(__('Edit Sub Unit Data'))
                                    ->modalSubmitActionLabel(__('Update'))
                                    ->modalWidth('md')
                                    ->icon('heroicon-o-pencil')
                                    ->color('warning');
                            })
                            ->fillEditOptionActionFormUsing(function (Select $component): ?array {
                                $sub = QuestionSubUnit::find($component->getState());
                                if (! $sub) {
                                    return null;
                                }
                                return ['name' => $sub->name];
                            })
                            ->updateOptionUsing(function (array $data, Select $component): void {
                                QuestionSubUnit::findOrFail((int) $component->getState())->update([
                                    'name' => $data['name'],
                                ]);
                            })
                            ->noOptionsMessage(function (Get $get): string {
                                if (! filled($get('question_unit_id'))) {
                                    return __('Select Unit first');
                                }
                                return __('No sub units for this unit. Please create a sub unit first.');
                            }),

                        // ── Conditional: Technical difficulty category ──────────

                        Select::make('category')
                            ->label(__('Difficulty Level'))
                            ->validationAttribute('Tingkat Kesulitan')
                            ->options([
                                'easy'   => __('Easy'),
                                'medium' => __('Medium'),
                                'hard'   => __('Hard'),
                            ])
                            ->live()
                            ->visible(fn(Get $get) => self::getEvaluationMethod($get) === 'correct_wrong')
                            ->required(fn(Get $get) => self::getEvaluationMethod($get) === 'correct_wrong')
                            ->columnSpan(4)
                            ->native(false),

                    ]),

                Section::make(__('Question Content & Discussion'))
                    ->schema([
                        View::make('filament.components.math-helper'),

                        RichEditor::make('question_text')
                            ->label(__('Question'))
                            ->required()
                            ->live(debounce: '1500ms')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('question-images')
                            ->columnSpanFull(),

                        View::make('filament.components.image-insert-widget'),

                        RichEditor::make('explanation')
                            ->label(__('Answer Discussion'))
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('question-images')
                            ->columnSpanFull()
                            ->hidden(),
                    ]),

                Section::make(__('Answers'))
                    ->description(__('Add answer choices. For Technical questions, mark one answer as the key. For Mansoskul, fill in the point weight for each option.'))
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('options')
                            ->live()
                            ->afterStateUpdated(function () {})
                            ->schema([
                                RichEditor::make('answer_text')
                                    ->label(__('Answer Text'))
                                    ->required()
                                    ->live(debounce: '2000ms')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('question-images')
                                    ->columnSpanFull(),

                                View::make('filament.components.image-insert-widget'),

                                // correct_wrong: Correct/Incorrect toggle
                                Toggle::make('is_correct')
                                    ->label(__('Answer Key (Correct = 1 Point)'))
                                    ->default(false)
                                    ->visible(fn(Get $get) => self::getEvaluationMethod($get, '../../') === 'correct_wrong')
                                    ->reactive(),

                                // weighted: Explicit Score input
                                TextInput::make('score')
                                    ->label(__('Score Weight'))
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->visible(fn(Get $get) => self::getEvaluationMethod($get, '../../') === 'weighted')
                                    ->required(fn(Get $get) => self::getEvaluationMethod($get, '../../') === 'weighted'),

                                Hidden::make('is_active')
                                    ->default(true),
                            ])
                            ->columns(1)
                            ->defaultItems(4)
                            // ->grid(2)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => is_string($state['answer_text'] ?? null) ? strip_tags($state['answer_text']) : null)
                            ->reorderableWithButtons(),
                    ]),

                // ── Live Preview ──────────────────────────────────────────
                Section::make(__('Question Preview'))
                    ->description(__('Live view as participants see during the exam. Updated automatically as you fill in the form above.'))
                    ->icon('heroicon-o-eye')
                    ->collapsible()
                    ->schema([
                        View::make('filament.components.question-preview'),
                    ]),
            ]);
    }
}
