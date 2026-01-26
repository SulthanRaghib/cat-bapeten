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
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Paket Ujian')
                            ->required()
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label('Tipe Paket Ujian')
                            ->options([
                                'technical' => 'Technical',
                                'structural' => 'Structural',
                            ])
                            ->required()
                            ->helperText('Pilih tipe paket ujian sesuai dengan kebutuhan.'),

                        TextInput::make('passing_grade')
                            ->label('Nilai Kelulusan')
                            ->numeric()
                            ->helperText('Masukkan nilai minimal untuk lulus ujian.')
                            ->required(),

                        TextInput::make('duration_minutes')
                            ->label('Durasi')
                            ->suffix('Menit')
                            ->numeric()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
