<?php

namespace App\Filament\Resources\Questions\Schemas;

use App\Models\ExamType;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class QuestionForm
{
    /**
     * Resolve evaluation method for the currently selected exam type.
     */
    private static function getEvaluationMethod(Get $get, string $prefix = ''): ?string
    {
        $examTypeId = $get($prefix . 'exam_type_id');

        return $examTypeId
            ? ExamType::find($examTypeId)?->evaluation_method
            : null;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Detail Pertanyaan')
                    ->columns(12)
                    ->schema([
                        Select::make('exam_type_id')
                            ->label('Tipe Soal')
                            ->relationship('examType', 'name')
                            ->preload()
                            ->required()
                            ->columnSpan(4)
                            ->live()
                            ->native(false),

                        // --- Conditional Fields for 'correct_wrong' evaluation ---
                        Section::make('Data Teknis')
                            ->schema([
                                TextInput::make('unit')
                                    ->label('Unit (Materi/Bab)')
                                    ->placeholder('Misal: Proteksi Radiasi')
                                    ->columnSpan(4),
                                TextInput::make('sub_unit')
                                    ->label('Sub Unit (Sub-Bab)')
                                    ->placeholder('Misal: Efek Biologis')
                                    ->columnSpan(4),
                                Select::make('category')
                                    ->label('Kategori')
                                    ->options([
                                        'easy' => 'Mudah',
                                        'medium' => 'Sedang',
                                        'hard' => 'Sulit',
                                    ])
                                    ->required()
                                    ->columnSpan(4),
                            ])
                            ->visible(fn(Get $get) => self::getEvaluationMethod($get) === 'correct_wrong')
                            ->columns(12)
                            ->columnSpan(12),

                        // --- Conditional Fields for 'weighted' evaluation ---
                        Section::make('Data Mansoskul')
                            ->schema([
                                TextInput::make('competence_area')
                                    ->label('Bidang Kompetensi')
                                    ->placeholder('Misal: Manajerial')
                                    ->columnSpan(6),
                                TextInput::make('competence_sub_area')
                                    ->label('Sub Bidang Kompetensi')
                                    ->placeholder('Misal: Integritas')
                                    ->columnSpan(6),
                            ])
                            ->visible(fn(Get $get) => self::getEvaluationMethod($get) === 'weighted')
                            ->columns(12)
                            ->columnSpan(12),
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
                                    ->label('Kunci Jawaban (Benar = 5 Poin)')
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
                            ->grid(2)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => strip_tags($state['answer_text'] ?? null))
                            ->reorderableWithButtons(),
                    ]),
            ]);
    }
}
