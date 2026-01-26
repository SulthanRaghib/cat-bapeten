<?php

namespace App\Filament\Resources\ExamPackages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Ujian')
                    ->description('Atur informasi dasar mengenai paket ujian ini.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Paket Ujian')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('Contoh: Ujian Kompetensi Teknis Batch 1'),

                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Tipe Ujian')
                                    ->options([
                                        'technical' => 'Teknis (Benar/Salah)',
                                        'structural' => 'Struktural (Bobot Nilai)',
                                    ])
                                    ->required()
                                    ->native(false),

                                TextInput::make('passing_grade')
                                    ->label('Nilai Ambang Batas (Passing Grade)')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Contoh: Jika 100 soal x 5 poin = 500 Max. Passing grade bisa 300.'),
                            ]),

                        TextInput::make('duration_minutes')
                            ->label('Durasi Pengerjaan')
                            ->suffix('Menit')
                            ->numeric()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Buka Pendaftaran')
                            ->helperText('Jika dimatikan, peserta tidak bisa mengakses ujian ini.')
                            ->default(true),
                    ]),
            ]);
    }
}
