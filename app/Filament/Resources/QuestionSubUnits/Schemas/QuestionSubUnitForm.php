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
                Section::make('Informasi Sub Unit Soal')
                    ->description('Lengkapi data sub unit soal berikut. Sub unit merupakan klasifikasi lebih rinci di dalam sebuah unit soal.')
                    ->icon('heroicon-o-folder-open')
                    ->schema([
                        Select::make('question_unit_id')
                            ->label('Unit Soal')
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
                            ->helperText('Sub unit ini akan terikat pada unit yang dipilih.'),

                        TextInput::make('name')
                            ->label('Nama Sub Unit')
                            ->validationAttribute('Nama Sub Unit')
                            ->placeholder('cth: Dosimetri, Keselamatan Transportasi')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
