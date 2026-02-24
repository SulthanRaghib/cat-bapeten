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
                Section::make('Informasi Tipe Ujian')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Tipe')
                            ->placeholder('cth: Teknis, Mansoskul')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Kode')
                            ->placeholder('cth: TEK, MAN')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Kode unik untuk identifikasi tipe ujian.'),

                        Select::make('evaluation_method')
                            ->label('Metode Evaluasi')
                            ->options([
                                'correct_wrong' => 'Benar / Salah (Pilihan tunggal dengan 1 jawaban benar)',
                                'weighted' => 'Berbobot (Setiap opsi memiliki skor/bobot)',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('Menentukan bagaimana jawaban akan dinilai.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Tipe ujian yang tidak aktif tidak akan muncul di pilihan.'),
                    ])
                    ->columns(2),
            ]);
    }
}
