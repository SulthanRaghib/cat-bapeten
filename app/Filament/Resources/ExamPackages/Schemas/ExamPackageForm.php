<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ExamPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
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
                                Select::make('exam_type_id')
                                    ->label('Tipe Ujian')
                                    ->relationship('examType', 'name')
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->native(false),

                                TextInput::make('passing_grade')
                                    ->label('Nilai Ambang Batas (Passing Grade)')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Contoh: Jika 100 soal x 1 poin = 100 Max. Passing grade bisa 70.'),
                            ]),

                        TextInput::make('duration_minutes')
                            ->label('Durasi Pengerjaan')
                            ->suffix('Menit')
                            ->numeric()
                            ->required(),

                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('start_time')
                                    ->label('Waktu Mulai')
                                    ->seconds(false)
                                    ->helperText('Kapan ujian ini mulai bisa diakses.')
                                    ->columnSpan(1),

                                DateTimePicker::make('end_time')
                                    ->label('Waktu Selesai')
                                    ->seconds(false)
                                    ->helperText('Batas akhir akses ujian.')
                                    ->after('start_time')
                                    ->columnSpan(1),
                            ]),

                        Select::make('is_active')
                            ->label('Status Ketersediaan')
                            ->options([
                                1 => 'Aktif — Peserta dapat mengakses ujian ini',
                                0 => 'Nonaktif — Paket ditutup untuk peserta',
                            ])
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->dehydrateStateUsing(fn($state) => (bool) $state)
                            ->helperText('Tentukan apakah paket ini terlihat dan dapat diakses oleh peserta.'),
                    ]),

                // ── Konfigurasi Wawancara (hanya untuk Teknis / correct_wrong) ─────
                Section::make('Konfigurasi Wawancara (Teknis)')
                    ->description('Aktifkan jika ujian ini memerlukan tahap wawancara setelah CBT selesai.')
                    ->icon('heroicon-o-microphone')
                    ->collapsible()
                    ->visible(
                        fn(Get $get): bool =>
                        \App\Models\ExamType::find($get('exam_type_id'))?->evaluation_method === 'correct_wrong'
                    )
                    ->schema([
                        Toggle::make('technical_scoring_config.has_interview')
                            ->label('Gunakan Tahap Wawancara?')
                            ->helperText('Jika aktif, status sesi peserta akan menjadi "Menunggu Wawancara" setelah CBT selesai, dan nilai akhir dihitung dari kombinasi CBT + Wawancara.')
                            ->live()
                            ->default(false),

                        Grid::make(2)
                            ->visible(fn(Get $get): bool => (bool) $get('technical_scoring_config.has_interview'))
                            ->schema([
                                TextInput::make('technical_scoring_config.cbt_weight')
                                    ->label('Bobot CBT (%)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(99)
                                    ->required(fn(Get $get): bool => (bool) $get('technical_scoring_config.has_interview'))
                                    ->helperText('Persentase kontribusi nilai CBT ke nilai akhir.')
                                    ->rules([
                                        fn(Get $get): \Closure => static function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                            $cbt       = (float) ($value ?? 0);
                                            $interview = (float) ($get('technical_scoring_config.interview_weight') ?? 0);
                                            if ($cbt + $interview !== 100.0) {
                                                $fail("Bobot CBT + Bobot Wawancara harus berjumlah tepat 100%. Saat ini: {$cbt} + {$interview} = " . ($cbt + $interview) . '%.');
                                            }
                                        },
                                    ]),

                                TextInput::make('technical_scoring_config.interview_weight')
                                    ->label('Bobot Wawancara (%)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(99)
                                    ->required(fn(Get $get): bool => (bool) $get('technical_scoring_config.has_interview'))
                                    ->helperText('Persentase kontribusi nilai wawancara ke nilai akhir.')
                                    ->rules([
                                        fn(Get $get): \Closure => static function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                            $interview = (float) ($value ?? 0);
                                            $cbt       = (float) ($get('technical_scoring_config.cbt_weight') ?? 0);
                                            if ($cbt + $interview !== 100.0) {
                                                $fail("Bobot CBT + Bobot Wawancara harus berjumlah tepat 100%. Saat ini: {$cbt} + {$interview} = " . ($cbt + $interview) . '%.');
                                            }
                                        },
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
