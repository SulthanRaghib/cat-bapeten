<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\Schemas;

use App\Models\SelectionStageType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
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
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d F Y H:i')
                                    ->seconds(false)
                                    ->timezone('Asia/Jakarta')
                                    ->helperText('Kapan ujian ini mulai bisa diakses.')
                                    ->columnSpan(1),

                                DateTimePicker::make('end_time')
                                    ->label('Waktu Selesai')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d F Y H:i')
                                    ->seconds(false)
                                    ->timezone('Asia/Jakarta')
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

                // ── Konfigurasi Seleksi Lanjutan (hanya untuk Teknis / correct_wrong) ─────
                Section::make('Konfigurasi Seleksi Lanjutan (Teknis)')
                    ->description('Aktifkan jika ujian ini memerlukan tahap seleksi setelah CBT selesai — seperti Wawancara, Presentasi, FGD, dan lain-lain.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->visible(
                        fn(Get $get): bool =>
                        \App\Models\ExamType::find($get('exam_type_id'))?->evaluation_method === 'correct_wrong'
                    )
                    ->schema([
                        Toggle::make('technical_scoring_config.has_stages')
                            ->label('Gunakan Tahap Seleksi Lanjutan?')
                            ->helperText('Jika aktif, peserta akan berstatus "Menunggu Seleksi" setelah CBT dan admin perlu menginput nilai setiap tahap seleksi sebelum nilai akhir ditetapkan.')
                            ->live()
                            ->default(false),

                        // ── Seluruh konten ini hanya tampil jika has_stages = true ──
                        Section::make()
                            ->visible(fn(Get $get): bool => (bool) $get('technical_scoring_config.has_stages'))
                            ->extraAttributes(['class' => 'bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl'])
                            ->schema([

                                // ── Bobot CBT ──
                                TextInput::make('technical_scoring_config.cbt_weight')
                                    ->label('Bobot CBT (%)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(99)
                                    ->default(100)
                                    ->suffix('%')
                                    ->live(debounce: 500)
                                    ->required(fn(Get $get): bool => (bool) $get('technical_scoring_config.has_stages'))
                                    ->helperText('Persentase bobot nilai CBT terhadap nilai akhir.')
                                    ->rules([
                                        fn(Get $get): \Closure => static function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                            if (! (bool) $get('technical_scoring_config.has_stages')) return;

                                            $cbtWeight   = (float) ($value ?? 0);
                                            $stages      = (array) ($get('technical_scoring_config.stages') ?? []);
                                            $stagesTotal = array_sum(array_column($stages, 'weight'));
                                            $total       = $cbtWeight + $stagesTotal;

                                            if (abs($total - 100) > 0.001) {
                                                $fail("Total bobot harus 100%. CBT ({$cbtWeight}%) + Tahap Seleksi ({$stagesTotal}%) = {$total}%.");
                                            }
                                        },
                                    ]),

                                // ── Repeater Tahap Seleksi ──
                                Repeater::make('technical_scoring_config.stages')
                                    ->label('Tahap Seleksi')
                                    ->helperText('Tambahkan setiap tahap seleksi beserta bobot nilainya. Total bobot CBT + semua tahap harus tepat 100%.')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('label')
                                                    ->label('Nama Tahap')
                                                    ->options(fn(): array => SelectionStageType::active()->pluck('name', 'name')->toArray())
                                                    ->searchable()
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label('Nama Tahap Baru')
                                                            ->required()
                                                            ->maxLength(100),
                                                        TextInput::make('description')
                                                            ->label('Keterangan')
                                                            ->maxLength(255),
                                                    ])
                                                    ->createOptionUsing(function (array $data): string {
                                                        $type = SelectionStageType::create([
                                                            'name'        => $data['name'],
                                                            'description' => $data['description'] ?? null,
                                                            'is_active'   => true,
                                                            'sort_order'  => (SelectionStageType::max('sort_order') ?? 0) + 1,
                                                        ]);
                                                        return $type->name;
                                                    })
                                                    ->getOptionLabelUsing(fn($value): string => $value)
                                                    ->required()
                                                    ->native(false)
                                                    ->placeholder('Pilih atau tambah nama tahap...'),

                                                TextInput::make('weight')
                                                    ->label('Bobot (%)')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->maxValue(99)
                                                    ->suffix('%')
                                                    ->required()
                                                    ->live(debounce: 500),
                                            ]),
                                    ])
                                    ->addActionLabel('+ Tambah Tahap Seleksi')
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->itemLabel(
                                        fn(array $state): string => ($state['label'] ?? 'Tahap Baru')
                                            . ' — Bobot: ' . ($state['weight'] ?? 0) . '%'
                                    )
                                    ->minItems(1)
                                    ->required(fn(Get $get): bool => (bool) $get('../../technical_scoring_config.has_stages'))
                                    ->live()
                                    ->afterStateUpdated(function () {}) // trigger live re-render for hint
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->hint(function (Get $get): string {
                                        $cbtWeight   = (float) ($get('technical_scoring_config.cbt_weight') ?? 0);
                                        $stages      = (array) ($get('technical_scoring_config.stages') ?? []);
                                        $stagesTotal = array_sum(array_column($stages, 'weight'));
                                        $total       = $cbtWeight + $stagesTotal;
                                        $remaining   = 100 - $total;

                                        // Build per-stage breakdown
                                        $parts = ["CBT: {$cbtWeight}%"];
                                        foreach ($stages as $stage) {
                                            $lbl = $stage['label'] ?? 'Tahap';
                                            $w   = $stage['weight'] ?? 0;
                                            $parts[] = "{$lbl}: {$w}%";
                                        }
                                        $breakdown = implode(' + ', $parts) . " = {$total}%";

                                        if (abs($remaining) < 0.001) {
                                            return "✅ {$breakdown} — Total sudah 100%";
                                        }
                                        if ($remaining > 0) {
                                            return "⚠️ {$breakdown} — Sisa bobot: {$remaining}%";
                                        }
                                        return "❌ {$breakdown} — Melebihi 100% sebesar " . abs($remaining) . '% — harap kurangi.';
                                    })
                                    ->hintColor(function (Get $get): string {
                                        $cbtWeight   = (float) ($get('technical_scoring_config.cbt_weight') ?? 0);
                                        $stages      = (array) ($get('technical_scoring_config.stages') ?? []);
                                        $stagesTotal = array_sum(array_column($stages, 'weight'));
                                        $total       = $cbtWeight + $stagesTotal;
                                        $remaining   = 100 - $total;
                                        if (abs($remaining) < 0.001) return 'success';
                                        if ($remaining > 0) return 'warning';
                                        return 'danger';
                                    }),

                            ]),
                    ]),
            ]);
    }
}
