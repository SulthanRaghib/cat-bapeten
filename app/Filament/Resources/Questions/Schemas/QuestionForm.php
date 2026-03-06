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
                Section::make('Detail Pertanyaan')
                    ->description('Lengkapi informasi soal berikut. Pilih tipe, unit, dan sub unit terlebih dahulu sebelum mengisi teks soal dan pilihan jawaban.')
                    ->icon('heroicon-o-document-text')
                    ->columns(12)
                    ->schema([
                        Select::make('exam_type_id')
                            ->label('Tipe Soal')
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
                            ->label('Unit (Materi/Bab)')
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
                                    ->label('Nama Unit Baru')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data, Get $get): int {
                                $examTypeId = $get('exam_type_id');

                                if (! $examTypeId) {
                                    throw new \RuntimeException('Pilih Tipe Soal terlebih dahulu sebelum membuat Unit baru.');
                                }

                                return QuestionUnit::create([
                                    'exam_type_id' => $examTypeId,
                                    'name'         => $data['name'],
                                    'is_active'    => true,
                                ])->getKey();
                            })
                            ->createOptionAction(function ($action) {
                                return $action
                                    ->label('Tambah Unit Baru')
                                    ->modalHeading('Buat Unit Baru')
                                    ->modalSubmitActionLabel('Simpan Unit')
                                    ->modalWidth('md')
                                    ->icon('heroicon-o-plus')
                                    ->color('primary');
                            })
                            ->editOptionForm([
                                TextInput::make('name')
                                    ->label('Nama Unit')
                                    ->required()
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->hidden()
                                    ->default(true),
                            ])
                            ->editOptionAction(function ($action) {
                                return $action
                                    ->label('Edit Unit')
                                    ->modalHeading('Ubah Data Unit')
                                    ->modalSubmitActionLabel('Update')
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
                                    return 'Pilih Tipe Soal terlebih dahulu';
                                }
                                return 'Tidak ada unit aktif untuk tipe soal ini. Silakan buat unit terlebih dahulu.';
                            }),

                        Select::make('question_sub_unit_id')
                            ->label('Sub Unit (Sub-Bab)')
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
                            ->helperText(fn(Get $get): ?string => ! filled($get('question_unit_id')) ? 'Pilih Unit terlebih dahulu' : null)
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nama Sub Unit Baru')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionAction(function ($action) {
                                return $action
                                    ->label('Tambah Sub Unit Baru')
                                    ->modalHeading('Buat Sub Unit Baru')
                                    ->modalSubmitActionLabel('Simpan Sub Unit')
                                    ->modalWidth('md')
                                    ->icon('heroicon-o-plus')
                                    ->color('primary');
                            })
                            ->createOptionUsing(function (array $data, Get $get): int {
                                $questionUnitId = $get('question_unit_id');

                                if (! $questionUnitId) {
                                    throw new \RuntimeException('Pilih Unit terlebih dahulu sebelum membuat Sub Unit baru.');
                                }

                                return QuestionSubUnit::create([
                                    'question_unit_id' => $questionUnitId,
                                    'name'             => $data['name'],
                                ])->getKey();
                            })
                            ->editOptionForm([
                                TextInput::make('name')
                                    ->label('Nama Sub Unit')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->editOptionAction(function ($action) {
                                return $action
                                    ->label('Edit Sub Unit')
                                    ->modalHeading('Ubah Data Sub Unit')
                                    ->modalSubmitActionLabel('Update')
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
                                    return 'Pilih Unit terlebih dahulu';
                                }
                                return 'Tidak ada sub unit untuk unit ini. Silakan buat sub unit terlebih dahulu.';
                            }),

                        // ── Conditional: Technical difficulty category ──────────

                        Select::make('category')
                            ->label('Tingkat Kesulitan')
                            ->options([
                                'easy'   => 'Mudah',
                                'medium' => 'Sedang',
                                'hard'   => 'Sulit',
                            ])
                            ->visible(fn(Get $get) => self::getEvaluationMethod($get) === 'correct_wrong')
                            ->required(fn(Get $get) => self::getEvaluationMethod($get) === 'correct_wrong')
                            ->columnSpan(4)
                            ->native(false),

                    ]),

                Section::make('Isi Soal & Pembahasan')
                    ->schema([
                        View::make('filament.components.math-helper'),

                        RichEditor::make('question_text')
                            ->label('Pertanyaan')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('question-images')
                            ->columnSpanFull(),

                        View::make('filament.components.image-insert-widget'),

                        RichEditor::make('explanation')
                            ->label('Pembahasan Jawaban')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('question-images')
                            ->columnSpanFull()
                            ->hidden(),
                    ]),

                Section::make('Jawaban')
                    ->description('Tambahkan pilihan jawaban. Untuk soal Teknis, tandai satu jawaban sebagai kunci. Untuk Mansoskul, isi bobot poin tiap opsi.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('options')
                            ->schema([
                                RichEditor::make('answer_text')
                                    ->label('Teks Jawaban')
                                    ->required()
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('question-images')
                                    ->columnSpanFull(),

                                View::make('filament.components.image-insert-widget'),

                                // correct_wrong: Correct/Incorrect toggle
                                Toggle::make('is_correct')
                                    ->label('Kunci Jawaban (Benar = 1 Poin)')
                                    ->default(false)
                                    ->visible(fn(Get $get) => self::getEvaluationMethod($get, '../../') === 'correct_wrong')
                                    ->reactive(),

                                // weighted: Explicit Score input
                                TextInput::make('score')
                                    ->label('Bobot Nilai')
                                    ->numeric()
                                    ->visible(fn(Get $get) => self::getEvaluationMethod($get, '../../') === 'weighted')
                                    ->required(fn(Get $get) => self::getEvaluationMethod($get, '../../') === 'weighted'),

                                Hidden::make('is_active')
                                    ->default(true),
                            ])
                            ->columns(1)
                            ->defaultItems(4)
                            // ->grid(2)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => strip_tags($state['answer_text'] ?? null))
                            ->reorderableWithButtons(),
                    ]),

                // ── Live Preview ──────────────────────────────────────────
                Section::make('Pratinjau Soal')
                    ->description('Tampilan langsung seperti yang dilihat peserta saat ujian. Diperbarui otomatis setiap kali Anda mengisi form di atas.')
                    ->icon('heroicon-o-eye')
                    ->collapsible()
                    ->schema([
                        View::make('filament.components.question-preview'),
                    ]),
            ]);
    }
}
