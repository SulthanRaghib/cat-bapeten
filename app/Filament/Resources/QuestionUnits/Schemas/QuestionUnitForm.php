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
                Section::make('Informasi Unit Soal')
                    ->description('Lengkapi data unit soal berikut. Unit mengelompokkan soal-soal berdasarkan kompetensi atau bidang materi.')
                    ->icon('heroicon-o-folder')
                    ->schema([
                        Select::make('exam_type_id')
                            ->label('Tipe Ujian')
                            ->validationAttribute('Tipe Ujian')
                            ->options(fn() => ExamType::query()
                                ->where('is_active', true)
                                ->pluck('name', 'id')
                                ->toArray())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Unit ini akan terikat pada tipe ujian yang dipilih.'),

                        TextInput::make('name')
                            ->label('Nama Unit')
                            ->validationAttribute('Nama Unit')
                            ->placeholder('cth: Proteksi Radiasi, Keselamatan Nuklir')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Unit yang tidak aktif tidak akan muncul di pilihan soal.'),
                    ])
                    ->columns(2),
            ]);
    }
}
