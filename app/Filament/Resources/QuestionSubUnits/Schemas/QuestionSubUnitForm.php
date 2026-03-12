<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionSubUnits\Schemas;

use App\Models\QuestionUnit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionSubUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Question Sub Unit Information'))
                    ->description(__('Complete the question sub unit data. Sub units are a more detailed classification within a question unit.'))
                    ->icon('heroicon-o-folder-open')
                    ->schema([
                        Select::make('question_unit_id')
                            ->label(__('Question Unit'))
                            ->validationAttribute('Unit Soal')
                            ->options(fn() => QuestionUnit::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn($unit) => [
                                    $unit->id => "{$unit->name} ({$unit->examType->name})",
                                ])
                                ->toArray())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText(__('This sub unit will be bound to the selected unit.')),

                        TextInput::make('name')
                            ->label(__('Sub Unit Name'))
                            ->validationAttribute('Nama Sub Unit')
                            ->placeholder(__('e.g. Dosimetry, Transport Safety'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
