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
                <p class="medt-panel-title">Bantuan Rumus Matematika</p>
                <p class="medt-panel-desc">
                    Klik rumus di bawah untuk langsung menyalin, atau buka
                    <strong>Editor Lengkap</strong> untuk menyusun rumus sendiri.
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
                Editor Lengkap
            </button>
        </div>

        <p class="medt-panel-tip">
            <span class="medt-tip-badge">INFO</span>&nbsp;
            Klik chip → salin otomatis → <kbd>Ctrl+V</kbd> di editor teks.
            Format: <code>\( rumus \)</code>
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
                    Editor Rumus Matematika
                </div>
                <button type="button" class="medt-close" @click.stop="closeEditor()" title="Tutup (Esc)">
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
                    <div class="medt-compose-row">
                        <label class="medt-label">✏️ Kode LaTeX <span class="medt-label-sub">(bisa diketik atau diklik
                                dari panel kiri)</span></label>
                        <button type="button" class="medt-btn-clear" @click.prevent="clearInput()" title="Bersihkan">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                            </svg>
                            Hapus
                        </button>
                    </div>

                    <textarea x-ref="latexInput" x-model="latexInput" @input.debounce.80ms="updatePreview()" @keydown.tab.prevent="onTab()"
                        class="medt-textarea" placeholder="Contoh: \frac{a}{b} atau klik simbol di kiri…" spellcheck="false"
                        autocomplete="off" rows="3">
                    </textarea>

                    <p class="medt-input-hint">
                        💡 <strong>Tips:</strong>
                        Klik simbol di kiri untuk menyisipkan di posisi kursor.
                        Tab = indentasi. Kursor tetap di posisi setelah menyisipkan.
                    </p>

                    {{-- Pratinjau visual (KaTeX render sesungguhnya) --}}
                    <label class="medt-label" style="margin-top:14px">🔍 Pratinjau Visual</label>

                    <div class="medt-preview">
                        {{-- State kosong --}}
                        <div x-show="!latexInput.trim()" class="medt-preview-empty">
                            <span class="medt-preview-empty-icon">∑</span>
                            <span>Pratinjau rumus muncul di sini</span>
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
                                @click.prevent="setMode('inline')" title="Sebaris — rumus menyatu dalam kalimat">
                                Sebaris&nbsp;<code>\(…\)</code>
                            </button>
                            <button type="button" class="medt-mode-btn"
                                :class="{ 'is-active': displayMode === 'display' }"
                                @click.prevent="setMode('display')"
                                title="Blok — rumus berdiri sendiri di tengah halaman">
                                Blok Tengah&nbsp;<code>\[…\]</code>
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

                    {{-- Panduan langkah --}}
                    <div class="medt-steps">
                        <p class="medt-steps-title">📋 Cara menggunakan:</p>
                        <ol class="medt-steps-list">
                            <li>Klik simbol/template di panel kiri, atau ketik LaTeX langsung.</li>
                            <li>Lihat pratinjau — pastikan rumus tampil dengan benar.</li>
                            <li>Klik <strong>Salin Rumus</strong>.</li>
                            <li>Klik di dalam editor teks soal, lalu tekan <kbd>Ctrl+V</kbd>.</li>
                        </ol>
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
            copyLabel: '📋 Salin Rumus',

            /* ────────────────────────────────────────────────
               CHIP CEPAT (panel kompak)
               ──────────────────────────────────────────────── */
            quickChips: [{
                    id: 'frac',
                    name: 'Pecahan',
                    latex: '\\frac{a}{b}'
                },
                {
                    id: 'pow',
                    name: 'Pangkat',
                    latex: 'x^{2}'
                },
                {
                    id: 'sqrt',
                    name: 'Akar',
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
                    label: '± Operator',
                    note: 'Klik simbol untuk menyisipkan ke posisi kursor.'
                },
                {
                    id: 'structure',
                    label: '∫ Struktur',
                    note: 'Template siap pakai — klik untuk menyisipkan, lalu ganti huruf placeholder.'
                },
                {
                    id: 'greek',
                    label: 'α Yunani',
                    note: 'Huruf alfabet Yunani — klik untuk menyisipkan.'
                },
                {
                    id: 'functions',
                    label: 'sin Fungsi',
                    note: 'Fungsi matematika standar — klik untuk menyisipkan.'
                },
                {
                    id: 'nuclear',
                    label: '⚛ Nuklir',
                    note: 'Rumus fisika dan keselamatan radiasi untuk konteks BAPETEN.'
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
            insertAt(text) {
                const el = this.$refs.latexInput;
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
                });
            },

            insertSym(sym) {
                this.insertAt(sym.latex);
            },

            onTab() {
                this.insertAt('  ');
            },

            clearInput() {
                this.latexInput = '';
                this.previewHtml = '';
                this.previewError = '';
                this.$nextTick(() => this.$refs.latexInput && this.$refs.latexInput.focus());
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
                    this.copyLabel = '✅ Tersalin!';
                    setTimeout(() => {
                        this.copyLabel = '📋 Salin Rumus';
                    }, 2200);
                };
                const onFail = () => {
                    this.copyLabel = '❌ Gagal menyalin';
                    setTimeout(() => {
                        this.copyLabel = '📋 Salin Rumus';
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
        background: #f8faff;
        border: 1px solid #c7d2fe;
        border-radius: 12px;
        padding: 14px 16px;
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
        background: #4f46e5;
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
        color: #1e1b4b;
    }

    .medt-panel-desc {
        margin: 0;
        font-size: 12px;
        color: #4338ca;
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
        border: 1.5px solid #c7d2fe;
        border-radius: 10px;
        cursor: pointer;
        transition: border-color .15s, background .15s, transform .1s, box-shadow .15s;
        min-width: 56px;
    }

    .medt-chip:hover {
        border-color: #4f46e5;
        background: #eef2ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(79, 70, 229, .15);
    }

    .medt-chip:active {
        transform: translateY(0);
    }

    .medt-chip-math {
        font-size: 15px;
        line-height: 1.3;
        color: #1e1b4b;
    }

    .medt-chip-math .katex {
        font-size: 1em !important;
    }

    .medt-chip-name {
        font-size: 9px;
        font-weight: 700;
        color: #4338ca;
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
        background: #4f46e5;
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
        background: #4338ca;
        box-shadow: 0 4px 12px rgba(79, 70, 229, .35);
        transform: translateY(-1px);
    }

    .medt-open-btn:active {
        transform: translateY(0);
    }

    .medt-panel-tip {
        margin: 10px 0 0;
        font-size: 11px;
        color: #6366f1;
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: wrap;
    }

    .medt-tip-badge {
        background: #4f46e5;
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
        background: linear-gradient(135deg, #312e81, #4f46e5);
        flex-shrink: 0;
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
        border: 1.5px solid transparent;
        border-radius: 20px;
        cursor: pointer;
        background: transparent;
        color: #6b7280;
        transition: all .15s;
        white-space: nowrap;
    }

    .medt-tab:hover {
        background: #ede9fe;
        color: #4f46e5;
    }

    .medt-tab.is-active {
        background: #4f46e5;
        color: #fff;
        border-color: #4f46e5;
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
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        transition: all .15s;
        text-align: center;
    }

    .medt-sym-btn:hover {
        border-color: #4f46e5;
        background: #eef2ff;
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(79, 70, 229, .15);
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
        resize: vertical;
        transition: border-color .15s;
        box-sizing: border-box;
    }

    .medt-textarea:focus {
        outline: none;
        border-color: #4f46e5;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
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

    .medt-preview {
        background: #f8faff;
        border: 2px solid #c7d2fe;
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
        color: #a5b4fc;
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
        justify-content: center;
        min-height: 96px;
        /* Scrollbar tipis & elegan */
        scrollbar-width: thin;
        scrollbar-color: #c7d2fe transparent;
    }

    .medt-preview-scroll::-webkit-scrollbar {
        height: 4px;
        width: 4px;
    }

    .medt-preview-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .medt-preview-scroll::-webkit-scrollbar-thumb {
        background: #c7d2fe;
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
        color: #a5b4fc;
        text-align: center;
    }

    .medt-preview-hint code {
        background: #eef2ff;
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 10px;
        color: #4f46e5;
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
        background: #4f46e5;
        color: #fff;
        box-shadow: 0 1px 4px rgba(79, 70, 229, .3);
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
        background: #4f46e5;
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
        background: #4338ca;
        box-shadow: 0 4px 16px rgba(79, 70, 229, .4);
        transform: translateY(-1px);
    }

    .medt-copy-btn:active:not(:disabled) {
        transform: translateY(0);
    }

    .medt-copy-btn:disabled {
        background: #c7d2fe;
        cursor: not-allowed;
    }

    .medt-steps {
        margin-top: 14px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 12px 14px;
    }

    .medt-steps-title {
        margin: 0 0 6px;
        font-size: 12px;
        font-weight: 700;
        color: #166534;
    }

    .medt-steps-list {
        margin: 0;
        padding-left: 18px;
        font-size: 11.5px;
        color: #15803d;
        line-height: 1.8;
    }

    .medt-steps-list kbd {
        background: #dcfce7;
        border: 1px solid #86efac;
        border-radius: 4px;
        padding: 1px 5px;
        font-size: 10.5px;
        font-family: monospace;
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
</style>
