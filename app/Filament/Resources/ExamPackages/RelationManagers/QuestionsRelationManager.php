<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Models\Question;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Schema $form): Schema
    {
        return $form
            ->columns(1)
            ->schema([
                Section::make('Detail Soal')
                    ->description('Kelola isi pertanyaan serta opsi jawaban secara fleksibel.')
                    ->columns(12)
                    ->schema([
                        Placeholder::make('editor_guide')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 16px; border-radius: 12px; border: 1px solid #f59e0b;">
                                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                                        <div style="background: #f59e0b; color: white; padding: 8px; border-radius: 8px; flex-shrink: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div style="flex: 1;">
                                            <h4 style="margin: 0 0 8px 0; font-weight: 700; color: #92400e; font-size: 14px;">📝 Panduan Editor Soal</h4>
                                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; font-size: 12px; color: #78350f;">
                                                <div>📷 <strong>Gambar:</strong> Klik 📎 → pilih file</div>
                                                <div>🔗 <strong>Link:</strong> Klik icon link</div>
                                                <div>📋 <strong>List:</strong> Bullet atau Numbered</div>
                                                <div>🎨 <strong>Format:</strong> Bold, Italic, dll</div>
                                            </div>
                                            <div style="margin-top: 12px; padding: 12px; background: white; border-radius: 8px; border: 1px dashed #d97706;">
                                                <p style="margin: 0 0 8px 0; font-weight: 600; color: #92400e; font-size: 12px;">📐 Rumus Matematika - Klik untuk Salin:</p>
                                                <div style="display: flex; flex-wrap: wrap; gap: 8px;" id="latex-chips-container">
                                                    <button type="button" class="latex-chip-btn" data-formula="\\( \\frac{a}{b} \\)">
                                                        <span class="chip-preview">ᵃ⁄ᵦ</span>
                                                        <span class="chip-label">Pecahan</span>
                                                    </button>
                                                    <button type="button" class="latex-chip-btn" data-formula="\\( x^{2} \\)">
                                                        <span class="chip-preview">x²</span>
                                                        <span class="chip-label">Pangkat</span>
                                                    </button>
                                                    <button type="button" class="latex-chip-btn" data-formula="\\( \\sqrt{x} \\)">
                                                        <span class="chip-preview">√x</span>
                                                        <span class="chip-label">Akar</span>
                                                    </button>
                                                    <button type="button" class="latex-chip-btn" data-formula="\\( \\sum_{i=1}^{n} x_i \\)">
                                                        <span class="chip-preview">Σ</span>
                                                        <span class="chip-label">Sigma</span>
                                                    </button>
                                                    <button type="button" class="latex-chip-btn" data-formula="\\( \\int_{a}^{b} f(x) dx \\)">
                                                        <span class="chip-preview">∫</span>
                                                        <span class="chip-label">Integral</span>
                                                    </button>
                                                    <button type="button" class="latex-chip-btn" data-formula="\\( \\pi \\)">
                                                        <span class="chip-preview">π</span>
                                                        <span class="chip-label">Pi</span>
                                                    </button>
                                                    <button type="button" class="latex-chip-btn" data-formula="\\( \\alpha \\)">
                                                        <span class="chip-preview">α</span>
                                                        <span class="chip-label">Alpha</span>
                                                    </button>
                                                    <button type="button" class="latex-chip-btn" data-formula="\\( \\lambda \\)">
                                                        <span class="chip-preview">λ</span>
                                                        <span class="chip-label">Lambda</span>
                                                    </button>
                                                </div>
                                                <p style="margin: 10px 0 0 0; font-size: 11px; color: #78350f; display: flex; align-items: center; gap: 4px;">
                                                    <span style="background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px;">TIP</span>
                                                    Klik chip → paste di editor. Gunakan tombol "Sisipkan Rumus" untuk rumus lengkap.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <style>
                                    .latex-chip-btn {
                                        display: inline-flex;
                                        flex-direction: column;
                                        align-items: center;
                                        padding: 8px 12px;
                                        background: linear-gradient(135deg, #fef3c7, #fde68a);
                                        border: 2px solid #fcd34d;
                                        border-radius: 10px;
                                        cursor: pointer;
                                        transition: all 0.2s ease;
                                        position: relative;
                                        min-width: 60px;
                                        font-family: inherit;
                                    }
                                    .latex-chip-btn:hover {
                                        background: linear-gradient(135deg, #fde68a, #fbbf24);
                                        border-color: #f59e0b;
                                        transform: translateY(-2px);
                                        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
                                    }
                                    .latex-chip-btn:active {
                                        transform: translateY(0);
                                    }
                                    .latex-chip-btn.copied {
                                        background: linear-gradient(135deg, #d1fae5, #a7f3d0) !important;
                                        border-color: #10b981 !important;
                                    }
                                    .latex-chip-btn .chip-preview {
                                        font-size: 20px;
                                        font-weight: 500;
                                        color: #92400e;
                                        line-height: 1;
                                    }
                                    .latex-chip-btn .chip-label {
                                        font-size: 9px;
                                        color: #b45309;
                                        margin-top: 4px;
                                        font-weight: 600;
                                        text-transform: uppercase;
                                        letter-spacing: 0.5px;
                                    }
                                </style>
                            '))
                            ->columnSpanFull(),

                        Placeholder::make('math_helper')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div x-data="mathFormulaHelper()" style="margin-bottom: 8px;">
                                    <button type="button" @click="openPicker()" class="math-formula-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.871 4A17.926 17.926 0 003 12c0 2.874.673 5.59 1.871 8m14.13 0a17.926 17.926 0 001.87-8c0-2.874-.673-5.59-1.87-8M9 9h1.246a1 1 0 01.961.725l1.586 5.55a1 1 0 00.961.725H15m-6 4h6" />
                                        </svg>
                                        🔢 Sisipkan Rumus Matematika
                                    </button>

                                    <template x-if="showPicker">
                                        <div class="math-picker-modal" @click.self="closePicker()">
                                            <div class="math-picker-content">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                                    <h3 style="margin: 0; font-size: 18px; font-weight: 700;">📐 Pilih Template Rumus</h3>
                                                    <button type="button" @click="closePicker()" style="background: #f3f4f6; border: none; padding: 8px; border-radius: 8px; cursor: pointer;">✕</button>
                                                </div>

                                                <div style="margin-bottom: 16px;">
                                                    <label style="font-size: 12px; font-weight: 600; color: #6b7280;">Kategori:</label>
                                                    <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                                                        <template x-for="cat in categories" :key="cat.id">
                                                            <button type="button"
                                                                @click="activeCategory = cat.id"
                                                                :class="activeCategory === cat.id ? \'active\' : \'\'"
                                                                style="padding: 6px 12px; border-radius: 20px; font-size: 12px; cursor: pointer; border: 2px solid;"
                                                                :style="activeCategory === cat.id ? \'background: #f59e0b; color: white; border-color: #f59e0b;\' : \'background: white; border-color: #e5e7eb;\'"
                                                                x-text="cat.name">
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>

                                                <div class="math-picker-grid">
                                                    <template x-for="formula in filteredFormulas" :key="formula.latex">
                                                        <div class="math-picker-item" @click="insertFormula(formula.latex)">
                                                            <div class="formula" x-html="formula.preview"></div>
                                                            <div class="label" x-text="formula.name"></div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <div style="margin-top: 16px; padding: 12px; background: #f0fdf4; border-radius: 8px; border: 1px solid #86efac;">
                                                    <p style="margin: 0; font-size: 12px; color: #166534;">
                                                        <strong>💡 Tips:</strong> Klik rumus untuk menyalin ke clipboard. Lalu paste (Ctrl+V) di editor teks.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <script>
                                    function mathFormulaHelper() {
                                        return {
                                            showPicker: false,
                                            activeCategory: "basic",
                                            categories: [
                                                { id: "basic", name: "🔢 Dasar" },
                                                { id: "fraction", name: "➗ Pecahan" },
                                                { id: "power", name: "📈 Pangkat & Akar" },
                                                { id: "greek", name: "🇬🇷 Greek" },
                                                { id: "calculus", name: "∫ Kalkulus" },
                                                { id: "physics", name: "⚛️ Fisika" }
                                            ],
                                            formulas: [
                                                // Basic
                                                { category: "basic", name: "Plus Minus", latex: "\\\\( \\\\pm \\\\)", preview: "±" },
                                                { category: "basic", name: "Kali", latex: "\\\\( \\\\times \\\\)", preview: "×" },
                                                { category: "basic", name: "Bagi", latex: "\\\\( \\\\div \\\\)", preview: "÷" },
                                                { category: "basic", name: "Tidak sama", latex: "\\\\( \\\\neq \\\\)", preview: "≠" },
                                                { category: "basic", name: "Kurang dari sama", latex: "\\\\( \\\\leq \\\\)", preview: "≤" },
                                                { category: "basic", name: "Lebih dari sama", latex: "\\\\( \\\\geq \\\\)", preview: "≥" },
                                                { category: "basic", name: "Tak hingga", latex: "\\\\( \\\\infty \\\\)", preview: "∞" },
                                                { category: "basic", name: "Derajat", latex: "\\\\( ^{\\\\circ} \\\\)", preview: "°" },

                                                // Fractions
                                                { category: "fraction", name: "Pecahan a/b", latex: "\\\\( \\\\frac{a}{b} \\\\)", preview: "a/b" },
                                                { category: "fraction", name: "Pecahan 1/2", latex: "\\\\( \\\\frac{1}{2} \\\\)", preview: "½" },
                                                { category: "fraction", name: "Pecahan x/y", latex: "\\\\( \\\\frac{x}{y} \\\\)", preview: "x/y" },
                                                { category: "fraction", name: "Persentase", latex: "\\\\( \\\\frac{N_f}{N_i} \\\\times 100\\\\% \\\\)", preview: "(Nf/Ni)×100%" },

                                                // Powers & Roots
                                                { category: "power", name: "Kuadrat x²", latex: "\\\\( x^{2} \\\\)", preview: "x²" },
                                                { category: "power", name: "Pangkat n", latex: "\\\\( x^{n} \\\\)", preview: "xⁿ" },
                                                { category: "power", name: "Akar kuadrat", latex: "\\\\( \\\\sqrt{x} \\\\)", preview: "√x" },
                                                { category: "power", name: "Akar pangkat n", latex: "\\\\( \\\\sqrt[n]{x} \\\\)", preview: "ⁿ√x" },
                                                { category: "power", name: "Subscript", latex: "\\\\( x_{1} \\\\)", preview: "x₁" },
                                                { category: "power", name: "Eksponen e", latex: "\\\\( e^{x} \\\\)", preview: "eˣ" },
                                                { category: "power", name: "Logaritma", latex: "\\\\( \\\\log_{10} x \\\\)", preview: "log₁₀x" },
                                                { category: "power", name: "Natural log", latex: "\\\\( \\\\ln x \\\\)", preview: "ln x" },

                                                // Greek
                                                { category: "greek", name: "Alpha", latex: "\\\\( \\\\alpha \\\\)", preview: "α" },
                                                { category: "greek", name: "Beta", latex: "\\\\( \\\\beta \\\\)", preview: "β" },
                                                { category: "greek", name: "Gamma", latex: "\\\\( \\\\gamma \\\\)", preview: "γ" },
                                                { category: "greek", name: "Delta", latex: "\\\\( \\\\Delta \\\\)", preview: "Δ" },
                                                { category: "greek", name: "Theta", latex: "\\\\( \\\\theta \\\\)", preview: "θ" },
                                                { category: "greek", name: "Lambda", latex: "\\\\( \\\\lambda \\\\)", preview: "λ" },
                                                { category: "greek", name: "Mu", latex: "\\\\( \\\\mu \\\\)", preview: "μ" },
                                                { category: "greek", name: "Pi", latex: "\\\\( \\\\pi \\\\)", preview: "π" },
                                                { category: "greek", name: "Sigma", latex: "\\\\( \\\\sigma \\\\)", preview: "σ" },
                                                { category: "greek", name: "Omega", latex: "\\\\( \\\\omega \\\\)", preview: "ω" },

                                                // Calculus
                                                { category: "calculus", name: "Integral", latex: "\\\\( \\\\int_{a}^{b} f(x) dx \\\\)", preview: "∫ᵃᵇ f(x)dx" },
                                                { category: "calculus", name: "Sigma Sum", latex: "\\\\( \\\\sum_{i=1}^{n} x_i \\\\)", preview: "Σxᵢ" },
                                                { category: "calculus", name: "Limit", latex: "\\\\( \\\\lim_{x \\\\to \\\\infty} \\\\)", preview: "lim x→∞" },
                                                { category: "calculus", name: "Turunan", latex: "\\\\( \\\\frac{dy}{dx} \\\\)", preview: "dy/dx" },
                                                { category: "calculus", name: "Turunan parsial", latex: "\\\\( \\\\frac{\\\\partial y}{\\\\partial x} \\\\)", preview: "∂y/∂x" },

                                                // Physics
                                                { category: "physics", name: "Dosis Radiasi", latex: "\\\\( D = \\\\frac{E}{m} \\\\)", preview: "D = E/m" },
                                                { category: "physics", name: "Aktivitas", latex: "\\\\( A = \\\\lambda N \\\\)", preview: "A = λN" },
                                                { category: "physics", name: "Peluruhan", latex: "\\\\( N(t) = N_0 e^{-\\\\lambda t} \\\\)", preview: "N(t)=N₀e⁻λt" },
                                                { category: "physics", name: "Half-life", latex: "\\\\( t_{1/2} = \\\\frac{\\\\ln 2}{\\\\lambda} \\\\)", preview: "t½=ln2/λ" },
                                                { category: "physics", name: "E=mc²", latex: "\\\\( E = mc^{2} \\\\)", preview: "E=mc²" },
                                                { category: "physics", name: "Hukum Invers", latex: "\\\\( I = \\\\frac{I_0}{r^2} \\\\)", preview: "I=I₀/r²" },
                                            ],
                                            get filteredFormulas() {
                                                return this.formulas.filter(f => f.category === this.activeCategory);
                                            },
                                            openPicker() {
                                                this.showPicker = true;
                                            },
                                            closePicker() {
                                                this.showPicker = false;
                                            },
                                            insertFormula(latex) {
                                                // Copy to clipboard
                                                const cleanLatex = latex.replace(/\\\\\\\\/g, "\\\\");
                                                navigator.clipboard.writeText(cleanLatex).then(() => {
                                                    // Show notification
                                                    const notification = document.createElement("div");
                                                    notification.innerHTML = "✅ Rumus disalin! Paste (Ctrl+V) di editor.";
                                                    notification.style.cssText = "position: fixed; bottom: 20px; right: 20px; background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; z-index: 99999; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);";
                                                    document.body.appendChild(notification);
                                                    setTimeout(() => notification.remove(), 2500);
                                                    this.closePicker();
                                                });
                                            }
                                        }
                                    }
                                </script>
                            '))
                            ->columnSpanFull(),

                        RichEditor::make('question_text')
                            ->label('Pertanyaan')
                            ->placeholder('Tulis pertanyaan di sini... Gunakan toolbar untuk format teks, upload gambar, dan insert link.')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('soal-image')
                            ->fileAttachmentsVisibility('public')
                            ->columnSpanFull(),

                        Repeater::make('options_data')
                            ->label('Pilihan Jawaban')
                            ->addActionLabel('Tambah Pilihan')
                            ->helperText('Kode A, B, C, dst akan dibuat otomatis sesuai urutan opsi.')
                            ->minItems(2)
                            ->maxItems(10)
                            ->reorderable()
                            ->columns(12)
                            ->live()
                            ->schema([
                                TextInput::make('teks')
                                    ->label('Teks Pilihan')
                                    ->placeholder('Masukkan teks pilihan')
                                    ->required()
                                    ->columnSpan(9)
                                    ->live(),

                                TextInput::make('bobot')
                                    ->label('Bobot Nilai')
                                    ->placeholder('Masukkan bobot nilai')
                                    ->numeric()
                                    ->visible(fn(): bool => $this->isStructural())
                                    ->required(fn(): bool => $this->isStructural())
                                    ->columnSpan(3)
                                    ->live(),
                            ])
                            ->default(fn(): array => $this->getDefaultOptionsData())
                            ->columnSpanFull(),

                        Select::make('scoring_config.correct')
                            ->label('Kunci Jawaban Benar')
                            ->placeholder('Pilih jawaban yang benar')
                            ->options(fn(Get $get): array => $this->generateOptionChoices($get))
                            ->required(fn(): bool => $this->isTechnical())
                            ->visible(fn(): bool => $this->isTechnical())
                            ->searchable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question_text')
                    ->label('Soal')
                    ->formatStateUsing(function ($state = null): string {
                        try {
                            return view('filament.tables.columns.math-text', [
                                'content' => Str::limit((string) $state, 80),
                            ])->render();
                        } catch (\Throwable $e) {
                            return Str::limit(strip_tags((string) $state), 80);
                        }
                    }),

                TextColumn::make('scoring_config')
                    ->label('Konfigurasi Penilaian')
                    ->formatStateUsing(fn($state, Question $record): string => $this->formatScoringConfig($record)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Soal Baru')
                    ->modalWidth('7xl')
                    ->slideOver()
                    ->mutateFormDataUsing(fn(array $data): array => $this->normalisasiScoringConfig($data)),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail')
                    ->modalWidth('7xl')
                    ->slideOver()
                    ->modalContent(fn(Question $record): \Illuminate\Contracts\View\View => view('filament.modals.question-detail', [
                        'record' => $record,
                        'manager' => $this,
                    ])),
                EditAction::make()
                    ->label('Ubah')
                    ->modalWidth('7xl')
                    ->slideOver()
                    ->mutateRecordDataUsing(function (array $data, Question $record): array {
                        // Fix image URLs for editing - convert absolute URLs to match current server
                        $baseUrl = request()->getSchemeAndHttpHost();

                        // Get question_text from record if not in data
                        $questionText = $data['question_text'] ?? $record->question_text ?? '';

                        if (!empty($questionText)) {
                            $data['question_text'] = preg_replace_callback(
                                '/src=["\']https?:\/\/[^\/]+\/storage\/([^"\']+)["\']/i',
                                fn($m) => 'src="' . $baseUrl . '/storage/' . $m[1] . '"',
                                $questionText
                            );
                        }

                        return $this->hydrateOptionsData([
                            ...$data,
                            'options' => $record->options,
                            'scoring_config' => $record->scoring_config,
                        ]);
                    })
                    ->mutateFormDataUsing(fn(array $data): array => $this->normalisasiScoringConfig($data)),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Hapus Pilihan Terpilih'),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->hydrateOptionsData($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalisasiScoringConfig($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalisasiScoringConfig($data);
    }

    private function normalisasiScoringConfig(array $data): array
    {
        if (! array_key_exists('options_data', $data)) {
            return $data;
        }

        $optionRows = collect($data['options_data'] ?? [])
            ->map(fn(array $item): array => [
                'teks' => trim((string) ($item['teks'] ?? '')),
                'bobot' => $item['bobot'] ?? null,
            ])
            ->filter(fn(array $item): bool => filled($item['teks']))
            ->values();

        if ($optionRows->isEmpty()) {
            $optionRows = collect($this->getDefaultOptionsData());
        }

        $options = [];
        $scoring = [];

        foreach ($optionRows as $index => $item) {
            $kode = $this->alphabetFromIndex($index);
            $options[$kode] = $item['teks'];

            if ($this->isStructural()) {
                $nilai = $item['bobot'] ?? 0;
                $scoring[$kode] = is_numeric($nilai) ? (float) $nilai : 0.0;
            }
        }

        $data['options'] = $options;

        if ($this->isTechnical()) {
            $correct = Str::upper((string) ($data['scoring_config']['correct'] ?? ''));

            if (! array_key_exists($correct, $options)) {
                $correct = array_key_first($options);
            }

            $data['scoring_config'] = [
                'correct' => $correct,
            ];
        } else {
            $data['scoring_config'] = $scoring;
        }

        unset($data['options_data']);

        return $data;
    }

    private function hydrateOptionsData(array $data): array
    {
        $tipe = $this->getExamType();
        $options = $data['options'] ?? null;
        $scoring = $data['scoring_config'] ?? [];

        if (! is_array($options) || empty($options)) {
            $data['options_data'] = $this->getDefaultOptionsData();

            if ($tipe === 'technical') {
                $data['scoring_config'] = [
                    'correct' => 'A',
                ];
            }

            return $data;
        }

        $optionsData = collect($options)
            ->map(function ($teks, $kode) use ($tipe, $scoring): array {
                $row = [
                    'teks' => $teks,
                ];

                if ($tipe === 'structural') {
                    $row['bobot'] = $scoring[$kode] ?? null;
                }

                return $row;
            })
            ->values()
            ->all();

        $data['options_data'] = array_values($optionsData);

        if ($tipe === 'technical') {
            $data['scoring_config'] = [
                'correct' => $scoring['correct'] ?? array_key_first($options),
            ];
        }

        return $data;
    }

    public function formatScoringConfig(Question $record): string
    {
        $tipe = $record->examPackage?->type ?? 'technical';

        if ($tipe === 'technical') {
            $kunci = $record->scoring_config['correct'] ?? null;

            return 'Kunci: ' . ($kunci ?: '-');
        }

        $bagian = collect($record->scoring_config ?? [])
            ->map(fn($nilai, $kode): string => sprintf('%s=%s', $kode, $nilai))
            ->implode(', ');

        return 'Bobot: ' . ($bagian !== '' ? $bagian : '-');
    }

    private function generateOptionChoices(Get $get): array
    {
        $optionRows = collect($get('options_data') ?? [])
            ->filter(fn($item): bool => filled($item['teks'] ?? null))
            ->values();

        $choices = [];

        foreach ($optionRows as $index => $item) {
            $kode = $this->alphabetFromIndex($index);
            $choices[$kode] = sprintf('%s - %s', $kode, Str::limit((string) $item['teks'], 40));
        }

        return $choices;
    }

    private function getDefaultOptionsData(): array
    {
        return [
            ['teks' => ''],
            ['teks' => ''],
        ];
    }

    private function alphabetFromIndex(int $index): string
    {
        return chr(ord('A') + $index);
    }

    private function isTechnical(): bool
    {
        return $this->getExamType() === 'technical';
    }

    private function isStructural(): bool
    {
        return $this->getExamType() === 'structural';
    }

    private function getExamType(): string
    {
        return $this->getOwnerRecord()?->type ?? 'technical';
    }
}
