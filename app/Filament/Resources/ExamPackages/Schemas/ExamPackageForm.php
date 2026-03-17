<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\Schemas;

use App\Forms\Components\CustomDateTimePicker;
use App\Models\SelectionStageType;
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
                Section::make(__('Exam Details'))
                    ->description(__('Set up the basic information for this exam package.'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Exam Package Title'))
                            ->validationAttribute('Judul Paket Ujian')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder(__('e.g. Technical Competency Exam Batch 1')),

                        Grid::make(2)
                            ->schema([
                                Select::make('exam_type_id')
                                    ->label(__('Exam Type'))
                                    ->validationAttribute('Tipe Ujian')
                                    ->relationship('examType', 'name')
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->native(false),

                                TextInput::make('passing_grade')
                                    ->label(__('Passing Grade'))
                                    ->validationAttribute('Nilai Ambang Batas')
                                    ->numeric()
                                    ->required()
                                    ->helperText(__('e.g. If 100 questions x 1 point = 100 Max. Passing grade could be 70.')),
                            ]),

                        TextInput::make('duration_minutes')
                            ->label(__('Duration'))
                            ->validationAttribute('Durasi Pengerjaan')
                            ->suffix(__('Minutes'))
                            ->numeric()
                            ->required(),

                        Grid::make(2)
                            ->schema([
                                CustomDateTimePicker::make('start_time')
                                    ->label(__('Start Time'))
                                    ->validationAttribute('Waktu Mulai')
                                    ->required()
                                    ->helperText(__('When this exam becomes accessible.'))
                                    ->columnSpan(1),

                                CustomDateTimePicker::make('end_time')
                                    ->label(__('End Time'))
                                    ->validationAttribute('Waktu Selesai')
                                    ->required()
                                    ->helperText(__('Deadline for exam access.'))
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                            $startTime = $get('start_time');
                                            if (! $startTime || ! $value) {
                                                return;
                                            }
                                            if (strtotime($value) <= strtotime($startTime)) {
                                                $fail(__('End Time must be after Start Time.'));
                                            }
                                        },
                                    ])
                                    ->columnSpan(1),
                            ]),

                        Select::make('is_active')
                            ->label(__('Availability Status'))
                            ->validationAttribute('Status Ketersediaan')
                            ->options([
                                1 => __('Active — Participants can access this exam'),
                                0 => __('Inactive — Package is closed for participants'),
                            ])
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->dehydrateStateUsing(fn($state) => (bool) $state)
                            ->helperText(__('Determine whether this package is visible and accessible by participants.')),
                    ]),

                // ── Konfigurasi Seleksi Lanjutan (hanya untuk Teknis / correct_wrong) ─────
                Section::make(__('Advanced Selection Configuration (Technical)'))
                    ->description(__('Enable if this exam requires a selection stage after CBT is complete — such as Interview, Presentation, FGD, etc.'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->visible(
                        fn(Get $get): bool =>
                        \App\Models\ExamType::find($get('exam_type_id'))?->evaluation_method === 'correct_wrong'
                    )
                    ->schema([
                        Toggle::make('technical_scoring_config.has_stages')
                            ->label(__('Use Advanced Selection Stages?'))
                            ->helperText(__('If enabled, participants will have status "Awaiting Selection" after CBT and admin needs to enter scores for each selection stage before the final score is set.'))
                            ->live()
                            ->default(false),

                        // ── Seluruh konten ini hanya tampil jika has_stages = true ──
                        Section::make()
                            ->visible(fn(Get $get): bool => (bool) $get('technical_scoring_config.has_stages'))
                            ->extraAttributes(['class' => 'bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl'])
                            ->schema([

                                // ── Bobot CBT ──
                                TextInput::make('technical_scoring_config.cbt_weight')
                                    ->label(__('CBT Weight (%)'))
                                    ->validationAttribute('Bobot CBT')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(99)
                                    ->default(100)
                                    ->suffix('%')
                                    ->live(debounce: 500)
                                    ->required(fn(Get $get): bool => (bool) $get('technical_scoring_config.has_stages'))
                                    ->helperText(__('Percentage weight of CBT score toward the final score.'))
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
                                    ->label(__('Selection Stages'))
                                    ->helperText(__('Add each selection stage along with its weight. Total weight of CBT + all stages must equal exactly 100%.'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('label')
                                                    ->label(__('Stage Name'))
                                                    ->validationAttribute('Nama Tahap Seleksi')
                                                    ->options(fn(): array => SelectionStageType::active()->pluck('name', 'name')->toArray())
                                                    ->searchable()
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label(__('New Stage Name'))
                                                            ->required()
                                                            ->maxLength(100),
                                                        TextInput::make('description')
                                                            ->label(__('Description'))
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
                                                    ->placeholder(__('Select or add stage name...')),

                                                TextInput::make('weight')
                                                    ->label(__('Weight (%)'))
                                                    ->validationAttribute('Bobot Tahap Seleksi')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->maxValue(99)
                                                    ->suffix('%')
                                                    ->required()
                                                    ->live(debounce: 500),
                                            ]),
                                    ])
                                    ->addActionLabel(__('+ Add Selection Stage'))
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->itemLabel(
                                        fn(array $state): string => ($state['label'] ?? __('New Stage'))
                                            . ' — ' . __('Weight') . ': ' . ($state['weight'] ?? 0) . '%'
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
