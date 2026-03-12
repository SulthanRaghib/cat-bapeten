{{-- ===================================================================
     Komponen Pratinjau Soal Langsung (Live Question Preview)
     Teknologi : Server-side Blade rendering via $get() dari Filament Schema
     Cara kerja: Field ->live() memicu Livewire re-render, $get() membaca state terbaru
     ================================================================== --}}

@php
    use App\Models\ExamType;
    use Filament\Forms\Components\RichEditor\RichContentRenderer;

    // ── Helper: konversi TipTap JSON → HTML ──
    // RichEditor di Filament v5 menyimpan state sebagai TipTap JSON (array),
    // bukan HTML string. Saat dibaca via $get() dalam konteks Repeater,
    // nilainya tetap berupa array. Kita konversi ke HTML pakai RichContentRenderer.
    $tiptapToHtml = function ($value): string {
        if (is_string($value)) {
            return $value; // sudah HTML
        }
        if (is_array($value) && isset($value['type'])) {
            try {
                return RichContentRenderer::make($value)->toHtml();
            } catch (\Throwable $e) {
                return '';
            }
        }
        return '';
    };

    // ── Baca state form via Filament $get() utility ──
    $rawQuestionText = $get('question_text');
    $questionText = $tiptapToHtml($rawQuestionText);
    $examTypeId = $get('exam_type_id');
    $category = $get('category');

    // Options: Repeater->getState() mengembalikan UUID-keyed array
    $rawOptions = $get('options');
    $allOptions = is_array($rawOptions) ? array_values($rawOptions) : [];

    // Resolve evaluation method
    $evalMethod = $examTypeId ? ExamType::find($examTypeId)?->evaluation_method ?? 'correct_wrong' : null;

    // Cek apakah ada konten (strip HTML tags untuk deteksi teks asli)
    $hasContent = strlen(trim(strip_tags($questionText))) > 0;

    // Hitung opsi yang sudah punya teks
    $filledCount = count(
        array_filter($allOptions, function ($opt) use ($tiptapToHtml) {
            $html = $tiptapToHtml($opt['answer_text'] ?? '');
            return strlen(trim(strip_tags($html))) > 0;
        }),
    );

    $letters = range('A', 'Z');
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
            background: #2f2f32 !important;
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
            background: #242427;
            border-color: #334155;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .3);
        }

        /* ══ Card header ═══════════════════════════════════════════════════════ */
        .qp-header {
            background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-500, #f59e0b) 10%, transparent) 0%, color-mix(in srgb, var(--color-primary-400, #fbbf24) 16%, transparent) 100%);
            border-bottom: 1.5px solid color-mix(in srgb, var(--color-primary-400, #fbbf24) 45%, transparent);
            padding: 0.875rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dark .qp-header {
            background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-800, #92400e) 20%, transparent) 0%, color-mix(in srgb, var(--color-primary-700, #b45309) 15%, transparent) 100%);
            border-bottom-color: color-mix(in srgb, var(--color-primary-700, #b45309) 60%, transparent);
        }

        .qp-header-icon {
            flex-shrink: 0;
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, var(--color-primary-500, #f59e0b), var(--color-primary-600, #d97706));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 2px 6px color-mix(in srgb, var(--color-primary-500, #f59e0b) 35%, transparent);
        }

        .qp-header-meta {
            flex: 1;
        }

        .qp-header-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
        }

        .dark .qp-header-title {
            color: var(--color-primary-300, #fcd34d);
        }

        .qp-header-sub {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 1px;
        }

        .dark .qp-header-sub {
            color: var(--color-primary-400, #fbbf24);
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
            background: color-mix(in srgb, var(--color-primary-500, #f59e0b) 4%, transparent);
            border-bottom: 1px solid color-mix(in srgb, var(--color-primary-400, #fbbf24) 20%, transparent);
            min-height: 2.25rem;
        }

        .dark .qp-meta {
            background: #242427;
            border-bottom-color: #334155;
        }

        .qp-badge {
            display: inline-flex;
            .dark .qp-option align-items: center;
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

        .dark .qp-badge-easy {
            background: #052e16;
            color: #4ade80;
            border-color: #166534;
        }

        .dark .qp-badge-medium {
            background: #451a03;
            color: #fbbf24;
            border-color: #92400e;
        }

        .dark .qp-badge-hard {
            background: #450a0a;
            color: #f87171;
            border-color: #991b1b;
        }

        .dark .qp-badge-teknis {
            background: #1e1b4b;
            color: #818cf8;
            border-color: #3730a3;
        }

        .dark .qp-badge-mansoskul {
            background: #2e1065;
            color: #a78bfa;
            border-color: #5b21b6;
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
            color: #64748b;
            margin-bottom: 0.65rem;
            user-select: none;
        }

        .dark .qp-section-label {
            color: #94a3b8;
        }

        .qp-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            flex-shrink: 0;
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary-500, #f59e0b) 20%, transparent);
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
            background: #18181b;
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

<div class="qp-root" wire:key="qp-{{ md5(serialize([$questionText, $allOptions, $category, $evalMethod])) }}">
    @if (!$hasContent)
        {{-- ── Empty state ─────────────────────────────────────────────── --}}
        <div class="qp-empty">
            <svg class="qp-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            <p class="qp-empty-title">{{ __('Preview will appear here') }}</p>
            <p class="qp-empty-sub">
                {{ __('Start typing the question text in the') }}
                <strong>{{ __('Question Content & Discussion') }}</strong>
                {{ __('to see a live preview as participants will see it.') }}
            </p>
        </div>
    @else
        {{-- ── Live preview card ───────────────────────────────────────── --}}
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
                    <p class="qp-header-title">{{ __('Participant View') }}</p>
                    <p class="qp-header-sub">{{ __('This is how the question looks during the exam') }}</p>
                </div>
                <span class="qp-live">
                    <span class="qp-live-dot"></span>
                    Live
                </span>
            </div>

            {{-- Meta badges ─────────────────────────────────────────────── --}}
            <div class="qp-meta">
                @if ($category === 'easy')
                    <span class="qp-badge qp-badge-easy">● {{ __('Easy') }}</span>
                @elseif ($category === 'medium')
                    <span class="qp-badge qp-badge-medium">◑ {{ __('Medium') }}</span>
                @elseif ($category === 'hard')
                    <span class="qp-badge qp-badge-hard">◆ {{ __('Hard') }}</span>
                @endif

                @if ($evalMethod === 'correct_wrong')
                    <span class="qp-badge qp-badge-teknis">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('Technical') }}
                    </span>
                @elseif ($evalMethod === 'weighted')
                    <span class="qp-badge qp-badge-mansoskul">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        {{ __('Mansoskul') }}
                    </span>
                @endif
            </div>

            {{-- Body ────────────────────────────────────────────────────── --}}
            <div class="qp-body">

                {{-- Question text ─────────────────────────────────────── --}}
                <div class="qp-section-label">
                    <span class="qp-dot" style="background: var(--color-primary-500, #f59e0b)"></span>
                    {{ __('Question') }}
                </div>
                <div class="qp-question-text prose prose-sm max-w-none">{!! $questionText !!}</div>

                {{-- Options ──────────────────────────────────────────── --}}
                <div class="qp-section-label">
                    <span class="qp-dot" style="background:#3b82f6"></span>
                    {{ __('Answer Options') }}
                    @if (count($allOptions) > 0)
                        <span
                            style="font-weight:400;text-transform:none;letter-spacing:0;font-size:0.625rem;color:#94a3b8;margin-left:.25rem">
                            ({{ $filledCount }}/{{ count($allOptions) }} {{ __('options filled') }})
                        </span>
                    @endif
                </div>

                @if (count($allOptions) > 0)
                    <div>
                        @foreach ($allOptions as $idx => $opt)
                            @php
                                $answerHtml = $tiptapToHtml($opt['answer_text'] ?? '');
                                $hasText = strlen(trim(strip_tags($answerHtml))) > 0;
                                $isCorrect = $evalMethod === 'correct_wrong' && !empty($opt['is_correct']);
                                $hasScore =
                                    $evalMethod === 'weighted' &&
                                    isset($opt['score']) &&
                                    $opt['score'] !== '' &&
                                    $opt['score'] !== null;
                                $optClasses = 'qp-option';
                                if ($isCorrect) {
                                    $optClasses .= ' correct';
                                }
                                if ($hasScore) {
                                    $optClasses .= ' weighted-has-score';
                                }
                            @endphp
                            <div class="{{ $optClasses }}">
                                <div class="qp-letter">{{ $letters[$idx] ?? '?' }}</div>
                                <div class="qp-option-content">
                                    @if ($hasText)
                                        <div class="qp-option-body prose prose-sm max-w-none">
                                            {!! $answerHtml !!}
                                        </div>
                                    @else
                                        <div class="qp-option-body"
                                            style="color:#94a3b8;font-style:italic;font-size:0.8125rem">
                                            {{ __('Waiting for answer text...') }}
                                        </div>
                                    @endif
                                    @if ($isCorrect)
                                        <span class="qp-option-tag qp-tag-correct">✓ {{ __('Answer Key') }}</span>
                                    @endif
                                    @if ($hasScore)
                                        <span class="qp-option-tag qp-tag-score">{{ __('Weight:') }}
                                            {{ $opt['score'] }} {{ __('pts') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="qp-no-options">
                        {{ __('Answer options not yet added — click') }} <strong>{{ __('Add to options') }}</strong>
                        {{ __('in the Answers section') }}
                    </div>
                @endif

            </div>
        </div>
    @endif
</div>

@once
    <script>
        document.addEventListener('livewire:init', () => {
            function renderQpMath() {
                document.querySelectorAll('.qp-root').forEach(el => {
                    if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                        MathJax.typesetClear([el]);
                        MathJax.typesetPromise([el]).catch(() => {});
                    }
                });
            }

            // Render setelah setiap Livewire commit selesai (morph DOM)
            Livewire.hook('commit', ({
                succeed
            }) => {
                succeed(() => {
                    requestAnimationFrame(() => renderQpMath());
                });
            });

            // Render pertama kali saat halaman dimuat
            requestAnimationFrame(() => renderQpMath());
        });
    </script>
@endonce
