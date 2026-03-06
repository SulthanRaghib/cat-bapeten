{{-- ===================================================================
     Komponen Pratinjau Soal Langsung (Live Question Preview)
     Teknologi : Alpine.js 3 + $wire (Livewire 3) — tidak perlu server call
     Cara kerja: polling $wire.data setiap 400ms setelah perubahan terdeteksi
     ================================================================== --}}

@php
    use App\Models\ExamType;
    // Embed exam type → method map so Alpine can resolve labels client-side
    $examTypeMap = ExamType::pluck('evaluation_method', 'id')->toArray();
@endphp

@once
    <style>
        /* ══ Root ══════════════════════════════════════════════════════════════ */
        .qp-root {
            font-family: inherit;
        }

        /* ══ Empty state ═══════════════════════════════════════════════════════ */
        .qp-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            text-align: center;
            border: 2px dashed #e2e8f0;
            border-radius: 1rem;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }

        .dark .qp-empty {
            border-color: #334155;
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }

        .qp-empty-icon {
            width: 3rem;
            height: 3rem;
            margin-bottom: 1rem;
            color: #94a3b8;
            opacity: 0.6;
        }

        .qp-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.375rem;
        }

        .dark .qp-empty-title {
            color: #94a3b8;
        }

        .qp-empty-sub {
            font-size: 0.8125rem;
            color: #94a3b8;
            line-height: 1.5;
            max-width: 24rem;
        }

        .dark .qp-empty-sub {
            color: #64748b;
        }

        /* ══ Preview card ══════════════════════════════════════════════════════ */
        .qp-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        }

        .dark .qp-card {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .3);
        }

        /* ══ Card header ═══════════════════════════════════════════════════════ */
        .qp-header {
            background: linear-gradient(135deg, #f59e0b15 0%, #fbbf2415 100%);
            border-bottom: 1px solid #fde68a;
            padding: 0.875rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dark .qp-header {
            background: linear-gradient(135deg, #78350f20 0%, #92400e20 100%);
            border-bottom-color: #78350f;
        }

        .qp-header-icon {
            flex-shrink: 0;
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 2px 6px rgba(245, 158, 11, .35);
        }

        .qp-header-meta {
            flex: 1;
        }

        .qp-header-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #92400e;
        }

        .dark .qp-header-title {
            color: #fcd34d;
        }

        .qp-header-sub {
            font-size: 0.7rem;
            color: #b45309;
            margin-top: 1px;
        }

        .dark .qp-header-sub {
            color: #d97706;
        }

        /* ══ Live pulse indicator ════════════════════════════════════════════ */
        .qp-live {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.2rem 0.625rem;
            border-radius: 9999px;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .dark .qp-live {
            background: #052e16;
            color: #4ade80;
            border-color: #166534;
        }

        .qp-live-dot {
            width: 0.4375rem;
            height: 0.4375rem;
            border-radius: 50%;
            background: #22c55e;
            animation: qp-pulse 1.6s ease-in-out infinite;
        }

        @keyframes qp-pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.45;
                transform: scale(0.75);
            }
        }

        /* ══ Meta strip (badges row) ═══════════════════════════════════════════ */
        .qp-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1.25rem;
            background: #fffbeb;
            border-bottom: 1px solid #fef3c7;
            min-height: 2.25rem;
        }

        .dark .qp-meta {
            background: #1e293b;
            border-bottom-color: #334155;
        }

        .qp-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            border: 1px solid transparent;
        }

        .qp-badge-easy {
            background: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .qp-badge-medium {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }

        .qp-badge-hard {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .qp-badge-teknis {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .qp-badge-mansoskul {
            background: #f5f3ff;
            color: #6d28d9;
            border-color: #ddd6fe;
        }

        /* ══ Body ══════════════════════════════════════════════════════════════ */
        .qp-body {
            padding: 1.125rem 1.25rem 1.25rem;
        }

        .qp-section-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-bottom: 0.65rem;
            user-select: none;
        }

        .qp-dot {
            width: 0.4375rem;
            height: 0.4375rem;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ══ Question content ══════════════════════════════════════════════════ */
        .qp-question-text {
            font-size: 0.9375rem;
            line-height: 1.75;
            color: #1e293b;
            margin-bottom: 1.25rem;
        }

        .dark .qp-question-text {
            color: #e2e8f0;
        }

        .qp-question-text img,
        .qp-option-body img {
            max-width: 100%;
            border-radius: 0.5rem;
            margin: 0.5rem 0;
        }

        .qp-question-text p:last-child,
        .qp-option-body p:last-child {
            margin-bottom: 0;
        }

        /* ══ Option cards ══════════════════════════════════════════════════════ */
        .qp-option {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.7rem 0.875rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.625rem;
            margin-bottom: 0.5rem;
            background: #ffffff;
            transition: border-color .15s, background .15s;
        }

        .dark .qp-option {
            background: #0f172a;
            border-color: #1e293b;
        }

        .qp-option.correct {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-color: #86efac;
        }

        .dark .qp-option.correct {
            background: #052e16;
            border-color: #166534;
        }

        .qp-option.weighted-has-score {
            background: linear-gradient(135deg, #faf5ff, #ede9fe);
            border-color: #c4b5fd;
        }

        .dark .qp-option.weighted-has-score {
            background: #1e1b4b;
            border-color: #4c1d95;
        }

        .qp-letter {
            flex-shrink: 0;
            width: 1.875rem;
            height: 1.875rem;
            border-radius: 0.4375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.875rem;
            background: #f1f5f9;
            color: #475569;
            border: 1.5px solid #e2e8f0;
            transition: background .15s, color .15s;
        }

        .dark .qp-letter {
            background: #1e293b;
            color: #94a3b8;
            border-color: #334155;
        }

        .correct .qp-letter {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 2px 8px rgba(34, 197, 94, .35);
        }

        .weighted-has-score .qp-letter {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 2px 8px rgba(124, 58, 237, .35);
        }

        .qp-option-content {
            flex: 1;
            min-width: 0;
        }

        .qp-option-body {
            font-size: 0.875rem;
            line-height: 1.6;
            color: #334155;
        }

        .dark .qp-option-body {
            color: #cbd5e1;
        }

        .qp-option-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            margin-top: 0.3rem;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
        }

        .qp-tag-correct {
            background: #dcfce7;
            color: #15803d;
        }

        .qp-tag-score {
            background: #ede9fe;
            color: #6d28d9;
        }

        .dark .qp-tag-correct {
            background: #052e16;
            color: #4ade80;
        }

        .dark .qp-tag-score {
            background: #1e1b4b;
            color: #a78bfa;
        }

        /* ══ Empty options placeholder ═════════════════════════════════════════ */
        .qp-no-options {
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1.5px dashed #e2e8f0;
            text-align: center;
            font-size: 0.8125rem;
            color: #94a3b8;
        }

        .dark .qp-no-options {
            border-color: #334155;
            color: #64748b;
        }

        /* ══ Overflow badge ════════════════════════════════════════════════════ */
        .qp-more {
            padding: 0.5rem 0.875rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 0.75rem;
            color: #64748b;
            text-align: center;
            margin-top: 0.25rem;
        }

        .dark .qp-more {
            border-color: #334155;
            background: #0f172a;
            color: #475569;
        }
    </style>
@endonce

<div x-data="questionPreview(@js($examTypeMap))" x-init="init()" class="qp-root">
    {{-- ── Empty state ─────────────────────────────────────────────────── --}}
    <template x-if="!hasContent">
        <div class="qp-empty">
            <svg class="qp-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            <p class="qp-empty-title">Pratinjau akan muncul di sini</p>
            <p class="qp-empty-sub">
                Mulai ketik teks pertanyaan di bagian <strong>Isi Soal &amp; Pembahasan</strong>
                untuk melihat tampilan soal secara langsung seperti yang dilihat peserta.
            </p>
        </div>
    </template>

    {{-- ── Live preview card ───────────────────────────────────────────── --}}
    <template x-if="hasContent">
        <div class="qp-card">

            {{-- Header ─────────────────────────────────────────────────── --}}
            <div class="qp-header">
                <div class="qp-header-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <div class="qp-header-meta">
                    <p class="qp-header-title">Tampilan Peserta</p>
                    <p class="qp-header-sub">Begini soal ini terlihat saat ujian berlangsung</p>
                </div>
                <span class="qp-live">
                    <span class="qp-live-dot"></span>
                    Live
                </span>
            </div>

            {{-- Meta badges ─────────────────────────────────────────────── --}}
            <div class="qp-meta">
                <template x-if="category === 'easy'">
                    <span class="qp-badge qp-badge-easy">● Mudah</span>
                </template>
                <template x-if="category === 'medium'">
                    <span class="qp-badge qp-badge-medium">◑ Sedang</span>
                </template>
                <template x-if="category === 'hard'">
                    <span class="qp-badge qp-badge-hard">◆ Sulit</span>
                </template>
                <template x-if="evalMethod === 'correct_wrong'">
                    <span class="qp-badge qp-badge-teknis">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Teknis
                    </span>
                </template>
                <template x-if="evalMethod === 'weighted'">
                    <span class="qp-badge qp-badge-mansoskul">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Mansoskul
                    </span>
                </template>
            </div>

            {{-- Body ────────────────────────────────────────────────────── --}}
            <div class="qp-body">

                {{-- Question text ─────────────────────────────────────── --}}
                <div class="qp-section-label">
                    <span class="qp-dot" style="background:#f59e0b"></span>
                    Pertanyaan
                </div>
                <div class="qp-question-text prose prose-sm max-w-none" x-html="qText"></div>

                {{-- Options ──────────────────────────────────────────── --}}
                <div class="qp-section-label">
                    <span class="qp-dot" style="background:#3b82f6"></span>
                    Pilihan Jawaban
                    <span
                        style="font-weight:400;text-transform:none;letter-spacing:0;font-size:0.625rem;color:#94a3b8;margin-left:.25rem"
                        x-show="visibleOptions.length > 0" x-text="'(' + visibleOptions.length + ' opsi terisi)'">
                    </span>
                </div>

                {{-- Has visible options ──────────────────────────────── --}}
                <template x-if="visibleOptions.length > 0">
                    <div>
                        <template x-for="(opt, idx) in visibleOptions" :key="idx">
                            <div class="qp-option"
                                :class="{
                                    'correct': evalMethod === 'correct_wrong' && opt.is_correct,
                                    'weighted-has-score': evalMethod === 'weighted' && opt.score !== null && opt
                                        .score !== ''
                                }">
                                <div class="qp-letter" x-text="optionLabel(idx)"></div>
                                <div class="qp-option-content">
                                    <div class="qp-option-body prose prose-sm max-w-none"
                                        x-html="opt.answer_text || '&mdash;'">
                                    </div>
                                    <template x-if="evalMethod === 'correct_wrong' && opt.is_correct">
                                        <span class="qp-option-tag qp-tag-correct">
                                            ✓ Kunci Jawaban
                                        </span>
                                    </template>
                                    <template
                                        x-if="evalMethod === 'weighted' && opt.score !== null && opt.score !== ''">
                                        <span class="qp-option-tag qp-tag-score"
                                            x-text="'Bobot: ' + opt.score + ' poin'">
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Hidden (empty) options hint ──────────────── --}}
                        <template x-if="(options.length - visibleOptions.length) > 0">
                            <div class="qp-more">
                                + <span x-text="options.length - visibleOptions.length"></span>
                                pilihan lainnya belum diisi
                            </div>
                        </template>
                    </div>
                </template>

                {{-- No filled options yet ────────────────────────────── --}}
                <template x-if="visibleOptions.length === 0">
                    <div class="qp-no-options">
                        Pilihan jawaban belum diisi — tambahkan teks pada opsi di bagian <strong>Jawaban</strong> di
                        atas
                    </div>
                </template>

            </div>
        </div>
    </template>
</div>

{{-- ── Alpine component registration ─────────────────────────────────── --}}
<script>
    (function() {
        function registerQuestionPreview() {
            if (typeof Alpine === 'undefined') return;
            if (Alpine._data && Alpine._data['questionPreview']) return;

            Alpine.data('questionPreview', function(examTypeMap) {
                return {
                    qText: '',
                    options: [],
                    evalMethod: 'correct_wrong',
                    category: null,
                    hasContent: false,

                    // Only return options whose answer_text is non-empty
                    get visibleOptions() {
                        return this.options.filter(function(opt) {
                            return ((opt.answer_text || '').replace(/<[^>]*>/g, '').trim()
                                .length > 0);
                        });
                    },

                    init() {
                        const self = this;

                        // ── Find the wire:id element (Livewire root) ─────────
                        // In Livewire 3 / Filament, the page component element
                        // has wire:id="xxx" and Alpine data includes $wire proxy.
                        // We find it once and use Alpine.evaluate() against it.
                        this._wireEl = null;
                        let el = this.$el;
                        while (el) {
                            if (el.hasAttribute && el.hasAttribute('wire:id')) {
                                this._wireEl = el;
                                break;
                            }
                            el = el.parentElement;
                        }

                        // Initial sync
                        self._sync();

                        // ── Poll for changes every 400ms ─────────────────────
                        let prevSnapshot = '';
                        setInterval(function() {
                            try {
                                // Re-find if not found on init
                                if (!self._wireEl) {
                                    let el = self.$el;
                                    while (el) {
                                        if (el.hasAttribute && el.hasAttribute('wire:id')) {
                                            self._wireEl = el;
                                            break;
                                        }
                                        el = el.parentElement;
                                    }
                                }
                                if (!self._wireEl) return;

                                const d = self._readData();
                                const snap = JSON.stringify(d);
                                if (snap !== prevSnapshot) {
                                    prevSnapshot = snap;
                                    self._sync();
                                }
                            } catch (e) {}
                        }, 400);
                    },

                    /**
                     * Read form data using Alpine.evaluate against the
                     * Livewire component element. This preserves the
                     * Alpine magic context so $wire is properly resolved.
                     */
                    _readData() {
                        try {
                            const wireEl = this._wireEl;
                            if (!wireEl) return {};

                            // Use Alpine.evaluate to run in the correct magic scope
                            const data = Alpine.evaluate(wireEl, '$wire.data');
                            if (!data) return {};

                            return {
                                question_text: data.question_text || '',
                                options: data.options || {},
                                exam_type_id: data.exam_type_id,
                                category: data.category,
                            };
                        } catch (e) {
                            return {};
                        }
                    },

                    _sync() {
                        try {
                            const d = this._readData();

                            this.qText = d.question_text || '';
                            this.category = d.category || null;

                            // Repeater stores items keyed by UUID → convert to array
                            const raw = d.options || {};
                            this.options = (typeof raw === 'object' && !Array.isArray(raw)) ?
                                Object.values(raw) :
                                (Array.isArray(raw) ? raw : []);

                            // Resolve evaluation method from embedded exam-type map
                            const etId = d.exam_type_id;
                            if (etId && examTypeMap[etId]) {
                                this.evalMethod = examTypeMap[etId];
                            }

                            // Has real content (ignore empty paragraph from TipTap: <p></p>)
                            const stripped = this.qText.replace(/<[^>]*>/g, '').trim();
                            this.hasContent = stripped.length > 0;

                            // Re-render KaTeX if loaded
                            this.$nextTick(function() {
                                if (typeof renderMathInElement !== 'undefined') {
                                    try {
                                        renderMathInElement(this.$el, {
                                            delimiters: [{
                                                    left: '$$',
                                                    right: '$$',
                                                    display: true
                                                },
                                                {
                                                    left: '$',
                                                    right: '$',
                                                    display: false
                                                },
                                                {
                                                    left: '\\(',
                                                    right: '\\)',
                                                    display: false
                                                },
                                                {
                                                    left: '\\[',
                                                    right: '\\]',
                                                    display: true
                                                },
                                            ],
                                            throwOnError: false,
                                        });
                                    } catch (e) {
                                        /* ignore */
                                    }
                                }
                            }.bind(this));

                        } catch (e) {
                            /* not ready */
                        }
                    },

                    optionLabel(index) {
                        return String.fromCharCode(65 + index);
                    },
                };
            });
        }

        if (window.Alpine) {
            registerQuestionPreview();
        }
        document.addEventListener('alpine:init', registerQuestionPreview);
        document.addEventListener('alpine:initialized', registerQuestionPreview);
    }());
</script>
