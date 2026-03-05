<?php

declare(strict_types=1);

namespace App\Filament\Resources\SelectionStageTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SelectionStageTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Tahap Seleksi')
                ->description('Data ini akan muncul sebagai pilihan saat menyusun konfigurasi paket ujian Teknis.')
                ->icon('heroicon-o-queue-list')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Tahap')
                        ->placeholder('Contoh: Wawancara, FGD, Presentasi')
                        ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true),

                    TextInput::make('description')
                        ->label('Keterangan')
                        ->placeholder('Deskripsi singkat mengenai tahap ini')
                        ->maxLength(255),

                    TextInput::make('icon')
                        ->label('Icon (Heroicon)')
                        ->placeholder('Contoh: heroicon-o-microphone')
                        ->helperText('Nama heroicon yang digunakan pada UI. Kosongkan jika tidak perlu.')
                        ->maxLength(100),

                    TextInput::make('sort_order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Angka lebih kecil tampil lebih dulu di dropdown.'),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->helperText('Jenis tahap yang tidak aktif tidak akan muncul di dropdown pilihan paket ujian.')
                        ->default(true),
                ]),
        ]);
    }
}
