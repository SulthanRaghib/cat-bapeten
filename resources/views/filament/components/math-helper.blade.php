{{-- ============================================================
     Komponen Editor Rumus Matematika v2.0
     Teknologi : Alpine.js v3 + KaTeX (render LaTeX real-time)
     Penggunaan: View::make('filament.components.math-helper')
     ============================================================ --}}

@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.10/dist/katex.min.css" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.10/dist/katex.min.js" crossorigin="anonymous"></script>
@endonce

{{-- ── Alpine component harus didaftar SEBELUM div x-data diproses ── --}}
<script>
    (function() {
        function registerMathEditorV2() {
            if (typeof Alpine === 'undefined' || Alpine.data === undefined) return;
            if (Alpine._data && Alpine._data['mathEditorV2']) return; // sudah terdaftar
            Alpine.data('mathEditorV2', mathEditorV2Factory);
        }
        // Coba daftar segera (Alpine sudah ada) atau tunggu event init
        if (window.Alpine) {
            registerMathEditorV2();
        }
        document.addEventListener('alpine:init', registerMathEditorV2);
        document.addEventListener('alpine:initialized', registerMathEditorV2);
    }());
</script>

{{-- ── Root wrapper ── --}}
<div x-data="mathEditorV2()" x-init="init()" class="medt-wrapper">

    {{-- ══════════════════════════════════════════════════════
         PANEL KOMPAK (selalu tampil di atas editor teks)
         ══════════════════════════════════════════════════════ --}}
    <div class="medt-panel">

        {{-- Judul & deskripsi --}}
        <div class="medt-panel-header">
            <div class="medt-panel-icon" aria-hidden="true">∑</div>
            <div>
                <p class="medt-panel-title">{{ __('Math Formula Helper') }}</p>
                <p class="medt-panel-desc">
                    {{ __('Click a formula below to copy it instantly, or open the Full Editor to build your own.') }}
                </p>
            </div>
        </div>

        {{-- Chip cepat — menampilkan render KaTeX asli --}}
        <div class="medt-chip-row">
            <template x-for="chip in quickChips" :key="chip.id">
                <button type="button" class="medt-chip" :title="chip.name" @click.prevent="quickCopy(chip)">
                    <span class="medt-chip-math" x-html="kr(chip.latex, false)"></span>
                    <span class="medt-chip-name" x-text="chip.name"></span>
                </button>
            </template>

            {{-- Tombol buka editor lengkap --}}
            <button type="button" class="medt-open-btn" @click.prevent="openEditor()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <path d="M8 12h8M12 8v8" />
                </svg>
                {{ __('Full Editor') }}
            </button>
        </div>

        <p class="medt-panel-tip">
            <span class="medt-tip-badge">INFO</span>&nbsp;
            {{ __('Click formula → auto-copy → Ctrl+V in editor. Format: \( formula \)') }}
        </p>
    </div>


    {{-- ══════════════════════════════════════════════════════
         MODAL EDITOR LENGKAP
         ══════════════════════════════════════════════════════ --}}
    <div x-show="showEditor" x-cloak class="medt-overlay" @click.self="closeEditor()"
        @keydown.escape.window="closeEditor()" role="dialog" aria-modal="true" aria-label="Editor Rumus Matematika">

        <div class="medt-dialog">

            {{-- ── Header dialog ── --}}
            <div class="medt-dialog-head">
                <div class="medt-dialog-title">
                    <span class="medt-dialog-icon" aria-hidden="true">∑</span>
                    {{ __('Math Formula Helper') }}
                </div>
                <button type="button" class="medt-close" @click.stop="closeEditor()" title="{{ __('Close (Esc)') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- ── Body dialog: 2 kolom ── --}}
            <div class="medt-dialog-body">

                {{-- ─────────────────────────────────────
                     KOLOM KIRI — pemilih simbol/template
                     ───────────────────────────────────── --}}
                <div class="medt-sym-panel">

                    {{-- Tab kategori --}}
                    <div class="medt-tabs" role="tablist">
                        <template x-for="tab in tabs" :key="tab.id">
                            <button type="button" class="medt-tab" role="tab"
                                :class="{ 'is-active': activeTab === tab.id }" @click.prevent="activeTab = tab.id"
                                x-text="tab.label">
                            </button>
                        </template>
                    </div>

                    {{-- Keterangan tab aktif --}}
                    <p class="medt-tab-note" x-text="activeTabNote"></p>

                    {{-- Grid simbol / template --}}
                    <div class="medt-gallery">
                        <template x-for="sym in currentSymbols" :key="sym.id">
                            <button type="button" class="medt-sym-btn" :class="{ 'is-tpl': sym.isTemplate }"
                                :title="sym.desc || sym.name" @click.prevent="insertSym(sym)">
                                <span class="medt-sym-math" x-html="kr(sym.display || sym.latex, !!sym.isTemplate)">
                                </span>
                                <span class="medt-sym-name" x-text="sym.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- ─────────────────────────────────────
                     KOLOM KANAN — input LaTeX + pratinjau
                     ───────────────────────────────────── --}}
                <div class="medt-compose">

                    {{-- Input LaTeX --}}
                    {{-- Toolbar: label + aksi --}}
                    <div class="medt-compose-row">
                        <label class="medt-label" style="margin:0">✏️ Kode LaTeX <span class="medt-label-sub">ketik atau
                                klik simbol</span></label>
                        <div class="medt-toolbar-actions">
                            {{-- Undo --}}
                            <button type="button" class="medt-btn-icon" @click.prevent="undo()" :disabled="!canUndo"
                                title="Undo (Ctrl+Z)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 7v6h6" />
                                    <path d="M3 13A9 9 0 1 0 6 6.7" />
                                </svg>
                            </button>
                            {{-- Redo --}}
                            <button type="button" class="medt-btn-icon" @click.prevent="redo()" :disabled="!canRedo"
                                title="Redo (Ctrl+Y)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 7v6h-6" />
                                    <path d="M21 13A9 9 0 1 1 18 6.7" />
                                </svg>
                            </button>
                            {{-- Hapus --}}
                            <button type="button" class="medt-btn-clear" @click.prevent="clearInput()"
                                title="{{ __('Clear all') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round">
                                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                                </svg>
                                {{ __('Clear') }}
                            </button>
                        </div>
                    </div>

                    <textarea x-ref="latexInput" x-model="latexInput" @input="onInput()" @keydown.tab.prevent="onTab()"
                        @keydown.ctrl.z.prevent="undo()" @keydown.ctrl.y.prevent="redo()" x-init="$nextTick(() => autoResize())" class="medt-textarea"
                        placeholder="Contoh: \frac{a}{b} atau klik simbol di kiri…" spellcheck="false" autocomplete="off">
                    </textarea>
                    <p class="medt-input-hint">Tab = indentasi &nbsp;·&nbsp; <kbd>Ctrl+Z</kbd> Undo &nbsp;·&nbsp;
                        <kbd>Ctrl+Y</kbd> Redo
                    </p>

                    {{-- Pratinjau visual (KaTeX render sesungguhnya) --}}
                    <label class="medt-label" style="margin-top:14px">🔍 {{ __('Visual Preview') }}</label>

                    <div class="medt-preview">
                        {{-- State kosong --}}
                        <div x-show="!latexInput.trim()" class="medt-preview-empty">
                            <span class="medt-preview-empty-icon">∑</span>
                            <span>{{ __('Formula preview appears here') }}</span>
                        </div>

                        {{-- Scroll wrapper: formula bisa di-scroll horizontal jika terlalu panjang --}}
                        <div x-show="latexInput.trim() && !previewError" class="medt-preview-scroll">
                            <div class="medt-preview-render" x-html="previewHtml"></div>
                        </div>

                        {{-- Pesan error sintaks --}}
                        <div x-show="previewError" class="medt-preview-error" x-text="previewError">
                        </div>
                    </div>
                    <p x-show="latexInput.trim() && !previewError" class="medt-preview-hint">
                        <span x-show="displayMode === 'inline'">Mode: <code>\( rumus \)</code> — sebaris</span>
                        <span x-show="displayMode === 'display'">Mode: <code>\[ rumus \]</code> — blok tengah</span>
                        &nbsp;·&nbsp; Geser ← → untuk lihat rumus panjang
                    </p>

                    {{-- Pilih mode tampil --}}
                    <div class="medt-mode-row">
                        <span class="medt-label" style="margin:0;flex-shrink:0">Mode:</span>
                        <div class="medt-mode-toggle">
                            <button type="button" class="medt-mode-btn"
                                :class="{ 'is-active': displayMode === 'inline' }"
                                @click.prevent="setMode('inline')" title="{{ __('Inline') }}">
                                {{ __('Inline') }}&nbsp;<code>\(…\)</code>
                            </button>
                            <button type="button" class="medt-mode-btn"
                                :class="{ 'is-active': displayMode === 'display' }"
                                @click.prevent="setMode('display')" title="{{ __('Center Block') }}">
                                {{ __('Center Block') }}&nbsp;<code>\[…\]</code>
                            </button>
                        </div>
                    </div>

                    {{-- Tombol salin --}}
                    <button type="button" class="medt-copy-btn" :disabled="!latexInput.trim()"
                        @click.prevent="copyFormula()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" />
                            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
                        </svg>
                        <span x-text="copyLabel"></span>
                    </button>

                    {{-- Panduan compact --}}
                    <div class="medt-steps">
                        <span class="medt-steps-title">📋 {{ __('How to use') }}</span>
                        <span
                            class="medt-steps-inline">{{ __('Select symbol → preview → Copy Formula → Ctrl+V in editor.') }}</span>
                    </div>

                </div>{{-- /medt-compose --}}

            </div>{{-- /medt-dialog-body --}}
        </div>{{-- /medt-dialog --}}
    </div>{{-- /medt-overlay --}}

</div>{{-- /medt-wrapper --}}


{{-- ══════════════════════════════════════════════════════════════
     ALPINE.JS DATA COMPONENT
     ══════════════════════════════════════════════════════════════ --}}
<script>
    function mathEditorV2Factory() {
        return {

            /* ── state ── */
            showEditor: false,
            latexInput: '',
            previewHtml: '',
            previewError: '',
            displayMode: 'inline',
            /* 'inline' | 'display' */
            activeTab: 'operators',
            copyLabel: '📋 ' + @js(__('Copy Formula')),

            /* ── undo / redo history ── */
            _history: [''],
            _historyIndex: 0,
            _historyTimer: null,

            /* ────────────────────────────────────────────────
               CHIP CEPAT (panel kompak)
               ──────────────────────────────────────────────── */
            quickChips: [{
                    id: 'frac',
                    name: @js(__('Fraction')),
                    latex: '\\frac{a}{b}'
                },
                {
                    id: 'pow',
                    name: @js(__('Power')),
                    latex: 'x^{2}'
                },
                {
                    id: 'sqrt',
                    name: @js(__('Root')),
                    latex: '\\sqrt{x}'
                },
                {
                    id: 'sum',
                    name: 'Sigma',
                    latex: '\\sum_{i=1}^{n} x_i'
                },
                {
                    id: 'int',
                    name: 'Integral',
                    latex: '\\int_{a}^{b} f(x)\\,dx'
                },
                {
                    id: 'pi',
                    name: 'Pi',
                    latex: '\\pi'
                },
                {
                    id: 'alpha',
                    name: 'Alpha',
                    latex: '\\alpha'
                },
                {
                    id: 'delta',
                    name: 'Delta',
                    latex: '\\Delta'
                },
            ],

            /* ────────────────────────────────────────────────
               TAB KATEGORI
               ──────────────────────────────────────────────── */
            tabs: [{
                    id: 'operators',
                    label: '± ' + @js(__('Operators')),
                    note: @js(__('Click a symbol to insert at the cursor position.'))
                },
                {
                    id: 'structure',
                    label: '∫ ' + @js(__('Structure')),
                    note: @js(__('Ready-to-use templates — click to insert, then replace placeholder letters.'))
                },
                {
                    id: 'greek',
                    label: 'α ' + @js(__('Greek')),
                    note: @js(__('Greek alphabet letters — click to insert.'))
                },
                {
                    id: 'functions',
                    label: 'sin ' + @js(__('Functions')),
                    note: @js(__('Standard math functions — click to insert.'))
                },
                {
                    id: 'nuclear',
                    label: '⚛ ' + @js(__('Nuclear')),
                    note: @js(__('Physics and radiation safety formulas for BAPETEN context.'))
                },
                {
                    id: 'atom',
                    label: '⚗ ' + @js(__('Atom')),
                    note: @js(__('Nuclide notation & element symbols — click to insert.'))
                },
                {
                    id: 'molecule',
                    label: '🧪 ' + @js(__('Molecule')),
                    note: @js(__('Molecular formulas & chemical compounds — click to insert.'))
                },
            ],

            /* ────────────────────────────────────────────────
               KAMUS SIMBOL & TEMPLATE PER KATEGORI
               ──────────────────────────────────────────────── */
            symbols: {

                operators: [{
                        id: 'pm',
                        name: '±',
                        latex: ' \\pm ',
                        desc: 'Plus minus'
                    },
                    {
                        id: 'times',
                        name: '×',
                        latex: ' \\times ',
                        desc: 'Perkalian'
                    },
                    {
                        id: 'div',
                        name: '÷',
                        latex: ' \\div ',
                        desc: 'Pembagian'
                    },
                    {
                        id: 'neq',
                        name: '≠',
                        latex: ' \\neq ',
                        desc: 'Tidak sama'
                    },
                    {
                        id: 'approx',
                        name: '≈',
                        latex: ' \\approx ',
                        desc: 'Kira-kira'
                    },
                    {
                        id: 'leq',
                        name: '≤',
                        latex: ' \\leq ',
                        desc: 'Lebih kecil sama dengan'
                    },
                    {
                        id: 'geq',
                        name: '≥',
                        latex: ' \\geq ',
                        desc: 'Lebih besar sama dengan'
                    },
                    {
                        id: 'infty',
                        name: '∞',
                        latex: ' \\infty ',
                        desc: 'Tak hingga'
                    },
                    {
                        id: 'deg',
                        name: '°',
                        latex: '^{\\circ}',
                        desc: 'Derajat'
                    },
                    {
                        id: 'propto',
                        name: '∝',
                        latex: ' \\propto ',
                        desc: 'Berbanding lurus'
                    },
                    {
                        id: 'sim',
                        name: '~',
                        latex: ' \\sim ',
                        desc: 'Sebanding/mirip'
                    },
                    {
                        id: 'equiv',
                        name: '≡',
                        latex: ' \\equiv ',
                        desc: 'Kongruen / identik'
                    },
                    {
                        id: 'in',
                        name: '∈',
                        latex: ' \\in ',
                        desc: 'Elemen dari'
                    },
                    {
                        id: 'notin',
                        name: '∉',
                        latex: ' \\notin ',
                        desc: 'Bukan elemen dari'
                    },
                    {
                        id: 'subset',
                        name: '⊂',
                        latex: ' \\subset ',
                        desc: 'Himpunan bagian'
                    },
                    {
                        id: 'perp',
                        name: '⊥',
                        latex: ' \\perp ',
                        desc: 'Tegak lurus'
                    },
                    {
                        id: 'parallel',
                        name: '∥',
                        latex: ' \\parallel ',
                        desc: 'Sejajar'
                    },
                    {
                        id: 'angle',
                        name: '∠',
                        latex: ' \\angle ',
                        desc: 'Sudut'
                    },
                    {
                        id: 'triangle',
                        name: '△',
                        latex: ' \\triangle ',
                        desc: 'Segitiga'
                    },
                    {
                        id: 'nabla',
                        name: '∇',
                        latex: ' \\nabla ',
                        desc: 'Nabla/gradien'
                    },
                    {
                        id: 'cdot',
                        name: '·',
                        latex: ' \\cdot ',
                        desc: 'Titik (perkalian)'
                    },
                    {
                        id: 'circ',
                        name: '○',
                        latex: ' \\circ ',
                        desc: 'Komposisi fungsi'
                    },
                ],

                structure: [{
                        id: 'frac',
                        name: 'Pecahan',
                        isTemplate: true,
                        latex: '\\frac{a}{b}',
                        display: '\\frac{a}{b}'
                    },
                    {
                        id: 'dfrac',
                        name: 'Pecahan Besar',
                        isTemplate: true,
                        latex: '\\dfrac{a}{b}',
                        display: '\\frac{a}{b}'
                    },
                    {
                        id: 'frac2',
                        name: 'Pecahan Bertingkat',
                        isTemplate: true,
                        latex: '\\frac{\\frac{a}{b}}{c}',
                        display: '\\frac{a/b}{c}'
                    },
                    {
                        id: 'sqrt',
                        name: 'Akar √',
                        isTemplate: true,
                        latex: '\\sqrt{x}',
                        display: '\\sqrt{x}'
                    },
                    {
                        id: 'sqrtn',
                        name: 'Akar ke-n',
                        isTemplate: true,
                        latex: '\\sqrt[n]{x}',
                        display: '\\sqrt[n]{x}'
                    },
                    {
                        id: 'pow',
                        name: 'Pangkat',
                        isTemplate: true,
                        latex: 'x^{n}',
                        display: 'x^{n}'
                    },
                    {
                        id: 'sub',
                        name: 'Subscript',
                        isTemplate: true,
                        latex: 'x_{i}',
                        display: 'x_{i}'
                    },
                    {
                        id: 'subpow',
                        name: 'Sub + Pangkat',
                        isTemplate: true,
                        latex: 'x_{i}^{n}',
                        display: 'x_{i}^{n}'
                    },
                    {
                        id: 'int_def',
                        name: 'Integral Tentu',
                        isTemplate: true,
                        latex: '\\int_{a}^{b} f(x)\\,dx',
                        display: '\\int_{a}^{b}'
                    },
                    {
                        id: 'int_indef',
                        name: 'Integral Tak Tentu',
                        isTemplate: true,
                        latex: '\\int f(x)\\,dx',
                        display: '\\int f\\,dx'
                    },
                    {
                        id: 'sum',
                        name: 'Sigma Σ',
                        isTemplate: true,
                        latex: '\\sum_{i=1}^{n} x_i',
                        display: '\\sum_{i}^{n}'
                    },
                    {
                        id: 'prod',
                        name: 'Produk Π',
                        isTemplate: true,
                        latex: '\\prod_{i=1}^{n} x_i',
                        display: '\\prod_{i}^{n}'
                    },
                    {
                        id: 'lim',
                        name: 'Limit',
                        isTemplate: true,
                        latex: '\\lim_{x \\to 0} f(x)',
                        display: '\\lim_{x\\to 0}'
                    },
                    {
                        id: 'deriv',
                        name: 'Turunan',
                        isTemplate: true,
                        latex: '\\frac{d}{dx} f(x)',
                        display: '\\frac{d}{dx}'
                    },
                    {
                        id: 'partial',
                        name: 'Turunan Parsial',
                        isTemplate: true,
                        latex: '\\frac{\\partial f}{\\partial x}',
                        display: '\\frac{\\partial}{\\partial x}'
                    },
                    {
                        id: 'mat2',
                        name: 'Matriks 2×2',
                        isTemplate: true,
                        latex: '\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}',
                        display: '\\begin{pmatrix}a&b\\\\c&d\\end{pmatrix}'
                    },
                    {
                        id: 'abs',
                        name: 'Nilai Mutlak |x|',
                        isTemplate: true,
                        latex: '\\left| x \\right|',
                        display: '|x|'
                    },
                    {
                        id: 'vec',
                        name: 'Vektor',
                        isTemplate: true,
                        latex: '\\vec{v}',
                        display: '\\vec{v}'
                    },
                    {
                        id: 'overline',
                        name: 'Garis Atas',
                        isTemplate: true,
                        latex: '\\overline{AB}',
                        display: '\\overline{AB}'
                    },
                    {
                        id: 'hat',
                        name: 'Topi',
                        isTemplate: true,
                        latex: '\\hat{x}',
                        display: '\\hat{x}'
                    },
                ],

                greek: [{
                        id: 'alpha',
                        name: 'alpha',
                        latex: '\\alpha '
                    },
                    {
                        id: 'beta',
                        name: 'beta',
                        latex: '\\beta '
                    },
                    {
                        id: 'gamma',
                        name: 'gamma',
                        latex: '\\gamma '
                    },
                    {
                        id: 'delta_l',
                        name: 'delta',
                        latex: '\\delta '
                    },
                    {
                        id: 'epsilon',
                        name: 'epsilon',
                        latex: '\\epsilon '
                    },
                    {
                        id: 'zeta',
                        name: 'zeta',
                        latex: '\\zeta '
                    },
                    {
                        id: 'eta',
                        name: 'eta',
                        latex: '\\eta '
                    },
                    {
                        id: 'theta',
                        name: 'theta',
                        latex: '\\theta '
                    },
                    {
                        id: 'kappa',
                        name: 'kappa',
                        latex: '\\kappa '
                    },
                    {
                        id: 'lambda',
                        name: 'lambda',
                        latex: '\\lambda '
                    },
                    {
                        id: 'mu',
                        name: 'mu',
                        latex: '\\mu '
                    },
                    {
                        id: 'nu',
                        name: 'nu',
                        latex: '\\nu '
                    },
                    {
                        id: 'xi',
                        name: 'xi',
                        latex: '\\xi '
                    },
                    {
                        id: 'pi_l',
                        name: 'pi',
                        latex: '\\pi '
                    },
                    {
                        id: 'rho',
                        name: 'rho',
                        latex: '\\rho '
                    },
                    {
                        id: 'sigma_l',
                        name: 'sigma',
                        latex: '\\sigma '
                    },
                    {
                        id: 'tau',
                        name: 'tau',
                        latex: '\\tau '
                    },
                    {
                        id: 'phi',
                        name: 'phi',
                        latex: '\\phi '
                    },
                    {
                        id: 'chi',
                        name: 'chi',
                        latex: '\\chi '
                    },
                    {
                        id: 'psi',
                        name: 'psi',
                        latex: '\\psi '
                    },
                    {
                        id: 'omega',
                        name: 'omega',
                        latex: '\\omega '
                    },
                    {
                        id: 'Gamma',
                        name: 'Gamma',
                        latex: '\\Gamma '
                    },
                    {
                        id: 'Delta',
                        name: 'Delta',
                        latex: '\\Delta '
                    },
                    {
                        id: 'Theta',
                        name: 'Theta',
                        latex: '\\Theta '
                    },
                    {
                        id: 'Lambda',
                        name: 'Lambda',
                        latex: '\\Lambda '
                    },
                    {
                        id: 'Xi',
                        name: 'Xi',
                        latex: '\\Xi '
                    },
                    {
                        id: 'Pi',
                        name: 'Pi',
                        latex: '\\Pi '
                    },
                    {
                        id: 'Sigma',
                        name: 'Sigma',
                        latex: '\\Sigma '
                    },
                    {
                        id: 'Phi',
                        name: 'Phi',
                        latex: '\\Phi '
                    },
                    {
                        id: 'Psi',
                        name: 'Psi',
                        latex: '\\Psi '
                    },
                    {
                        id: 'Omega',
                        name: 'Omega',
                        latex: '\\Omega '
                    },
                ],

                functions: [{
                        id: 'sin',
                        name: 'sin',
                        latex: '\\sin '
                    },
                    {
                        id: 'cos',
                        name: 'cos',
                        latex: '\\cos '
                    },
                    {
                        id: 'tan',
                        name: 'tan',
                        latex: '\\tan '
                    },
                    {
                        id: 'cot',
                        name: 'cot',
                        latex: '\\cot '
                    },
                    {
                        id: 'sec',
                        name: 'sec',
                        latex: '\\sec '
                    },
                    {
                        id: 'csc',
                        name: 'csc',
                        latex: '\\csc '
                    },
                    {
                        id: 'arcsin',
                        name: 'arcsin',
                        latex: '\\arcsin '
                    },
                    {
                        id: 'arccos',
                        name: 'arccos',
                        latex: '\\arccos '
                    },
                    {
                        id: 'arctan',
                        name: 'arctan',
                        latex: '\\arctan '
                    },
                    {
                        id: 'log',
                        name: 'log',
                        latex: '\\log '
                    },
                    {
                        id: 'log10',
                        name: 'log₁₀',
                        latex: '\\log_{10} '
                    },
                    {
                        id: 'ln',
                        name: 'ln',
                        latex: '\\ln '
                    },
                    {
                        id: 'exp',
                        name: 'exp',
                        latex: '\\exp '
                    },
                    {
                        id: 'abs_fn',
                        name: '|x|',
                        isTemplate: true,
                        latex: '\\left| x \\right|',
                        display: '|x|'
                    },
                    {
                        id: 'norm_fn',
                        name: '‖x‖',
                        isTemplate: true,
                        latex: '\\left\\| x \\right\\|',
                        display: '\\|x\\|'
                    },
                    {
                        id: 'floor',
                        name: '⌊x⌋',
                        isTemplate: true,
                        latex: '\\lfloor x \\rfloor',
                        display: '\\lfloor x\\rfloor'
                    },
                    {
                        id: 'ceil',
                        name: '⌈x⌉',
                        isTemplate: true,
                        latex: '\\lceil x \\rceil',
                        display: '\\lceil x\\rceil'
                    },
                    {
                        id: 'paren',
                        name: '(x)',
                        isTemplate: true,
                        latex: '\\left( x \\right)',
                        display: '(x)'
                    },
                    {
                        id: 'brack',
                        name: '[x]',
                        isTemplate: true,
                        latex: '\\left[ x \\right]',
                        display: '[x]'
                    },
                ],

                nuclear: [{
                        id: 'n_dose',
                        name: 'Dosis D',
                        isTemplate: true,
                        latex: 'D = \\frac{E}{m}',
                        desc: 'Dosis serap (Gy)'
                    },
                    {
                        id: 'n_act',
                        name: 'Aktivitas A',
                        isTemplate: true,
                        latex: 'A = \\lambda N',
                        desc: 'Aktivitas radionuklida (Bq)'
                    },
                    {
                        id: 'n_decay',
                        name: 'Peluruhan',
                        isTemplate: true,
                        latex: 'N(t) = N_0 e^{-\\lambda t}',
                        desc: 'Hukum peluruhan radioaktif'
                    },
                    {
                        id: 'n_half',
                        name: 'Waktu Paro',
                        isTemplate: true,
                        latex: 't_{1/2} = \\frac{\\ln 2}{\\lambda}',
                        desc: 'Waktu paruh (T½)'
                    },
                    {
                        id: 'n_emc2',
                        name: 'E = mc²',
                        isTemplate: true,
                        latex: 'E = mc^{2}',
                        desc: 'Kesetaraan massa-energi'
                    },
                    {
                        id: 'n_inv',
                        name: 'Hk. Invers Kuadrat',
                        isTemplate: true,
                        latex: 'I = \\frac{I_0}{r^2}',
                        desc: 'Hukum invers kuadrat jarak'
                    },
                    {
                        id: 'n_flux',
                        name: 'Fluks Neutron',
                        isTemplate: true,
                        latex: '\\Phi = \\frac{N}{A \\cdot t}',
                        desc: 'Fluks neutron (n/cm²·s)'
                    },
                    {
                        id: 'n_equiv',
                        name: 'Dosis Ekivalen H',
                        isTemplate: true,
                        latex: 'H = D \\cdot w_R',
                        desc: 'Dosis ekivalen (Sv)'
                    },
                    {
                        id: 'n_drate',
                        name: 'Laju Dosis',
                        isTemplate: true,
                        latex: '\\dot{D} = \\frac{dD}{dt}',
                        desc: 'Laju dosis serap (Gy/s)'
                    },
                    {
                        id: 'n_atten',
                        name: 'Atenuasi',
                        isTemplate: true,
                        latex: 'I = I_0 \\, e^{-\\mu x}',
                        desc: 'Atenuasi radiasi foton'
                    },
                    {
                        id: 'n_hvl',
                        name: 'HVL (Paro Hamparan)',
                        isTemplate: true,
                        latex: 'HVL = \\frac{\\ln 2}{\\mu}',
                        desc: 'Half Value Layer'
                    },
                    {
                        id: 'n_buildup',
                        name: 'Faktor Build-up',
                        isTemplate: true,
                        latex: 'D = B \\cdot D_0 \\cdot e^{-\\mu x}',
                        desc: 'Faktor build-up radiasi'
                    },
                    {
                        id: 'n_teff',
                        name: 'T Efektif',
                        isTemplate: true,
                        latex: '\\frac{1}{T_{eff}} = \\frac{1}{T_{bio}} + \\frac{1}{T_{1/2}}',
                        desc: 'Waktu paro efektif'
                    },
                    {
                        id: 'n_conc',
                        name: 'Konsentrasi',
                        isTemplate: true,
                        latex: 'C = \\frac{A}{V}',
                        desc: 'Konsentrasi aktivitas (Bq/m³)'
                    },
                    {
                        id: 'n_compton',
                        name: 'Hamburan Compton',
                        isTemplate: true,
                        latex: "E' = \\frac{E_0}{1 + \\frac{E_0}{m_e c^2}(1-\\cos\\theta)}",
                        desc: 'Energi foton setelah hamburan Compton'
                    },
                ],

                atom: [
                    /* ── Partikel dasar ── */
                    {
                        id: 'nuclide_tpl',
                        name: 'Nuklida Umum',
                        isTemplate: true,
                        latex: '{}_{Z}^{A}\\text{El}',
                        display: '{}_{Z}^{A}\\text{El}',
                        desc: 'Template notasi nuklida umum'
                    },
                    {
                        id: 'proton',
                        name: 'Proton p',
                        isTemplate: true,
                        latex: '{}_{1}^{1}\\text{p}',
                        display: '{}_{1}^{1}\\text{p}',
                        desc: 'Proton'
                    },
                    {
                        id: 'neutron',
                        name: 'Neutron n',
                        isTemplate: true,
                        latex: '{}_{0}^{1}\\text{n}',
                        display: '{}_{0}^{1}\\text{n}',
                        desc: 'Neutron'
                    },
                    {
                        id: 'electron',
                        name: 'Elektron e⁻',
                        isTemplate: true,
                        latex: '{}_{-1}^{\\;0}\\text{e}',
                        display: '{}_{-1}^{\\;0}\\text{e}',
                        desc: 'Elektron'
                    },
                    {
                        id: 'positron',
                        name: 'Positron e⁺',
                        isTemplate: true,
                        latex: '{}_{+1}^{\\;0}\\text{e}',
                        display: '{}_{+1}^{\\;0}\\text{e}',
                        desc: 'Positron'
                    },
                    {
                        id: 'alpha_p',
                        name: 'α Partikel',
                        isTemplate: true,
                        latex: '{}_{2}^{4}\\text{He}',
                        display: '{}_{2}^{4}\\text{He}',
                        desc: 'Partikel alfa'
                    },
                    {
                        id: 'beta_m',
                        name: 'β⁻ Partikel',
                        isTemplate: true,
                        latex: '{}_{-1}^{\\;0}\\beta^{-}',
                        display: '\\beta^{-}',
                        desc: 'Beta minus'
                    },
                    {
                        id: 'beta_p',
                        name: 'β⁺ Positron',
                        isTemplate: true,
                        latex: '{}_{+1}^{\\;0}\\beta^{+}',
                        display: '\\beta^{+}',
                        desc: 'Beta plus'
                    },
                    {
                        id: 'gamma_q',
                        name: 'γ Foton',
                        isTemplate: true,
                        latex: '{}_{0}^{0}\\gamma',
                        display: '\\gamma',
                        desc: 'Foton gamma'
                    },
                    {
                        id: 'neutrino',
                        name: 'Anti-ν Neutrino',
                        isTemplate: true,
                        latex: '\\bar{\\nu}_{e}',
                        display: '\\bar{\\nu}_{e}',
                        desc: 'Anti-neutrino elektron'
                    },
                    /* ── Isotop ringan ── */
                    {
                        id: 'H1',
                        name: '¹H Protium',
                        isTemplate: true,
                        latex: '{}_{1}^{1}\\text{H}',
                        display: '{}_{1}^{1}\\text{H}',
                        desc: 'Protium'
                    },
                    {
                        id: 'H2',
                        name: '²H Deuterium',
                        isTemplate: true,
                        latex: '{}_{1}^{2}\\text{H}',
                        display: '{}_{1}^{2}\\text{H}',
                        desc: 'Deuterium (D)'
                    },
                    {
                        id: 'H3',
                        name: '³H Tritium',
                        isTemplate: true,
                        latex: '{}_{1}^{3}\\text{H}',
                        display: '{}_{1}^{3}\\text{H}',
                        desc: 'Tritium (T)'
                    },
                    {
                        id: 'He3',
                        name: '³He',
                        isTemplate: true,
                        latex: '{}_{2}^{3}\\text{He}',
                        display: '{}_{2}^{3}\\text{He}',
                        desc: 'Helium-3'
                    },
                    {
                        id: 'He4',
                        name: '⁴He',
                        isTemplate: true,
                        latex: '{}_{2}^{4}\\text{He}',
                        display: '{}_{2}^{4}\\text{He}',
                        desc: 'Helium-4'
                    },
                    {
                        id: 'C12',
                        name: '¹²C',
                        isTemplate: true,
                        latex: '{}_{6}^{12}\\text{C}',
                        display: '{}_{6}^{12}\\text{C}',
                        desc: 'Karbon-12'
                    },
                    {
                        id: 'C13',
                        name: '¹³C',
                        isTemplate: true,
                        latex: '{}_{6}^{13}\\text{C}',
                        display: '{}_{6}^{13}\\text{C}',
                        desc: 'Karbon-13'
                    },
                    {
                        id: 'C14',
                        name: '¹⁴C',
                        isTemplate: true,
                        latex: '{}_{6}^{14}\\text{C}',
                        display: '{}_{6}^{14}\\text{C}',
                        desc: 'Karbon-14 (radioaktif)'
                    },
                    {
                        id: 'N14',
                        name: '¹⁴N',
                        isTemplate: true,
                        latex: '{}_{7}^{14}\\text{N}',
                        display: '{}_{7}^{14}\\text{N}',
                        desc: 'Nitrogen-14'
                    },
                    {
                        id: 'O16',
                        name: '¹⁶O',
                        isTemplate: true,
                        latex: '{}_{8}^{16}\\text{O}',
                        display: '{}_{8}^{16}\\text{O}',
                        desc: 'Oksigen-16'
                    },
                    {
                        id: 'O18',
                        name: '¹⁸O',
                        isTemplate: true,
                        latex: '{}_{8}^{18}\\text{O}',
                        display: '{}_{8}^{18}\\text{O}',
                        desc: 'Oksigen-18'
                    },
                    {
                        id: 'Na23',
                        name: '²³Na',
                        isTemplate: true,
                        latex: '{}_{11}^{23}\\text{Na}',
                        display: '{}_{11}^{23}\\text{Na}',
                        desc: 'Natrium-23'
                    },
                    {
                        id: 'Co60',
                        name: '⁶⁰Co',
                        isTemplate: true,
                        latex: '{}_{27}^{60}\\text{Co}',
                        display: '{}_{27}^{60}\\text{Co}',
                        desc: 'Kobalt-60'
                    },
                    {
                        id: 'Sr90',
                        name: '⁹⁰Sr',
                        isTemplate: true,
                        latex: '{}_{38}^{90}\\text{Sr}',
                        display: '{}_{38}^{90}\\text{Sr}',
                        desc: 'Strontium-90'
                    },
                    {
                        id: 'Y90',
                        name: '⁹⁰Y',
                        isTemplate: true,
                        latex: '{}_{39}^{90}\\text{Y}',
                        display: '{}_{39}^{90}\\text{Y}',
                        desc: 'Yttrium-90'
                    },
                    {
                        id: 'Tc99m',
                        name: '⁹⁹ᵐTc',
                        isTemplate: true,
                        latex: '{}_{43}^{99m}\\text{Tc}',
                        display: '{}_{43}^{99m}\\text{Tc}',
                        desc: 'Teknesium-99m'
                    },
                    {
                        id: 'I131',
                        name: '¹³¹I',
                        isTemplate: true,
                        latex: '{}_{53}^{131}\\text{I}',
                        display: '{}_{53}^{131}\\text{I}',
                        desc: 'Iodium-131'
                    },
                    {
                        id: 'I125',
                        name: '¹²⁵I',
                        isTemplate: true,
                        latex: '{}_{53}^{125}\\text{I}',
                        display: '{}_{53}^{125}\\text{I}',
                        desc: 'Iodium-125'
                    },
                    {
                        id: 'Cs137',
                        name: '¹³⁷Cs',
                        isTemplate: true,
                        latex: '{}_{55}^{137}\\text{Cs}',
                        display: '{}_{55}^{137}\\text{Cs}',
                        desc: 'Sesium-137'
                    },
                    {
                        id: 'Ba137',
                        name: '¹³⁷Ba',
                        isTemplate: true,
                        latex: '{}_{56}^{137}\\text{Ba}',
                        display: '{}_{56}^{137}\\text{Ba}',
                        desc: 'Barium-137'
                    },
                    {
                        id: 'Au198',
                        name: '¹⁹⁸Au',
                        isTemplate: true,
                        latex: '{}_{79}^{198}\\text{Au}',
                        display: '{}_{79}^{198}\\text{Au}',
                        desc: 'Emas-198'
                    },
                    {
                        id: 'Pb208',
                        name: '²⁰⁸Pb',
                        isTemplate: true,
                        latex: '{}_{82}^{208}\\text{Pb}',
                        display: '{}_{82}^{208}\\text{Pb}',
                        desc: 'Timbal-208'
                    },
                    {
                        id: 'Po210',
                        name: '²¹⁰Po',
                        isTemplate: true,
                        latex: '{}_{84}^{210}\\text{Po}',
                        display: '{}_{84}^{210}\\text{Po}',
                        desc: 'Polonium-210'
                    },
                    {
                        id: 'Rn222',
                        name: '²²²Rn',
                        isTemplate: true,
                        latex: '{}_{86}^{222}\\text{Rn}',
                        display: '{}_{86}^{222}\\text{Rn}',
                        desc: 'Radon-222'
                    },
                    {
                        id: 'Ra226',
                        name: '²²⁶Ra',
                        isTemplate: true,
                        latex: '{}_{88}^{226}\\text{Ra}',
                        display: '{}_{88}^{226}\\text{Ra}',
                        desc: 'Radium-226'
                    },
                    {
                        id: 'Th232',
                        name: '²³²Th',
                        isTemplate: true,
                        latex: '{}_{90}^{232}\\text{Th}',
                        display: '{}_{90}^{232}\\text{Th}',
                        desc: 'Torium-232'
                    },
                    {
                        id: 'U235',
                        name: '²³⁵U',
                        isTemplate: true,
                        latex: '{}_{92}^{235}\\text{U}',
                        display: '{}_{92}^{235}\\text{U}',
                        desc: 'Uranium-235'
                    },
                    {
                        id: 'U238',
                        name: '²³⁸U',
                        isTemplate: true,
                        latex: '{}_{92}^{238}\\text{U}',
                        display: '{}_{92}^{238}\\text{U}',
                        desc: 'Uranium-238'
                    },
                    {
                        id: 'Pu239',
                        name: '²³⁹Pu',
                        isTemplate: true,
                        latex: '{}_{94}^{239}\\text{Pu}',
                        display: '{}_{94}^{239}\\text{Pu}',
                        desc: 'Plutonium-239'
                    },
                    {
                        id: 'Am241',
                        name: '²⁴¹Am',
                        isTemplate: true,
                        latex: '{}_{95}^{241}\\text{Am}',
                        display: '{}_{95}^{241}\\text{Am}',
                        desc: 'Amerisium-241'
                    },
                    /* ── Reaksi nuklir ── */
                    {
                        id: 'decay_alpha',
                        name: 'Peluruhan α',
                        isTemplate: true,
                        latex: '{}_{Z}^{A}\\text{X} \\rightarrow {}_{Z-2}^{A-4}\\text{Y} + {}_{2}^{4}\\text{He}',
                        display: '\\text{X}\\rightarrow\\text{Y}+\\alpha',
                        desc: 'Reaksi peluruhan alfa'
                    },
                    {
                        id: 'decay_beta',
                        name: 'Peluruhan β⁻',
                        isTemplate: true,
                        latex: '{}_{Z}^{A}\\text{X} \\rightarrow {}_{Z+1}^{A}\\text{Y} + {}_{-1}^{\\;0}\\beta^{-} + \\bar{\\nu}_{e}',
                        display: '\\text{X}\\rightarrow\\text{Y}+\\beta^{-}',
                        desc: 'Reaksi peluruhan beta minus'
                    },
                    {
                        id: 'decay_betap',
                        name: 'Peluruhan β⁺',
                        isTemplate: true,
                        latex: '{}_{Z}^{A}\\text{X} \\rightarrow {}_{Z-1}^{A}\\text{Y} + {}_{+1}^{\\;0}\\beta^{+} + \\nu_{e}',
                        display: '\\text{X}\\rightarrow\\text{Y}+\\beta^{+}',
                        desc: 'Reaksi peluruhan beta plus'
                    },
                    {
                        id: 'fission',
                        name: 'Fisi U-235',
                        isTemplate: true,
                        latex: '{}_{92}^{235}\\text{U} + {}_{0}^{1}\\text{n} \\rightarrow {}_{56}^{141}\\text{Ba} + {}_{36}^{92}\\text{Kr} + 3\\,{}_{0}^{1}\\text{n}',
                        display: '\\text{U}+\\text{n}\\rightarrow\\text{fisi}',
                        desc: 'Contoh reaksi fisi U-235'
                    },
                    {
                        id: 'fusion',
                        name: 'Fusi D-T',
                        isTemplate: true,
                        latex: '{}_{1}^{2}\\text{H} + {}_{1}^{3}\\text{H} \\rightarrow {}_{2}^{4}\\text{He} + {}_{0}^{1}\\text{n}',
                        display: '\\text{D}+\\text{T}\\rightarrow\\text{He}+\\text{n}',
                        desc: 'Reaksi fusi deuterium-tritium'
                    },
                ],

                molecule: [
                    /* ── Gas & Molekul Umum ── */
                    {
                        id: 'm_h2',
                        name: 'H\u2082',
                        isTemplate: true,
                        latex: '\\text{H}_2',
                        display: '\\text{H}_2',
                        desc: 'Gas hidrogen'
                    },
                    {
                        id: 'm_o2',
                        name: 'O\u2082',
                        isTemplate: true,
                        latex: '\\text{O}_2',
                        display: '\\text{O}_2',
                        desc: 'Gas oksigen'
                    },
                    {
                        id: 'm_n2',
                        name: 'N\u2082',
                        isTemplate: true,
                        latex: '\\text{N}_2',
                        display: '\\text{N}_2',
                        desc: 'Gas nitrogen'
                    },
                    {
                        id: 'm_o3',
                        name: 'O\u2083 Ozon',
                        isTemplate: true,
                        latex: '\\text{O}_3',
                        display: '\\text{O}_3',
                        desc: 'Ozon'
                    },
                    {
                        id: 'm_co2',
                        name: 'CO\u2082',
                        isTemplate: true,
                        latex: '\\text{CO}_2',
                        display: '\\text{CO}_2',
                        desc: 'Karbon dioksida'
                    },
                    {
                        id: 'm_co',
                        name: 'CO',
                        isTemplate: true,
                        latex: '\\text{CO}',
                        display: '\\text{CO}',
                        desc: 'Karbon monoksida'
                    },
                    {
                        id: 'm_h2o',
                        name: 'H\u2082O Air',
                        isTemplate: true,
                        latex: '\\text{H}_2\\text{O}',
                        display: '\\text{H}_2\\text{O}',
                        desc: 'Air'
                    },
                    {
                        id: 'm_h2o2',
                        name: 'H\u2082O\u2082',
                        isTemplate: true,
                        latex: '\\text{H}_2\\text{O}_2',
                        display: '\\text{H}_2\\text{O}_2',
                        desc: 'Hidrogen peroksida'
                    },
                    {
                        id: 'm_nh3',
                        name: 'NH\u2083 Amonia',
                        isTemplate: true,
                        latex: '\\text{NH}_3',
                        display: '\\text{NH}_3',
                        desc: 'Amonia'
                    },
                    {
                        id: 'm_ch4',
                        name: 'CH\u2084 Metana',
                        isTemplate: true,
                        latex: '\\text{CH}_4',
                        display: '\\text{CH}_4',
                        desc: 'Gas metana'
                    },
                    {
                        id: 'm_no2',
                        name: 'NO\u2082',
                        isTemplate: true,
                        latex: '\\text{NO}_2',
                        display: '\\text{NO}_2',
                        desc: 'Nitrogen dioksida'
                    },
                    {
                        id: 'm_so2',
                        name: 'SO\u2082',
                        isTemplate: true,
                        latex: '\\text{SO}_2',
                        display: '\\text{SO}_2',
                        desc: 'Sulfur dioksida'
                    },
                    {
                        id: 'm_so3',
                        name: 'SO\u2083',
                        isTemplate: true,
                        latex: '\\text{SO}_3',
                        display: '\\text{SO}_3',
                        desc: 'Sulfur trioksida'
                    },
                    {
                        id: 'm_h2s',
                        name: 'H\u2082S',
                        isTemplate: true,
                        latex: '\\text{H}_2\\text{S}',
                        display: '\\text{H}_2\\text{S}',
                        desc: 'Hidrogen sulfida'
                    },
                    {
                        id: 'm_cl2',
                        name: 'Cl\u2082',
                        isTemplate: true,
                        latex: '\\text{Cl}_2',
                        display: '\\text{Cl}_2',
                        desc: 'Gas klorin'
                    },
                    {
                        id: 'm_f2',
                        name: 'F\u2082',
                        isTemplate: true,
                        latex: '\\text{F}_2',
                        display: '\\text{F}_2',
                        desc: 'Gas fluor'
                    },
                    /* ── Asam ── */
                    {
                        id: 'm_hcl',
                        name: 'HCl',
                        isTemplate: true,
                        latex: '\\text{HCl}',
                        display: '\\text{HCl}',
                        desc: 'Asam klorida'
                    },
                    {
                        id: 'm_hf',
                        name: 'HF',
                        isTemplate: true,
                        latex: '\\text{HF}',
                        display: '\\text{HF}',
                        desc: 'Asam fluorida'
                    },
                    {
                        id: 'm_hbr',
                        name: 'HBr',
                        isTemplate: true,
                        latex: '\\text{HBr}',
                        display: '\\text{HBr}',
                        desc: 'Asam bromida'
                    },
                    {
                        id: 'm_hi',
                        name: 'HI',
                        isTemplate: true,
                        latex: '\\text{HI}',
                        display: '\\text{HI}',
                        desc: 'Asam iodida'
                    },
                    {
                        id: 'm_hno3',
                        name: 'HNO\u2083',
                        isTemplate: true,
                        latex: '\\text{HNO}_3',
                        display: '\\text{HNO}_3',
                        desc: 'Asam nitrat'
                    },
                    {
                        id: 'm_h2so4',
                        name: 'H\u2082SO\u2084',
                        isTemplate: true,
                        latex: '\\text{H}_2\\text{SO}_4',
                        display: '\\text{H}_2\\text{SO}_4',
                        desc: 'Asam sulfat'
                    },
                    {
                        id: 'm_h3po4',
                        name: 'H\u2083PO\u2084',
                        isTemplate: true,
                        latex: '\\text{H}_3\\text{PO}_4',
                        display: '\\text{H}_3\\text{PO}_4',
                        desc: 'Asam fosfat'
                    },
                    {
                        id: 'm_hcooh',
                        name: 'HCOOH',
                        isTemplate: true,
                        latex: '\\text{HCOOH}',
                        display: '\\text{HCOOH}',
                        desc: 'Asam formiat'
                    },
                    {
                        id: 'm_acetic',
                        name: 'CH\u2083COOH',
                        isTemplate: true,
                        latex: '\\text{CH}_3\\text{COOH}',
                        display: '\\text{CH}_3\\text{COOH}',
                        desc: 'Asam asetat'
                    },
                    /* ── Basa ── */
                    {
                        id: 'm_naoh',
                        name: 'NaOH',
                        isTemplate: true,
                        latex: '\\text{NaOH}',
                        display: '\\text{NaOH}',
                        desc: 'Natrium hidroksida'
                    },
                    {
                        id: 'm_koh',
                        name: 'KOH',
                        isTemplate: true,
                        latex: '\\text{KOH}',
                        display: '\\text{KOH}',
                        desc: 'Kalium hidroksida'
                    },
                    {
                        id: 'm_caoh2',
                        name: 'Ca(OH)\u2082',
                        isTemplate: true,
                        latex: '\\text{Ca(OH)}_2',
                        display: '\\text{Ca(OH)}_2',
                        desc: 'Kalsium hidroksida'
                    },
                    {
                        id: 'm_nh4oh',
                        name: 'NH\u2084OH',
                        isTemplate: true,
                        latex: '\\text{NH}_4\\text{OH}',
                        display: '\\text{NH}_4\\text{OH}',
                        desc: 'Amonium hidroksida'
                    },
                    /* ── Garam & Oksida ── */
                    {
                        id: 'm_nacl',
                        name: 'NaCl Garam',
                        isTemplate: true,
                        latex: '\\text{NaCl}',
                        display: '\\text{NaCl}',
                        desc: 'Natrium klorida'
                    },
                    {
                        id: 'm_kcl',
                        name: 'KCl',
                        isTemplate: true,
                        latex: '\\text{KCl}',
                        display: '\\text{KCl}',
                        desc: 'Kalium klorida'
                    },
                    {
                        id: 'm_caco3',
                        name: 'CaCO\u2083',
                        isTemplate: true,
                        latex: '\\text{CaCO}_3',
                        display: '\\text{CaCO}_3',
                        desc: 'Kalsium karbonat'
                    },
                    {
                        id: 'm_caso4',
                        name: 'CaSO\u2084',
                        isTemplate: true,
                        latex: '\\text{CaSO}_4',
                        display: '\\text{CaSO}_4',
                        desc: 'Kalsium sulfat'
                    },
                    {
                        id: 'm_na2co3',
                        name: 'Na\u2082CO\u2083',
                        isTemplate: true,
                        latex: '\\text{Na}_2\\text{CO}_3',
                        display: '\\text{Na}_2\\text{CO}_3',
                        desc: 'Natrium karbonat / soda abu'
                    },
                    {
                        id: 'm_nahco3',
                        name: 'NaHCO\u2083',
                        isTemplate: true,
                        latex: '\\text{NaHCO}_3',
                        display: '\\text{NaHCO}_3',
                        desc: 'Natrium bikarbonat / soda kue'
                    },
                    {
                        id: 'm_fecl3',
                        name: 'FeCl\u2083',
                        isTemplate: true,
                        latex: '\\text{FeCl}_3',
                        display: '\\text{FeCl}_3',
                        desc: 'Besi(III) klorida'
                    },
                    {
                        id: 'm_al2o3',
                        name: 'Al\u2082O\u2083',
                        isTemplate: true,
                        latex: '\\text{Al}_2\\text{O}_3',
                        display: '\\text{Al}_2\\text{O}_3',
                        desc: 'Aluminium oksida'
                    },
                    {
                        id: 'm_fe2o3',
                        name: 'Fe\u2082O\u2083 Karat',
                        isTemplate: true,
                        latex: '\\text{Fe}_2\\text{O}_3',
                        display: '\\text{Fe}_2\\text{O}_3',
                        desc: 'Besi(III) oksida'
                    },
                    {
                        id: 'm_mno2',
                        name: 'MnO\u2082',
                        isTemplate: true,
                        latex: '\\text{MnO}_2',
                        display: '\\text{MnO}_2',
                        desc: 'Mangan(IV) oksida'
                    },
                    {
                        id: 'm_sio2',
                        name: 'SiO\u2082 Silika',
                        isTemplate: true,
                        latex: '\\text{SiO}_2',
                        display: '\\text{SiO}_2',
                        desc: 'Silikon dioksida'
                    },
                    {
                        id: 'm_tio2',
                        name: 'TiO\u2082',
                        isTemplate: true,
                        latex: '\\text{TiO}_2',
                        display: '\\text{TiO}_2',
                        desc: 'Titanium dioksida'
                    },
                    /* ── Senyawa Nuklir & Radioaktif ── */
                    {
                        id: 'm_uo2',
                        name: 'UO\u2082',
                        isTemplate: true,
                        latex: '\\text{UO}_2',
                        display: '\\text{UO}_2',
                        desc: 'Uranium dioksida (bahan bakar nuklir)'
                    },
                    {
                        id: 'm_u3o8',
                        name: 'U\u2083O\u2088 Yellowcake',
                        isTemplate: true,
                        latex: '\\text{U}_3\\text{O}_8',
                        display: '\\text{U}_3\\text{O}_8',
                        desc: 'Triuranium oktoksida / yellowcake'
                    },
                    {
                        id: 'm_uf6',
                        name: 'UF\u2086 Pengayaan',
                        isTemplate: true,
                        latex: '\\text{UF}_6',
                        display: '\\text{UF}_6',
                        desc: 'Uranium heksafluorida (pengayaan)'
                    },
                    {
                        id: 'm_puo2',
                        name: 'PuO\u2082',
                        isTemplate: true,
                        latex: '\\text{PuO}_2',
                        display: '\\text{PuO}_2',
                        desc: 'Plutonium dioksida'
                    },
                    {
                        id: 'm_tho2',
                        name: 'ThO\u2082',
                        isTemplate: true,
                        latex: '\\text{ThO}_2',
                        display: '\\text{ThO}_2',
                        desc: 'Torium dioksida'
                    },
                    /* ── Radiofarmaka & Proteksi ── */
                    {
                        id: 'm_ki',
                        name: 'KI Tiroid',
                        isTemplate: true,
                        latex: '\\text{KI}',
                        display: '\\text{KI}',
                        desc: 'Kalium iodida (proteksi tiroid)'
                    },
                    {
                        id: 'm_kio3',
                        name: 'KIO\u2083',
                        isTemplate: true,
                        latex: '\\text{KIO}_3',
                        display: '\\text{KIO}_3',
                        desc: 'Kalium iodat (proteksi tiroid)'
                    },
                    {
                        id: 'm_dtpa',
                        name: 'Na-DTPA',
                        isTemplate: true,
                        latex: '\\text{Na}_5[\\text{DTPA}]',
                        display: '\\text{Na}_5[\\text{DTPA}]',
                        desc: 'Natrium DTPA (dekontaminasi internal)'
                    },
                    {
                        id: 'm_edta',
                        name: 'Na\u2082-EDTA',
                        isTemplate: true,
                        latex: '\\text{Na}_2[\\text{EDTA}]',
                        display: '\\text{Na}_2[\\text{EDTA}]',
                        desc: 'Dinatrium EDTA (chelating agent)'
                    },
                    {
                        id: 'm_fdg',
                        name: '\u00b9\u2078F-FDG',
                        isTemplate: true,
                        latex: '[^{18}\\text{F}]\\text{FDG}',
                        display: '[{}^{18}\\text{F}]\\text{FDG}',
                        desc: 'Fluorodeoksiglukosa (PET scan)'
                    },
                    {
                        id: 'm_glucose',
                        name: 'C\u2086H\u2081\u2082O\u2086',
                        isTemplate: true,
                        latex: '\\text{C}_6\\text{H}_{12}\\text{O}_6',
                        display: '\\text{C}_6\\text{H}_{12}\\text{O}_6',
                        desc: 'Glukosa'
                    },
                    /* ── Ion Penting ── */
                    {
                        id: 'm_hplus',
                        name: 'H\u207a Ion',
                        isTemplate: true,
                        latex: '\\text{H}^{+}',
                        display: '\\text{H}^{+}',
                        desc: 'Ion hidrogen'
                    },
                    {
                        id: 'm_ohminus',
                        name: 'OH\u207b Ion',
                        isTemplate: true,
                        latex: '\\text{OH}^{-}',
                        display: '\\text{OH}^{-}',
                        desc: 'Ion hidroksida'
                    },
                    {
                        id: 'm_naplus',
                        name: 'Na\u207a',
                        isTemplate: true,
                        latex: '\\text{Na}^{+}',
                        display: '\\text{Na}^{+}',
                        desc: 'Ion natrium'
                    },
                    {
                        id: 'm_clminus',
                        name: 'Cl\u207b',
                        isTemplate: true,
                        latex: '\\text{Cl}^{-}',
                        display: '\\text{Cl}^{-}',
                        desc: 'Ion klorida'
                    },
                    {
                        id: 'm_ca2p',
                        name: 'Ca\u00b2\u207a',
                        isTemplate: true,
                        latex: '\\text{Ca}^{2+}',
                        display: '\\text{Ca}^{2+}',
                        desc: 'Ion kalsium'
                    },
                    {
                        id: 'm_fe3p',
                        name: 'Fe\u00b3\u207a',
                        isTemplate: true,
                        latex: '\\text{Fe}^{3+}',
                        display: '\\text{Fe}^{3+}',
                        desc: 'Ion besi(III)'
                    },
                    {
                        id: 'm_uo2ion',
                        name: 'UO\u2082\u00b2\u207a Uranil',
                        isTemplate: true,
                        latex: '\\text{UO}_2^{2+}',
                        display: '\\text{UO}_2^{2+}',
                        desc: 'Ion uranil'
                    },
                    {
                        id: 'm_ion_tpl',
                        name: 'X\u207f\u00b1 Ion Umum',
                        isTemplate: true,
                        latex: '\\text{X}^{n\\pm}',
                        display: '\\text{X}^{n\\pm}',
                        desc: 'Template ion umum'
                    },
                    /* ── Reaksi Kimia ── */
                    {
                        id: 'm_rxn',
                        name: 'Reaksi Umum',
                        isTemplate: true,
                        latex: '\\text{A} + \\text{B} \\rightarrow \\text{C} + \\text{D}',
                        display: '\\text{A}+\\text{B}\\rightarrow\\text{C}',
                        desc: 'Template reaksi kimia umum'
                    },
                    {
                        id: 'm_equil',
                        name: 'Kesetimbangan',
                        isTemplate: true,
                        latex: '\\text{A} + \\text{B} \\rightleftharpoons \\text{C} + \\text{D}',
                        display: '\\text{A}\\rightleftharpoons\\text{C}',
                        desc: 'Reaksi reversibel / kesetimbangan'
                    },
                    {
                        id: 'm_neutr',
                        name: 'Netralisasi',
                        isTemplate: true,
                        latex: '\\text{HCl} + \\text{NaOH} \\rightarrow \\text{NaCl} + \\text{H}_2\\text{O}',
                        display: '\\text{HCl}+\\text{NaOH}\\rightarrow\\text{NaCl}',
                        desc: 'Reaksi netralisasi asam-basa'
                    },
                    {
                        id: 'm_elec',
                        name: 'Elektrolisis Air',
                        isTemplate: true,
                        latex: '2\\,\\text{H}_2\\text{O} \\xrightarrow{\\text{elektrolisis}} 2\\,\\text{H}_2 + \\text{O}_2',
                        display: '2\\text{H}_2\\text{O}\\rightarrow2\\text{H}_2+\\text{O}_2',
                        desc: 'Elektrolisis air'
                    },
                    {
                        id: 'm_combust',
                        name: 'Pembakaran C',
                        isTemplate: true,
                        latex: '\\text{C} + \\text{O}_2 \\rightarrow \\text{CO}_2',
                        display: '\\text{C}+\\text{O}_2\\rightarrow\\text{CO}_2',
                        desc: 'Pembakaran karbon sempurna'
                    },
                ],
            },

            /* ────────────────────────────────────────────────
               COMPUTED
               ──────────────────────────────────────────────── */
            get currentSymbols() {
                return this.symbols[this.activeTab] || [];
            },

            get activeTabNote() {
                const t = this.tabs.find(t => t.id === this.activeTab);
                return t ? t.note : '';
            },

            /* ────────────────────────────────────────────────
               LIFECYCLE
               ──────────────────────────────────────────────── */
            init() {
                /* Reset modal setiap kali Livewire me-render ulang komponen ini.
                   Tanpa ini, Livewire morphing mempertahankan showEditor:true
                   sehingga modal tetap terbuka setelah refresh / navigasi. */
                const resetModal = () => {
                    this.showEditor = false;
                };

                // Livewire v3
                if (typeof Livewire !== 'undefined') {
                    Livewire.hook('morph.updated', resetModal);
                    Livewire.hook('commit', ({
                        component,
                        succeed
                    }) => {
                        succeed(() => {
                            if (this.$el && !document.contains(this.$el)) return;
                        });
                    });
                }

                // Livewire v2 fallback
                this.$el.addEventListener('livewire:update', resetModal);

                // Reset saat navigasi Filament (misalnya pindah resource)
                document.addEventListener('livewire:navigated', resetModal);
            },

            /* ────────────────────────────────────────────────
               KATEX RENDER HELPER
               ──────────────────────────────────────────────── */
            /**
             * Render satu string LaTeX ke HTML via KaTeX.
             * @param {string} latex   - raw LaTeX (tanpa \( \) pembungkus)
             * @param {boolean} block  - true → displayMode (untuk template besar)
             */
            kr(latex, block = false) {
                if (typeof katex === 'undefined') {
                    return `<code style="font-size:11px">${latex}</code>`;
                }
                /* Hapus pembungkus \( \) atau \[ \] jika ada */
                const inner = latex.trim()
                    .replace(/^\\\(/, '').replace(/\\\)$/, '')
                    .replace(/^\\\[/, '').replace(/\\\]$/, '')
                    .trim();
                try {
                    return katex.renderToString(inner, {
                        throwOnError: false,
                        displayMode: block,
                        output: 'html',
                    });
                } catch (e) {
                    return `<code style="font-size:10px">${inner}</code>`;
                }
            },

            /* ────────────────────────────────────────────────
               PREVIEW LIVE
               ──────────────────────────────────────────────── */
            updatePreview() {
                if (!this.latexInput.trim()) {
                    this.previewHtml = '';
                    this.previewError = '';
                    return;
                }
                /* Coba render dengan throwOnError untuk deteksi masalah */
                if (typeof katex !== 'undefined') {
                    try {
                        this.previewHtml = katex.renderToString(this.latexInput.trim(), {
                            throwOnError: true,
                            displayMode: this.displayMode === 'display',
                            output: 'html',
                        });
                        this.previewError = '';
                    } catch (e) {
                        this.previewError = '⚠️ Sintaks LaTeX tidak valid: ' + e.message.split(
                            '\n')[0];
                        /* Tetap tampilkan render parsial */
                        this.previewHtml = katex.renderToString(this.latexInput.trim(), {
                            throwOnError: false,
                            displayMode: this.displayMode === 'display',
                            output: 'html',
                        });
                    }
                } else {
                    this.previewHtml = `<code>${this.latexInput}</code>`;
                    this.previewError = '';
                }
            },

            /* ────────────────────────────────────────────────
               MANIPULASI INPUT
               ──────────────────────────────────────────────── */
            /**
             * Sisipkan string `text` di posisi kursor textarea.
             */
            /* ── undo / redo helpers ── */
            get canUndo() {
                return this._historyIndex > 0;
            },
            get canRedo() {
                return this._historyIndex < this._history.length - 1;
            },

            pushHistory() {
                /* truncate redo branch */
                this._history = this._history.slice(0, this._historyIndex + 1);
                /* avoid duplicate consecutive entries */
                if (this._history[this._historyIndex] === this.latexInput) return;
                this._history.push(this.latexInput);
                if (this._history.length > 120) {
                    this._history.shift();
                } else {
                    this._historyIndex++;
                }
            },

            undo() {
                if (this._historyIndex > 0) {
                    /* save current state if it's a new unsaved change */
                    if (this._history[this._historyIndex] !== this.latexInput) {
                        this.pushHistory();
                    }
                    this._historyIndex--;
                    this.latexInput = this._history[this._historyIndex];
                    this.$nextTick(() => {
                        this.updatePreview();
                        this.autoResize();
                    });
                }
            },

            redo() {
                if (this._historyIndex < this._history.length - 1) {
                    this._historyIndex++;
                    this.latexInput = this._history[this._historyIndex];
                    this.$nextTick(() => {
                        this.updatePreview();
                        this.autoResize();
                    });
                }
            },

            /* ── textarea auto-resize ── */
            autoResize() {
                const el = this.$refs.latexInput;
                if (!el) return;
                el.style.height = 'auto';
                const max = 220;
                el.style.height = Math.min(el.scrollHeight, max) + 'px';
                el.style.overflowY = el.scrollHeight > max ? 'auto' : 'hidden';
            },

            /* ── handler @input textarea ── */
            onInput() {
                this.updatePreview();
                this.autoResize();
                /* debounced history push after typing pauses */
                clearTimeout(this._historyTimer);
                this._historyTimer = setTimeout(() => this.pushHistory(), 600);
            },

            insertAt(text) {
                const el = this.$refs.latexInput;
                this.pushHistory(); /* snapshot sebelum insert */
                if (!el) {
                    this.latexInput += text;
                    this.updatePreview();
                    return;
                }

                const s = el.selectionStart;
                const e_ = el.selectionEnd;
                this.latexInput = this.latexInput.substring(0, s) + text +
                    this.latexInput.substring(e_);
                this.$nextTick(() => {
                    const pos = s + text.length;
                    el.setSelectionRange(pos, pos);
                    el.focus();
                    this.updatePreview();
                    this.autoResize();
                });
            },

            insertSym(sym) {
                this.insertAt(sym.latex);
            },

            onTab() {
                this.insertAt('  ');
            },

            clearInput() {
                this.pushHistory(); /* snapshot sebelum clear */
                this.latexInput = '';
                this.previewHtml = '';
                this.previewError = '';
                this.$nextTick(() => {
                    this.$refs.latexInput && this.$refs.latexInput.focus();
                    this.autoResize();
                });
            },

            setMode(mode) {
                this.displayMode = mode;
                this.updatePreview();
            },

            /* ────────────────────────────────────────────────
               SALIN KE CLIPBOARD
               ──────────────────────────────────────────────── */
            wrap(latex) {
                const s = latex.trim();
                return this.displayMode === 'display' ?
                    `\\[ ${s} \\]` :
                    `\\( ${s} \\)`;
            },

            copyFormula() {
                if (!this.latexInput.trim()) return;
                this.doCopy(this.wrap(this.latexInput));
            },

            quickCopy(chip) {
                this.doCopy(`\\( ${chip.latex} \\)`);
            },

            doCopy(text) {
                const onOk = () => {
                    this.copyLabel = @js(__('✅ Copied!'));
                    setTimeout(() => {
                        this.copyLabel = '📋 ' + @js(__('Copy Formula'));
                    }, 2200);
                };
                const onFail = () => {
                    this.copyLabel = @js(__('❌ Failed to copy'));
                    setTimeout(() => {
                        this.copyLabel = '📋 ' + @js(__('Copy Formula'));
                    }, 2200);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(onOk).catch(() => this.legacyCopy(text,
                        onOk, onFail));
                } else {
                    this.legacyCopy(text, onOk, onFail);
                }
            },

            legacyCopy(text, onOk, onFail) {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.cssText = 'position:fixed;left:-9999px;top:0;opacity:0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy') ? onOk() : onFail();
                } catch (_) {
                    onFail();
                }
                document.body.removeChild(ta);
            },

            /* ────────────────────────────────────────────────
               BUKA / TUTUP MODAL
               ──────────────────────────────────────────────── */
            openEditor() {
                this.showEditor = true;
                this.$nextTick(() => {
                    this.$refs.latexInput && this.$refs.latexInput.focus();
                    this.updatePreview();
                });
            },

            closeEditor() {
                this.showEditor = false;
            },

        };
    } // end mathEditorV2Factory

    // Registrasi ulang jika Alpine sudah siap
    if (window.Alpine && Alpine.data) {
        try {
            Alpine.data('mathEditorV2', mathEditorV2Factory);
        } catch (e) {}
    }
</script>


{{-- ══════════════════════════════════════════════════════════════
     CSS — semua class diawali .medt- untuk menghindari konflik
     ══════════════════════════════════════════════════════════════ --}}
<style>
    [x-cloak] {
        display: none !important;
    }

    /* ── Wrapper ── */
    .medt-wrapper {
        font-family: inherit;
        margin-bottom: .75rem;
    }

    /* ════════════════════════════════════════
   PANEL KOMPAK
   ════════════════════════════════════════ */
    .medt-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 3px solid rgb(var(--color-primary-500, 245 158 11));
        border-left: 3px solid var(--color-primary-500, #f59e0b);
        background: rgb(var(--color-primary-600, 217 119 6));
        background: var(--color-primary-600, #d97706);
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
    }

    .medt-panel-header {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
    }

    .medt-panel-icon {
        width: 32px;
        height: 32px;
        background: rgb(var(--color-primary-600, 217 119 6));
        border-color: rgb(var(--color-primary-500, 245 158 11));
        border-color: var(--color-primary-500, #f59e0b);
        background: rgba(var(--color-primary-500, 245 158 11), .08);
        color: #fff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .medt-panel-title {
        margin: 0 0 2px;
        font-weight: 700;
        font-size: 13px;
        color: #111827;
    }

    .medt-panel-desc {
        margin: 0;
        font-size: 12px;
        color: #4b5563;
    }

    /* Chip cepat */
    .medt-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .medt-chip {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 8px 10px;
        background: #fff;
        border: 1.5px solid #d1d5db;
        border-radius: 10px;
        cursor: pointer;
        transition: border-color .15s, background .15s, transform .1s, box-shadow .15s;
        min-width: 56px;
    }

    .medt-chip:hover {
        border-color: rgb(var(--color-primary-500, 245 158 11));
        background: rgba(var(--color-primary-500, 245 158 11), .08);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(var(--color-primary-500, 245 158 11), .15);
        box-shadow: 0 4px 10px color-mix(in srgb, var(--color-primary-500, #f59e0b) 15%, transparent);
        background: rgb(var(--color-primary-600, 217 119 6));
        background: var(--color-primary-600, #d97706);
    }

    .medt-chip:active {
        transform: translateY(0);
    }

    .medt-chip-math {
        font-size: 15px;
        line-height: 1.3;
        color: #111827;
    }

    .medt-chip-math .katex {
        font-size: 1em !important;
    }

    .medt-chip-name {
        font-size: 9px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .4px;
        white-space: nowrap;
    }

    /* Tombol buka editor lengkap */
    .medt-open-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgb(var(--color-primary-600, 217 119 6));
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s, box-shadow .15s, transform .1s;
        white-space: nowrap;
    }

    .medt-open-btn svg {
        width: 14px;
        height: 14px;
    }

    .medt-open-btn:hover {
        background: rgb(var(--color-primary-700, 180 83 9));
        background: var(--color-primary-700, #b45309);
        box-shadow: 0 4px 12px color-mix(in srgb, var(--color-primary-500, #f59e0b) 35%, transparent);
        background: var(--color-primary-600, #d97706);
        box-shadow: 0 4px 12px rgba(var(--color-primary-500, 245 158 11), .35);
        transform: translateY(-1px);
    }

    .medt-open-btn:active {
        transform: translateY(0);
    }

    .medt-panel-tip {
        margin: 10px 0 0;
        font-size: 11px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: wrap;
    }

    .medt-tip-badge {
        background: rgb(var(--color-primary-600, 217 119 6));
        background: var(--color-primary-600, #d97706);
        border-color: rgb(var(--color-primary-600, 217 119 6));
        border-color: var(--color-primary-600, #d97706);
        color: #fff;
        font-size: 9px;
        padding: 1px 6px;
        border-radius: 4px;
        font-weight: 700;
    }

    /* ════════════════════════════════════════
       MODAL / OVERLAY
       ════════════════════════════════════════ */
    .medt-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(3px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        padding: 16px;
    }

    .medt-dialog {
        background: #ffffff;
        border-radius: 16px;
        width: 100%;
        max-width: 920px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, .35);
        overflow: hidden;
    }

    .medt-dialog-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        background: linear-gradient(135deg, rgb(var(--color-primary-800, 146 64 14)), rgb(var(--color-primary-600, 217 119 6)));
        flex-shrink: 0;
        text-shadow: 0 1px 3px rgba(0, 0, 0, .15);
    }

    .medt-dialog-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-size: 17px;
        font-weight: 700;
    }

    .medt-dialog-icon {
        width: 34px;
        height: 34px;
        background: rgba(255, 255, 255, .2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 700;
    }

    .medt-close {
        width: 34px;
        height: 34px;
        background: rgba(255, 255, 255, .15);
        border: none;
        border-radius: 8px;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s;
        flex-shrink: 0;
    }

    .medt-close svg {
        width: 16px;
        height: 16px;
    }

    .medt-close:hover {
        background: rgba(255, 255, 255, .3);
    }

    .medt-dialog-body {
        display: flex;
        flex: 1;
        overflow: hidden;
        min-height: 0;
    }

    /* ════════════════════════════════════════
       KOLOM KIRI
       ════════════════════════════════════════ */
    .medt-sym-panel {
        width: 310px;
        flex-shrink: 0;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fafafa;
    }

    .medt-tabs {
        display: flex;
        gap: 2px;
        padding: 10px 10px 0;
        flex-wrap: wrap;
    }

    .medt-tab {
        padding: 5px 10px;
        font-size: 11.5px;
        font-weight: 600;
        border: 1.5px solid #e5e7eb;
        border-radius: 20px;
        cursor: pointer;
        background: #f9fafb;
        color: #4b5563;
        transition: all .15s;
        white-space: nowrap;
    }

    .medt-tab:hover {
        background: rgba(var(--color-primary-500, 245 158 11), .1);
        color: rgb(var(--color-primary-600, 217 119 6));
    }

    .medt-tab.is-active {
        background: rgb(var(--color-primary-600, 217 119 6));
        color: #fff;
        border-color: rgb(var(--color-primary-600, 217 119 6));
    }

    .medt-tab-note {
        margin: 6px 12px 8px;
        font-size: 10.5px;
        color: #9ca3af;
        line-height: 1.4;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 8px;
    }

    .medt-gallery {
        flex: 1;
        overflow-y: auto;
        padding: 8px 10px 12px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));
        gap: 6px;
        align-content: start;
    }

    .medt-sym-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 8px 4px;
        background: #fff;
        border: 1.5px solid #d1d5db;
        border-radius: 10px;
        cursor: pointer;
        transition: all .15s;
        text-align: center;
    }

    .medt-sym-btn:hover {
        border-color: rgb(var(--color-primary-500, 245 158 11));
        border-color: var(--color-primary-500, #f59e0b);
        background: rgba(var(--color-primary-500, 245 158 11), .08);
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 8%, transparent);
        background: rgba(var(--color-primary-500, 245 158 11), .08);
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(var(--color-primary-500, 245 158 11), .15);
        box-shadow: 0 3px 8px color-mix(in srgb, var(--color-primary-500, #f59e0b) 15%, transparent);
        border-color: rgb(var(--color-primary-500, 245 158 11));
        border-color: var(--color-primary-500, #f59e0b);
    }

    .medt-sym-btn:active {
        transform: translateY(0);
    }

    .medt-sym-btn.is-tpl {
        grid-column: span 2;
        background: #f5f3ff;
        border-color: #c4b5fd;
        flex-direction: row;
        padding: 8px 10px;
        text-align: left;
        gap: 10px;
    }

    .medt-sym-btn.is-tpl:hover {
        border-color: #7c3aed;
        background: #ede9fe;
    }

    .medt-sym-math {
        min-height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e1b4b;
        font-size: 14px;
    }

    .medt-sym-math .katex {
        font-size: .95em !important;
    }

    .medt-sym-btn.is-tpl .medt-sym-math {
        min-width: 52px;
        font-size: 12px;
        background: #fff;
        border-radius: 6px;
        padding: 3px 6px;
        flex-shrink: 0;
    }

    .medt-sym-btn.is-tpl .medt-sym-math .katex {
        font-size: .85em !important;
    }

    .medt-sym-name {
        font-size: 9.5px;
        color: #6b7280;
        text-align: center;
        line-height: 1.2;
    }

    .medt-sym-btn.is-tpl .medt-sym-name {
        font-size: 10.5px;
        color: #4c1d95;
        font-weight: 600;
        text-align: left;
        flex: 1;
    }

    /* ════════════════════════════════════════
       KOLOM KANAN
       ════════════════════════════════════════ */
    .medt-compose {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .medt-label {
        font-size: 12px;
        font-weight: 700;
        color: #374151;
        display: block;
        margin-bottom: 6px;
    }

    .medt-label-sub {
        font-weight: 400;
        color: #9ca3af;
        font-size: 11px;
    }

    .medt-compose-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .medt-btn-clear {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        cursor: pointer;
        transition: all .15s;
    }

    .medt-btn-clear svg {
        width: 13px;
        height: 13px;
    }

    .medt-btn-clear:hover {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fca5a5;
    }

    /* ── Toolbar actions (undo/redo/hapus group) ── */
    .medt-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .medt-btn-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        color: #374151;
        cursor: pointer;
        transition: all .15s;
        flex-shrink: 0;
    }

    .medt-btn-icon svg {
        width: 14px;
        height: 14px;
    }

    .medt-btn-icon:hover:not(:disabled) {
        background: rgba(var(--color-primary-500, 245 158 11), .08);
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 8%, transparent);
        color: rgb(var(--color-primary-600, 217 119 6));
        color: var(--color-primary-600, #d97706);
        border-color: rgb(var(--color-primary-500, 245 158 11));
        color: rgb(var(--color-primary-600, 217 119 6));
    }

    .medt-btn-icon:disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    .medt-textarea {
        width: 100%;
        padding: 10px 12px;
        font-family: 'Courier New', 'Consolas', monospace;
        font-size: 13px;
        line-height: 1.6;
        color: #1f2937;
        background: #f9fafb;
        border: 2px solid #d1d5db;
        border-radius: 10px;
        resize: none;
        min-height: 68px;
        max-height: 220px;
        overflow-y: hidden;
        transition: border-color .15s, box-shadow .15s;
        box-sizing: border-box;
    }

    .medt-textarea:focus {
        outline: none;
        border-color: rgb(var(--color-primary-500, 245 158 11));
        background: #fff;
        box-shadow: 0 0 0 3px rgba(var(--color-primary-500, 245 158 11), .12);
    }

    .medt-input-hint {
        margin: 4px 0 0;
        font-size: 11px;
        color: #9ca3af;
        line-height: 1.4;
    }

    .medt-input-hint code {
        background: #f3f4f6;
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 10.5px;
        color: #374151;
    }

    .medt-input-hint kbd {
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        padding: 0 4px;
        font-size: 10px;
        font-family: monospace;
        color: #374151;
    }

    .medt-preview {
        background: rgba(var(--color-primary-500, 245 158 11), .04);
        border: 2px solid rgba(var(--color-primary-500, 245 158 11), .3);
        border-radius: 10px;
        min-height: 96px;
        max-height: 180px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0;
        position: relative;
    }

    /* Gradient hint: konten bisa di-scroll ke kanan */
    .medt-preview::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 32px;
        height: 100%;
        background: linear-gradient(to right, transparent, #f0f4ff80);
        pointer-events: none;
        border-radius: 0 10px 10px 0;
        opacity: 0;
        transition: opacity .2s;
    }

    .medt-preview:has(.medt-preview-scroll):hover::after {
        opacity: 1;
    }

    .medt-preview-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        color: rgba(var(--color-primary-500, 245 158 11), .5);
        font-size: 12px;
        padding: 20px;
    }

    .medt-preview-empty-icon {
        font-size: 32px;
        opacity: .22;
        line-height: 1;
    }

    /* Wrapper scrollable — hanya area ini yang scroll */
    .medt-preview-scroll {
        width: 100%;
        max-height: 180px;
        overflow-x: auto;
        overflow-y: auto;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        /* Catatan: JANGAN pakai justify-content:center di sini.
           Flex + overflow-x:auto + justify-content:center menyebabkan
           konten yang meluap ke kanan tidak bisa di-scroll ke kiri. */
        min-height: 96px;
        /* Scrollbar tipis & elegan */
        scrollbar-width: thin;
        scrollbar-color: rgba(var(--color-primary-500, 245 158 11), .4) transparent;
    }

    .medt-preview-scroll::-webkit-scrollbar {
        height: 4px;
        width: 4px;
    }

    .medt-preview-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .medt-preview-scroll::-webkit-scrollbar-thumb {
        background: rgba(var(--color-primary-500, 245 158 11), .4);
        border-radius: 10px;
    }

    .medt-preview-scroll::-webkit-scrollbar-thumb:hover {
        background: #818cf8;
    }

    .medt-preview-render {
        font-size: 1.15em;
        color: #1e1b4b;
        flex-shrink: 0;
        /* jangan kompres formula */
        white-space: nowrap;
        margin: 0 auto;
        /* center ketika muat; auto collapse saat overflow → bisa scroll kiri */
        text-align: center;
    }

    .medt-preview-render .katex-display {
        margin: .2em 0 !important;
    }

    .medt-preview-render .katex {
        white-space: nowrap;
    }

    .medt-preview-hint {
        margin: 4px 0 10px;
        font-size: 10.5px;
        color: rgba(var(--color-primary-500, 245 158 11), .6);
        text-align: center;
    }

    .medt-preview-hint code {
        background: rgba(var(--color-primary-500, 245 158 11), .1);
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 10px;
        color: rgb(var(--color-primary-600, 217 119 6));
    }

    .medt-preview-error {
        font-size: 11px;
        color: #dc2626;
        background: #fff5f5;
        padding: 8px 12px;
        border-radius: 6px;
        word-break: break-all;
        text-align: left;
        width: calc(100% - 24px);
        margin: 12px;
        border-left: 3px solid #fca5a5;
    }

    .medt-mode-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .medt-mode-toggle {
        display: flex;
        background: #f3f4f6;
        border-radius: 8px;
        padding: 3px;
        gap: 3px;
    }

    .medt-mode-btn {
        padding: 5px 12px;
        font-size: 11.5px;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        background: transparent;
        color: #6b7280;
        transition: all .15s;
        white-space: nowrap;
    }

    .medt-mode-btn code {
        font-size: 10px;
        background: rgba(0, 0, 0, .07);
        padding: 1px 4px;
        border-radius: 3px;
    }

    .medt-mode-btn.is-active {
        background: rgb(var(--color-primary-600, 217 119 6));
        background: var(--color-primary-600, #d97706);
        box-shadow: 0 1px 4px rgba(var(--color-primary-500, 245 158 11), .3);
        box-shadow: 0 1px 4px color-mix(in srgb, var(--color-primary-500, #f59e0b) 30%, transparent);
        color: #fff;
        box-shadow: 0 1px 4px rgba(var(--color-primary-500, 245 158 11), .3);
    }

    .medt-mode-btn.is-active code {
        background: rgba(255, 255, 255, .25);
        color: #fff;
    }

    .medt-copy-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        margin-top: 14px;
        padding: 12px;
        background: rgb(var(--color-primary-600, 217 119 6));
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background .15s, box-shadow .15s, transform .1s;
    }

    .medt-copy-btn svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .medt-copy-btn:hover:not(:disabled) {
        background: rgb(var(--color-primary-700, 180 83 9));
        box-shadow: 0 4px 16px rgba(var(--color-primary-500, 245 158 11), .4);
        box-shadow: 0 4px 16px color-mix(in srgb, var(--color-primary-500, #f59e0b) 40%, transparent);
        background: rgba(var(--color-primary-500, 245 158 11), .3);
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 30%, transparent);
        transform: translateY(-1px);
    }

    .medt-copy-btn:active:not(:disabled) {
        transform: translateY(0);
    }

    .medt-copy-btn:disabled {
        background: rgba(var(--color-primary-500, 245 158 11), .3);
        cursor: not-allowed;
    }

    .medt-steps-title {
        font-size: 11.5px;
        font-weight: 700;
        color: #166534;
        margin-right: 6px;
    }

    .medt-steps-inline {
        font-size: 11.5px;
        color: #15803d;
        line-height: 1.6;
    }

    .medt-steps-inline kbd {
        background: #dcfce7;
        border: 1px solid #86efac;
        border-radius: 4px;
        padding: 1px 5px;
        font-size: 10.5px;
        font-family: monospace;
    }

    .medt-steps {
        margin-top: 12px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 10px 14px;
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 2px;
    }

    /* ════════════════════════════════════════
       RESPONSIF
       ════════════════════════════════════════ */
    @media (max-width: 720px) {
        .medt-dialog-body {
            flex-direction: column;
        }

        .medt-sym-panel {
            width: 100%;
            max-height: 250px;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }

        .medt-dialog {
            max-height: 95vh;
        }

        .medt-sym-btn.is-tpl {
            grid-column: span 2;
        }
    }

    @media (max-width: 480px) {
        .medt-chip-row {
            gap: 6px;
        }

        .medt-chip {
            min-width: 48px;
            padding: 6px 8px;
        }

        .medt-chip-name {
            font-size: 8px;
        }
    }

    /* ════════════════════════════════════════
       DARK MODE
       ════════════════════════════════════════ */
    .dark .medt-panel {
        background: #242427 !important;
        border-color: #374151;
        border-left-color: rgb(var(--color-primary-500, 245 158 11));
        border-left-color: var(--color-primary-500, #f59e0b);
        border-color: rgba(var(--color-primary-500, 245 158 11), .25);
        border-color: color-mix(in srgb, var(--color-primary-500, #f59e0b) 25%, transparent);
        box-shadow: 0 1px 4px rgba(0, 0, 0, .3);
    }

    .dark .medt-panel-title {
        color: #f3f4f6;
    }

    .dark .medt-panel-desc {
        color: #9ca3af;
    }

    .dark .medt-chip {
        background: #1f2937;
        border-color: rgba(var(--color-primary-500, 245 158 11), .25);
    }

    .dark .medt-chip:hover {
        background: rgba(var(--color-primary-500, 245 158 11), .12);
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 12%, transparent);
        color: rgb(var(--color-primary-400, 251 191 36));
        color: var(--color-primary-400, #fbbf24);
        border-color: rgb(var(--color-primary-500, 245 158 11));
        border-color: var(--color-primary-500, #f59e0b);
        box-shadow: 0 0 0 3px rgba(var(--color-primary-500, 245 158 11), .15);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary-500, #f59e0b) 15%, transparent);
        box-shadow: 0 4px 10px rgba(var(--color-primary-500, 245 158 11), .2);
    }

    .dark .medt-chip-math {
        color: #f3f4f6;
    }

    .dark .medt-chip-name {
        color: #9ca3af;
    }

    .dark .medt-panel-tip {
        color: #9ca3af;
    }

    .dark .medt-dialog {
        background: #1f2937;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, .7);
    }

    .dark .medt-sym-panel {
        background: #111827;
        border-right-color: #374151;
    }

    .dark .medt-tab {
        color: #9ca3af;
        background: #1f2937;
        border-color: #374151;
    }

    .dark .medt-tab:hover {
        background: rgba(var(--color-primary-500, 245 158 11), .12);
        color: rgb(var(--color-primary-400, 251 191 36));
    }

    .dark .medt-tab.is-active {
        background: rgb(var(--color-primary-600, 217 119 6));
        color: #fff;
        border-color: rgb(var(--color-primary-600, 217 119 6));
    }

    .dark .medt-tab-note {
        color: #6b7280;
        border-bottom-color: #374151;
    }

    .dark .medt-sym-btn {
        background: #1f2937;
        border-color: #374151;
    }

    .dark .medt-sym-btn:hover {
        background: rgba(var(--color-primary-500, 245 158 11), .1);
        border-color: rgb(var(--color-primary-500, 245 158 11));
        box-shadow: 0 3px 8px rgba(var(--color-primary-500, 245 158 11), .2);
    }

    .dark .medt-sym-math {
        color: #e5e7eb;
    }

    .dark .medt-sym-name {
        color: #9ca3af;
    }

    .dark .medt-label {
        color: #d1d5db;
    }

    .dark .medt-label-sub {
        color: #6b7280;
    }

    .dark .medt-btn-icon {
        background: #374151;
        border-color: #4b5563;
        color: #d1d5db;
    }

    .dark .medt-btn-icon:hover:not(:disabled) {
        background: rgba(var(--color-primary-500, 245 158 11), .12);
        border-color: rgb(var(--color-primary-500, 245 158 11));
        color: rgb(var(--color-primary-400, 251 191 36));
    }

    .dark .medt-btn-clear {
        background: #374151;
        border-color: #4b5563;
        color: #9ca3af;
    }

    .dark .medt-btn-clear:hover {
        background: #450a0a;
        color: #fca5a5;
        border-color: #7f1d1d;
    }

    .dark .medt-compose {
        background: #1f2937;
    }

    .dark .medt-textarea {
        background: #111827;
        border-color: #374151;
        color: #e5e7eb;
    }

    .dark .medt-textarea:focus {
        background: #1f2937;
        border-color: rgb(var(--color-primary-500, 245 158 11));
        box-shadow: 0 0 0 3px rgba(var(--color-primary-500, 245 158 11), .15);
    }

    .dark .medt-input-hint {
        color: #6b7280;
    }

    .dark .medt-input-hint code,
    .dark .medt-input-hint kbd {
        background: #374151;
        color: #d1d5db;
        border-color: #4b5563;
    }

    .dark .medt-preview {
        background: rgba(var(--color-primary-500, 245 158 11), .05);
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 5%, transparent);
        border-color: rgba(var(--color-primary-500, 245 158 11), .25);
        border-color: color-mix(in srgb, var(--color-primary-500, #f59e0b) 25%, transparent);
        border-color: rgba(var(--color-primary-500, 245 158 11), .25);
    }

    .dark .medt-preview-empty {
        color: rgba(var(--color-primary-500, 245 158 11), .4);
        color: color-mix(in srgb, var(--color-primary-500, #f59e0b) 40%, transparent);
        color: rgba(var(--color-primary-500, 245 158 11), .6);
        color: color-mix(in srgb, var(--color-primary-500, #f59e0b) 60%, transparent);
    }

    .dark .medt-preview-render {
        color: #e5e7eb;
    }

    .dark .medt-preview-hint {
        color: rgba(var(--color-primary-500, 245 158 11), .6);
    }

    .dark .medt-preview-hint code {
        background: rgba(var(--color-primary-500, 245 158 11), .12);
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 12%, transparent);
        color: rgb(var(--color-primary-400, 251 191 36));
        color: var(--color-primary-400, #fbbf24);
        color: rgb(var(--color-primary-400, 251 191 36));
    }

    .dark .medt-mode-toggle {
        background: #374151;
    }

    .dark .medt-mode-btn {
        color: #9ca3af;
    }

    .dark .medt-mode-btn.is-active {
        background: rgb(var(--color-primary-600, 217 119 6));
        color: #fff;
    }

    .dark .medt-copy-btn:disabled {
        background: rgba(var(--color-primary-500, 245 158 11), .2);
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 20%, transparent);
    }

    .dark .medt-steps {
        background: #052e16;
        border-color: #166534;
    }

    /* ════════════════════════════════════════
       FINAL TOKEN-COMPAT OVERRIDES
       memastikan konsisten dengan var(--color-primary-*) Filament
       ════════════════════════════════════════ */
    .medt-panel {
        background: #fff !important;
        border: 1px solid #e5e7eb !important;
        border-left: 3px solid var(--color-primary-500, #f59e0b) !important;
    }

    .medt-panel-icon,
    .medt-open-btn,
    .medt-tip-badge,
    .medt-tab.is-active,
    .medt-mode-btn.is-active,
    .medt-copy-btn {
        background: var(--color-primary-600, #d97706) !important;
    }

    .medt-open-btn:hover,
    .medt-copy-btn:hover:not(:disabled) {
        background: var(--color-primary-700, #b45309) !important;
    }

    .medt-chip:hover,
    .medt-sym-btn:hover,
    .medt-tab:hover,
    .medt-btn-icon:hover:not(:disabled) {
        border-color: var(--color-primary-500, #f59e0b) !important;
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 8%, transparent) !important;
        color: var(--color-primary-600, #d97706) !important;
    }

    .medt-textarea:focus {
        border-color: var(--color-primary-500, #f59e0b) !important;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary-500, #f59e0b) 12%, transparent) !important;
    }

    .medt-preview {
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 4%, transparent) !important;
        border-color: color-mix(in srgb, var(--color-primary-500, #f59e0b) 30%, transparent) !important;
    }

    .dark .medt-panel {
        background: #242427 !important;
        border-color: #374151 !important;
        border-left-color: var(--color-primary-500, #f59e0b) !important;
    }

    .dark .medt-panel-title,
    .dark .medt-chip-math {
        color: #f3f4f6 !important;
    }

    .dark .medt-panel-desc,
    .dark .medt-chip-name,
    .dark .medt-panel-tip,
    .dark .medt-tab,
    .dark .medt-mode-btn {
        color: #9ca3af !important;
    }

    .dark .medt-chip,
    .dark .medt-sym-btn,
    .dark .medt-tab {
        background: #18181b !important;
        border-color: #374151 !important;
    }

    .dark .medt-chip:hover,
    .dark .medt-sym-btn:hover,
    .dark .medt-tab:hover,
    .dark .medt-btn-icon:hover:not(:disabled) {
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 12%, transparent) !important;
        border-color: var(--color-primary-500, #f59e0b) !important;
        color: var(--color-primary-400, #fbbf24) !important;
    }

    .dark .medt-tab.is-active,
    .dark .medt-mode-btn.is-active,
    .dark .medt-open-btn,
    .dark .medt-copy-btn,
    .dark .medt-tip-badge,
    .dark .medt-panel-icon {
        background: var(--color-primary-600, #d97706) !important;
        color: #fff !important;
    }

    .dark .medt-open-btn:hover,
    .dark .medt-copy-btn:hover:not(:disabled) {
        background: var(--color-primary-700, #b45309) !important;
    }

    .dark .medt-textarea:focus {
        border-color: var(--color-primary-500, #f59e0b) !important;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary-500, #f59e0b) 15%, transparent) !important;
    }

    .dark .medt-preview {
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 6%, transparent) !important;
        border-color: color-mix(in srgb, var(--color-primary-500, #f59e0b) 25%, transparent) !important;
    }

    /* ════════════════════════════════════════
       FULL EDITOR MODAL POLISH
       ════════════════════════════════════════ */
    .medt-dialog {
        border: 1px solid #e5e7eb !important;
        background: #ffffff !important;
        box-shadow: 0 24px 50px -16px rgba(15, 23, 42, .35) !important;
    }

    .medt-dialog-head {
        background: linear-gradient(135deg, var(--color-primary-700, #b45309), var(--color-primary-600, #d97706)) !important;
        border-bottom: 1px solid color-mix(in srgb, var(--color-primary-500, #f59e0b) 40%, transparent) !important;
    }

    .medt-dialog-title {
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    .medt-dialog-icon {
        background: rgba(255, 255, 255, .2) !important;
        color: #ffffff !important;
    }

    .medt-close {
        background: rgba(255, 255, 255, .14) !important;
        border: 1px solid rgba(255, 255, 255, .25) !important;
        color: #ffffff !important;
    }

    .medt-close:hover {
        background: rgba(255, 255, 255, .25) !important;
    }

    .medt-sym-panel {
        background: #f8fafc !important;
        border-right-color: #e5e7eb !important;
    }

    .medt-compose {
        background: #ffffff !important;
    }

    .medt-label {
        color: #1f2937 !important;
    }

    .medt-label-sub,
    .medt-input-hint {
        color: #6b7280 !important;
    }

    .medt-textarea {
        background: #f9fafb !important;
        border-color: #cbd5e1 !important;
        color: #111827 !important;
    }

    .medt-btn-icon,
    .medt-btn-clear {
        background: #f8fafc !important;
        border-color: #d1d5db !important;
        color: #4b5563 !important;
    }

    .medt-preview {
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 5%, #ffffff) !important;
        border-color: color-mix(in srgb, var(--color-primary-500, #f59e0b) 28%, #e5e7eb) !important;
    }

    .medt-mode-toggle {
        background: #f3f4f6 !important;
        border: 1px solid #e5e7eb !important;
    }

    .medt-mode-btn {
        color: #4b5563 !important;
    }

    .dark .medt-dialog {
        background: #111827 !important;
        border-color: #374151 !important;
        box-shadow: 0 26px 56px -18px rgba(0, 0, 0, .75) !important;
    }

    .dark .medt-dialog-head {
        background: linear-gradient(135deg, var(--color-primary-900, #78350f), var(--color-primary-700, #b45309)) !important;
        border-bottom-color: color-mix(in srgb, var(--color-primary-500, #f59e0b) 35%, #111827) !important;
    }

    .dark .medt-sym-panel {
        background: #0a121e !important;
        border-right-color: #334155 !important;
    }

    .dark .medt-compose {
        background: #0a121e !important;
    }

    .dark .medt-label {
        color: #e5e7eb !important;
    }

    .dark .medt-label-sub,
    .dark .medt-input-hint {
        color: #9ca3af !important;
    }

    .dark .medt-textarea {
        background: #0b1220 !important;
        border-color: #334155 !important;
        color: #f3f4f6 !important;
    }

    .dark .medt-btn-icon,
    .dark .medt-btn-clear {
        background: #1f2937 !important;
        border-color: #374151 !important;
        color: #d1d5db !important;
    }

    .dark .medt-preview {
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 7%, #111827) !important;
        border-color: color-mix(in srgb, var(--color-primary-500, #f59e0b) 22%, #374151) !important;
    }

    .dark .medt-mode-toggle {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }

    /* Dark mode readability fix for left menu tabs */
    .dark .medt-tab {
        background: #1f2937 !important;
        border-color: #334155 !important;
        color: #e5e7eb !important;
    }

    .dark .medt-tab:hover {
        background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 18%, #1f2937) !important;
        border-color: var(--color-primary-400, #fbbf24) !important;
        color: #ffffff !important;
    }

    .dark .medt-tab.is-active {
        background: var(--color-primary-500, #f59e0b) !important;
        border-color: var(--color-primary-400, #fbbf24) !important;
        color: #ffffff !important;
        box-shadow: 0 0 0 1px color-mix(in srgb, var(--color-primary-500, #f59e0b) 45%, transparent) inset !important;
    }

    .dark .medt-tab-note {
        color: #cbd5e1 !important;
    }

    /* Dark mode readability fix for template items in symbol list */
    .dark .medt-sym-btn.is-tpl {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    .dark .medt-sym-btn.is-tpl .medt-sym-math {
        background: #0f172a !important;
        color: #f8fafc !important;
    }

    .dark .medt-sym-btn.is-tpl .medt-sym-name {
        color: #e2e8f0 !important;
        font-weight: 700 !important;
    }

    .dark .medt-sym-btn.is-tpl:hover .medt-sym-name {
        color: #ffffff !important;
    }
</style>
