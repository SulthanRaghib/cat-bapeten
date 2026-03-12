<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamTypes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExamTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Exam Type Information'))
                    ->description(__('Complete the exam type configuration. The exam type determines the grading method and type of questions used.'))
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Type Name'))
                            ->validationAttribute('Nama Tipe')
                            ->placeholder(__('e.g. Technical, Mansoskul'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label(__('Code'))
                            ->validationAttribute('Kode Tipe Ujian')
                            ->placeholder(__('e.g. TEK, MAN'))
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Unique code for exam type identification.')),

                        Select::make('evaluation_method')
                            ->label(__('Evaluation Method'))
                            ->validationAttribute('Metode Evaluasi')
                            ->options([
                                'correct_wrong' => __('Correct / Incorrect (Single choice with 1 correct answer)'),
                                'weighted' => __('Weighted (Each option has a score/weight)'),
                            ])
                            ->required()
                            ->native(false)
                            ->helperText(__('Determines how answers will be scored.')),

                        Toggle::make('is_active')
                            ->hidden(true)
                            ->label(__('Active'))
                            ->default(true)
                            ->helperText(__('Inactive exam types will not appear in the selection.')),
                    ])
                    ->columns(2),
            ]);
    }
}
