<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\Schemas;

use App\Models\ExamType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Question Unit Information'))
                    ->description(__('Complete the question unit data. Units group questions by competency or subject area.'))
                    ->icon('heroicon-o-folder')
                    ->schema([
                        Select::make('exam_type_id')
                            ->label(__('Exam Type'))
                            ->validationAttribute('Tipe Ujian')
                            ->options(fn() => ExamType::query()
                                ->where('is_active', true)
                                ->pluck('name', 'id')
                                ->toArray())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText(__('This unit will be bound to the selected exam type.')),

                        TextInput::make('name')
                            ->label(__('Unit Name'))
                            ->validationAttribute('Nama Unit')
                            ->placeholder(__('e.g. Radiation Protection, Nuclear Safety'))
                            ->required()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->helperText(__('Inactive units will not appear in question selections.')),

                    ])
                    ->columns(2),
            ]);
    }
}
