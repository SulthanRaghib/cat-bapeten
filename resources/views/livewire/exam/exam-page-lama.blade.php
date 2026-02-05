@push('styles')
    <style>
        /* ================= LAYOUT ================= */
        .container {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 20px;
        }

        /* ================= SECTION SOAL ================= */
        .question-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .question-number {
            font-weight: 800;
            font-size: 20px;
            color: #333;
            margin-bottom: 12px;
        }

        .question-content {
            margin-top: 12px;
        }

        .question-text {
            font-size: 18px;
            margin-bottom: 16px;
            line-height: 1.6;
            color: #333;
        }

        /* GAMBAR SOAL */
        .question-image {
            margin: 15px 0;
            text-align: center;
        }

        .question-image img {
            max-width: 80%;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        /* RUMUS / SIMBOL */
        .question-formula {
            margin: 15px 0;
            padding: 12px;
            background: #f1f8f1;
            border-left: 4px solid #2e7d32;
            font-size: 18px;
            text-align: center;
            font-weight: 700;
        }

        /* ================= OPSI JAWABAN ================= */
        .options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }

        .option {
            border: 1px solid #dcdcdc;
            padding: 14px 16px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
            background: #ffffff;
        }

        .option:hover {
            background: #eef4ee;
            border-color: #2e7d32;
        }

        .option.selected {
            background: #2e7d32;
            color: white;
            border-color: #2e7d32;
        }

        .option input[type="radio"] {
            transform: scale(1.2);
            cursor: pointer;
        }

        .option span {
            flex: 1;
            font-size: 16px;
        }

        .option-text {
            flex: 1;
            font-size: 16px;
            line-height: 1.5;
        }

        .option.selected .option-text {
            color: white;
        }

        /* ================= RAGU-RAGU ================= */
        .ragu {
            margin-top: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            cursor: pointer;
        }

        .ragu input[type="checkbox"] {
            transform: scale(1.2);
            cursor: pointer;
        }

        .flag-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #dcdcdc;
            background: white;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .flag-toggle svg {
            width: 18px;
            height: 18px;
        }

        .flag-toggle:hover {
            background: #fff3cd;
            border-color: #f9a825;
        }

        .flag-toggle.active {
            background: #f9a825;
            color: #000;
            border-color: #f9a825;
        }

        /* ================= NAVIGASI ================= */
        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 12px;
        }

        button {
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        button.primary {
            background: #2e7d32;
            color: white;
        }

        button.primary:hover:not(:disabled) {
            background: #1b5e20;
        }

        button.secondary {
            background: #e0e0e0;
            color: #333;
        }

        button.secondary:hover:not(:disabled) {
            background: #bdbdbd;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            background: white;
            border-radius: 12px;
            padding: 20px;
            position: sticky;
            top: 120px;
            align-self: start;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .sidebar h3 {
            margin-top: 0;
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 16px;
        }

        /* LEGEND */
        .legend {
            display: flex;
            gap: 18px;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .box {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .text {
            font-size: 14px;
            white-space: nowrap;
        }

        .status-belum { background: #bdbdbd; }
        .status-ragu  { background: #f9a825; }
        .status-jawab { background: #2e7d32; }

        /* ================= DAFTAR SOAL ================= */
        .question-list {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }

        .question-list button,
        .nav-indicator {
            padding: 10px;
            background: #e0e0e0;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            color: #333;
        }

        .question-list button:hover,
        .nav-indicator:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .question-list button.answered,
        .nav-indicator.answered {
            background: #2e7d32;
            color: white;
        }

        .question-list button.doubt,
        .nav-indicator.doubtful {
            background: #f9a825;
            color: #000;
        }

        .question-list button.current,
        .nav-indicator.current {
            background: #1976d2;
            color: white;
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.4);
        }

        /* ================= FINISH BUTTON ================= */
        .finish {
            width: 100%;
            background: #c62828;
            color: white;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .finish:hover {
            background: #b71c1c;
            box-shadow: 0 4px 12px rgba(198, 40, 40, 0.4);
        }

        /* ================= ANSWER STATS ================= */
        .answer-stats {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e0e0e0;
        }

        .answer-stats > div {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        /* ================= SAVE INDICATOR ================= */
        .save-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            background: #d4edda;
            color: #155724;
            font-size: 13px;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .save-indicator.show {
            opacity: 1;
        }

        .save-indicator svg {
            width: 16px;
            height: 16px;
        }

        /* ================= FOOTER NAVIGATION ================= */
        .exam-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e0e0e0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.08);
            z-index: 100;
        }

        .exam-footer-inner {
            max-width: 100%;
            margin: 0 auto;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .exam-action-primary {
            background: #2e7d32;
            color: white;
        }

        .exam-action-primary:hover:not(:disabled) {
            background: #1b5e20;
        }

        .exam-action-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .exam-action-secondary:hover:not(:disabled) {
            background: #bdbdbd;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                margin-top: 20px;
            }
        }

        @media (max-width: 640px) {
            .exam-footer-inner {
                flex-direction: column;
                align-items: stretch;
            }

            .flag-toggle {
                width: 100%;
                justify-content: center;
            }

            .question-list {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* ================= LOADING ANIMATION ================= */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
@endpush
            box-shadow: 0 16px 42px rgba(37, 99, 235, 0.35);
            color: white;
        }

        .option-letter {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: 2px solid #cbd5f5;
            background: #f8fafc;
            color: oklch(0.52 0.19 58.318);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.25s ease;
        }

        .question-option.selected .option-letter {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.45);
            color: #ffffff;
        }

        .question-option .option-text {
            font-size: 1rem;
            line-height: 1.65;
            color: #1f2937;
        }

        .question-option.selected .option-text {
            color: rgba(255, 255, 255, 0.96);
        }

        .nav-indicator {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            background: linear-gradient(135deg, #ffffff 0%, #f7fafc 100%);
            color: #475569;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
            transition: all 0.25s ease;
        }

        .nav-indicator:hover {
            transform: scale(1.06);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
        }

        .nav-indicator.answered {
            background: linear-gradient(135deg, oklch(0.65 0.18 150) 0%, oklch(0.55 0.18 150) 100%);
            border-color: transparent;
            color: #ffffff;
        }

        .nav-indicator.current {
            background: linear-gradient(135deg, oklch(0.72 0.19 58.318) 0%, oklch(0.52 0.19 58.318) 100%);
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 14px 32px rgba(37, 99, 235, 0.35);
            color: #ffffff;
        }

        .nav-indicator.doubtful {
            background: linear-gradient(135deg, oklch(0.82 0.16 65) 0%, oklch(0.72 0.2 65) 100%);
            border-color: transparent;
            color: #7c2d12;
        }

        .nav-indicator.current.doubtful {
            background: linear-gradient(135deg, oklch(0.72 0.2 65) 0%, oklch(0.62 0.2 65) 100%);
            color: #fff;
        }

        .save-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            color: oklch(0.65 0.18 150);
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .save-indicator.show {
            opacity: 1;
            transform: translateY(0);
        }

        .flag-toggle {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 16px;
            border: 2px solid rgba(251, 191, 36, 0.45);
            background: #fff8eb;
            color: #92400e;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px rgba(234, 179, 8, 0.18);
        }

        .flag-toggle svg {
            width: 18px;
            height: 18px;
        }

        .flag-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(234, 179, 8, 0.26);
        }

        .flag-toggle.active {
            background: linear-gradient(135deg, oklch(0.82 0.16 65) 0%, oklch(0.72 0.2 65) 100%);
            border-color: transparent;
            color: #fff;
        }

        .flag-toggle:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .answer-stats {
            background: linear-gradient(135deg, #ffffff 0%, oklch(0.98 0.02 58.318) 100%);
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
        }

        .exam-action-primary {
            background: linear-gradient(135deg, oklch(0.72 0.19 58.318) 0%, oklch(0.52 0.19 58.318) 100%);
            color: #ffffff;
            box-shadow: 0 14px 32px rgba(37, 99, 235, 0.32);
            transition: all 0.25s ease;
        }

        .exam-action-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 44px rgba(37, 99, 235, 0.38);
        }

        .exam-action-primary:disabled {
            background: #c7d2fe;
            cursor: not-allowed;
            box-shadow: none;
        }

        .exam-action-secondary {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            color: #475569;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
            transition: all 0.25s ease;
        }

        .exam-action-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
        }

        .exam-action-secondary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .exam-legend-dot {
            width: 16px;
            height: 16px;
            border-radius: 6px;
        }

        .exam-footer {
            position: fixed;
            inset-inline: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(18px);
            border-top: 1px solid rgba(148, 163, 184, 0.24);
            box-shadow: 0 -10px 32px rgba(15, 23, 42, 0.12);
            z-index: 60;
        }

        .exam-footer-inner {
            max-width: 960px;
            margin-inline: auto;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        @media (max-width: 640px) {
            .exam-footer-inner {
                flex-direction: column;
                align-items: stretch;
            }

            .flag-toggle {
                width: 100%;
                justify-content: center;
            }
        }

        @keyframes exam-pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.04);
                opacity: 0.85;
            }
        }
    </style>
@endpush

<div class="exam-app">
    <div class="exam-surface">
        @if ($totalQuestions > 0 && $this->currentQuestion)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="exam-card p-8">
                        @php
                            $flagClasses = 'flag-toggle';
                            if ($currentDoubtful) {
                                $flagClasses .= ' active';
                            }
                        @endphp
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between pb-4 border-b border-slate-200">
                            <div>
                                <span class="text-sm font-semibold tracking-wide text-blue-500 uppercase">Soal
                                    {{ $currentQuestionIndex + 1 }} dari {{ $totalQuestions }}</span>
                                <h2 class="text-xl font-bold text-slate-900 mt-1">Pertanyaan</h2>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-end">
                                {{-- WRAPPER ALPINE.JS UNTUK OPTIMISTIC UI --}}
                                <div x-data="{
                                    localDoubtful: @entangle('currentDoubtful').live,
                                    toggle() {
                                        // 1. Ubah state lokal INSTAN (tanpa nunggu server)
                                        this.localDoubtful = !this.localDoubtful;
                                
                                        // 2. Kirim request ke server di background
                                        $wire.toggleDoubtful();
                                    }
                                }" wire:ignore.self>

                                    <button type="button" @click="toggle()" class="flag-toggle"
                                        :class="localDoubtful ? 'active' : ''"
                                        :aria-pressed="localDoubtful ? 'true' : 'false'">

                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M5.5 3a1.5 1.5 0 00-1.5 1.5v15a1 1 0 102 0v-4.146l1.276-.638a3 3 0 012.536.026l1.715.8a5 5 0 004.018.063l4.091-1.636a1.5 1.5 0 00.936-1.384V4.5A1.5 1.5 0 0018.5 3h-13z" />
                                        </svg>

                                        <span x-text="localDoubtful ? 'Ditandai' : 'Ragu-ragu'"></span>
                                    </button>

                                </div>
                                <div id="save-indicator" class="save-indicator">
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Jawaban tersimpan</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 prose max-w-none text-slate-800 question-content text-lg leading-relaxed">
                            {!! $this->currentQuestion->question_text !!}
                        </div>

                        {{-- Optimistic UI with Alpine.js for instant feedback --}}
                        <div class="mt-8 space-y-4" x-data="{
                            localAnswer: @entangle('currentAnswer'),
                            saving: false,
                            selectAnswer(code) {
                                this.localAnswer = code;
                                this.saving = true;
                                $wire.saveAnswer(code).then(() => {
                                    this.saving = false;
                                }).catch(() => {
                                    this.saving = false;
                                });
                            }
                        }" wire:ignore.self>
                            @php
                                $options = $this->currentQuestion->options;
                                if (is_string($options)) {
                                    $decoded = json_decode($options, true);
                                    if (is_array($decoded)) {
                                        $options = $decoded;
                                    }
                                }
                            @endphp

                            @if (is_array($options) && count($options) > 0)
                                @foreach ($options as $index => $optionData)
                                    @php
                                        // Determine option label (A, B, C...) based on index
                                        $optionLabel = chr(65 + $index);

                                        // We use the original array key/index as the unique ID/value to save
                                        // Unless there is a specific 'code' or 'id' in data, but index is safest to match backend logic
                                        $optionValue = (string) $index;

                                        if (is_array($optionData)) {
                                            // Handle various field names: 'answer_text' (new), 'teks' (legacy)
                                            $optionText = $optionData['answer_text'] ?? ($optionData['teks'] ?? '');

                                            // Ensure RichText content is handled if needed, usually it's HTML string
                                        } else {
                                            $optionText = $optionData;
                                        }
                                    @endphp
                                    <div wire:key="option-{{ $this->currentQuestion->id }}-{{ $index }}"
                                        @click="selectAnswer('{{ $optionValue }}')"
                                        class="question-option cursor-pointer"
                                        :class="localAnswer === '{{ $optionValue }}' ? 'selected' : ''">
                                        <div class="option-letter"
                                            :class="localAnswer === '{{ $optionValue }}' ?
                                                'bg-white/20 border-white/45 text-white' : ''">
                                            {{ $optionLabel }}
                                        </div>
                                        <div class="flex-1 option-text">{!! $optionText !!}</div>
                                        <template x-if="saving && localAnswer === '{{ $optionValue }}'">
                                            <svg class="w-5 h-5 animate-spin text-white" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </template>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-slate-500 italic">Pilihan jawaban belum tersedia.</p>
                            @endif
                        </div>
                    </div>

                    <div class="h-32"></div>
                </div>

                <div class="lg:col-span-1 space-y-6">
                    <div class="exam-card exam-nav p-6">
                        <h3 class="text-base font-bold text-slate-900 mb-5 tracking-wide">Navigasi Soal</h3>

                        <div class="flex flex-wrap gap-3 text-xs font-medium text-slate-500 mb-6">
                            <div class="flex items-center gap-2">
                                <span class="exam-legend-dot"
                                    style="background: linear-gradient(135deg, oklch(0.65 0.18 150) 0%, oklch(0.55 0.18 150) 100%);"></span>
                                Dijawab
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="exam-legend-dot"
                                    style="background: linear-gradient(135deg, oklch(0.82 0.16 65) 0%, oklch(0.72 0.2 65) 100%);"></span>
                                Ragu-ragu
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="exam-legend-dot"
                                    style="background: #f1f5f9; border: 2px solid #e2e8f0;"></span>
                                Belum dijawab
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="exam-legend-dot"
                                    style="background: linear-gradient(135deg, oklch(0.72 0.19 58.318) 0%, oklch(0.52 0.19 58.318) 100%);"></span>
                                Aktif
                            </div>
                        </div>

                        <div class="grid grid-cols-5 gap-2 mb-6">
                            @forelse ($questionStatuses as $status)
                                @php
                                    $classes = 'nav-indicator';
                                    if ($status['answered']) {
                                        $classes .= ' answered';
                                    } else {
                                        $classes .= ' unanswered';
                                    }

                                    if ($status['doubtful']) {
                                        $classes .= ' doubtful';
                                    }

                                    if ($status['current']) {
                                        $classes .= ' current';
                                    }
                                @endphp
                                <button type="button" wire:click="goToQuestion({{ $status['index'] }})"
                                    wire:key="nav-{{ $status['question_id'] }}" class="{{ $classes }}">
                                    {{ $status['number'] }}
                                </button>
                            @empty
                                <p class="text-sm text-slate-500 col-span-5">Belum ada daftar soal.</p>
                            @endforelse
                        </div>

                        <div class="answer-stats space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-medium">Dijawab</span>
                                <span class="font-bold text-green-600">{{ $answeredCount }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-medium">Ragu-ragu</span>
                                <span class="font-bold text-amber-600">{{ $doubtfulCount }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-medium">Belum dijawab</span>
                                <span class="font-bold text-slate-700">{{ $unansweredCount }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-medium">Total Soal</span>
                                <span class="font-bold text-slate-900">{{ $totalQuestions }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="exam-footer">
                <div class="exam-footer-inner">
                    <button wire:click="prevQuestion" @disabled($currentQuestionIndex === 0)
                        class="exam-action-secondary px-6 py-3 rounded-xl text-sm font-semibold uppercase tracking-wide
                               disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.class="opacity-70" wire:target="prevQuestion,nextQuestion">
                        <span wire:loading.remove wire:target="prevQuestion">← Sebelumnya</span>
                        <span wire:loading wire:target="prevQuestion" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memuat...
                        </span>
                    </button>

                    <div wire:loading wire:target="saveAnswer"
                        class="text-green-600 font-medium text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Menyimpan...
                    </div>

                    <button wire:click="nextQuestion" @disabled($currentQuestionIndex === $totalQuestions - 1)
                        class="exam-action-primary px-6 py-3 rounded-xl text-sm font-semibold uppercase tracking-wide
                               disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.class="opacity-70" wire:target="prevQuestion,nextQuestion">
                        <span wire:loading.remove wire:target="nextQuestion">
                            @if ($currentQuestionIndex === $totalQuestions - 1)
                                Selesai →
                            @else
                                Selanjutnya →
                            @endif
                        </span>
                        <span wire:loading wire:target="nextQuestion" class="flex items-center gap-2">
                            Memuat...
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </footer>
        @else
            <div class="exam-card p-10 text-center">
                <h2 class="text-xl font-semibold text-red-600 mb-2">Belum ada soal tersedia.</h2>
                <p class="text-slate-500">Silakan hubungi pengawas atau refresh halaman.</p>
            </div>
        @endif
    </div>
</div>


@push('scripts')
    <script>
        (function() {
            if (window.__examPageInitialised) {
                return;
            }

            window.__examPageInitialised = true;
            var timerInterval = null;
            var mathRenderDebounce = null;

            function initTimer() {
                var timerEl = document.getElementById('exam-timer');
                if (!timerEl) {
                    return;
                }

                var endAttr = timerEl.getAttribute('data-end-time');
                if (!endAttr) {
                    timerEl.textContent = '--:--';
                    return;
                }

                var endTime = Date.parse(endAttr);
                if (isNaN(endTime)) {
                    timerEl.textContent = '--:--';
                    return;
                }

                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }

                var container = timerEl.closest('[data-timer-container]');

                function setTimerState(state) {
                    timerEl.setAttribute('data-state', state);
                    if (container) {
                        container.setAttribute('data-state', state);
                    }
                }

                function prefix(value) {
                    return value.toString().padStart(2, '0');
                }

                function updateTimer() {
                    var now = Date.now();
                    var remaining = endTime - now;

                    if (remaining <= 0) {
                        timerEl.textContent = '00:00';
                        setTimerState('danger');
                        return;
                    }

                    var hours = Math.floor(remaining / (1000 * 60 * 60));
                    var minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((remaining % (1000 * 60)) / 1000);

                    if (hours > 0) {
                        timerEl.textContent = prefix(hours) + ':' + prefix(minutes) + ':' + prefix(seconds);
                    } else {
                        timerEl.textContent = prefix(minutes) + ':' + prefix(seconds);
                    }

                    if (remaining <= 5 * 60 * 1000) {
                        setTimerState('danger');
                    } else if (remaining <= 10 * 60 * 1000) {
                        setTimerState('warning');
                    } else {
                        setTimerState('normal');
                    }
                }

                updateTimer();
                timerInterval = setInterval(updateTimer, 1000);
            }

            function renderMath() {
                if (window.renderMathJax) {
                    window.renderMathJax();
                    return;
                }

                if (window.MathJax && window.MathJax.typesetPromise) {
                    var nodeList = document.querySelectorAll('.question-content, .option-text');
                    if (nodeList.length) {
                        var nodes = Array.from(nodeList);
                        if (typeof window.MathJax.typesetClear === 'function') {
                            window.MathJax.typesetClear(nodes);
                        }

                        window.MathJax.typesetPromise(nodes).catch(function(err) {
                            console.warn('MathJax error:', err);
                        });
                    }
                }
            }

            function showSaveIndicator() {
                var indicator = document.getElementById('save-indicator');
                if (!indicator) {
                    return;
                }

                indicator.classList.add('show');
                setTimeout(function() {
                    indicator.classList.remove('show');
                }, 1600);
            }

            function initialiseEnhancements() {
                initTimer();
                renderMath();
            }

            // Debounced MathJax render to prevent multiple rapid calls
            function debouncedRenderMath() {
                if (mathRenderDebounce) {
                    clearTimeout(mathRenderDebounce);
                }
                mathRenderDebounce = setTimeout(function() {
                    renderMath();
                }, 50);
            }

            // Initial setup
            if (document.readyState !== 'loading') {
                initialiseEnhancements();
            } else {
                document.addEventListener('DOMContentLoaded', initialiseEnhancements);
            }

            // Livewire 3 compatible hooks
            document.addEventListener('livewire:init', function() {
                // Hook into Livewire's morph cycle for MathJax re-rendering
                Livewire.hook('morph.updated', function({
                    el,
                    component
                }) {
                    // Only re-render MathJax, don't reinit timer (it's wire:ignore)
                    debouncedRenderMath();
                });

                // Listen for custom events from the component
                Livewire.on('answer-saved', function() {
                    showSaveIndicator();
                });

                Livewire.on('question-changed', function() {
                    requestAnimationFrame(initialiseEnhancements);
                });
            });

            // Fallback for initial page load
            document.addEventListener('livewire:navigated', function() {
                initialiseEnhancements();
            });
        })();
    </script>
@endpush
