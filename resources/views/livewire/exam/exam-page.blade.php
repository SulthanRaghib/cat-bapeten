@push('styles')
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <style>
        .exam-app {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0f172a;
        }

        .exam-surface {
            background: linear-gradient(135deg, oklch(0.97 0.02 58.318) 0%, oklch(0.95 0.04 58.318) 100%);
            border-radius: 32px;
            padding: 32px 24px 120px;
            box-shadow: 0 12px 48px rgba(15, 23, 42, 0.08);
            min-height: calc(100vh - 360px);
        }

        .exam-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 8px 32px rgba(15, 23, 42, 0.08);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .exam-card:hover {
            box-shadow: 0 16px 48px rgba(15, 23, 42, 0.12);
            transform: translateY(-2px);
        }

        @media (min-width: 1024px) {
            .exam-nav {
                position: sticky;
                top: 9rem;
                max-height: calc(100vh - 9rem);
                overflow-y: auto;
            }
        }

        .timer-shell {
            background: linear-gradient(135deg, oklch(0.96 0.06 58.318) 0%, #ffffff 100%);
            border: 2px solid oklch(0.82 0.15 58.318);
            border-radius: 18px;
            padding: 12px 24px;
            box-shadow: 0 8px 28px rgba(37, 99, 235, 0.18);
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            min-width: 150px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .timer-shell[data-state="warning"] {
            border-color: oklch(0.72 0.16 85);
            box-shadow: 0 12px 32px rgba(234, 179, 8, 0.24);
        }

        .timer-shell[data-state="danger"] {
            border-color: oklch(0.62 0.24 25);
            box-shadow: 0 14px 40px rgba(220, 38, 38, 0.28);
        }

        .timer-value {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: oklch(0.52 0.19 58.318);
        }

        .timer-value[data-state="warning"] {
            color: oklch(0.72 0.16 85);
            animation: exam-pulse 1.8s ease-in-out infinite;
        }

        .timer-value[data-state="danger"] {
            color: oklch(0.62 0.24 25);
            animation: exam-pulse 1.2s ease-in-out infinite;
        }

        .question-option {
            border: 2px solid #e2e8f0;
            border-radius: 18px;
            padding: 18px 20px;
            display: flex;
            gap: 18px;
            align-items: flex-start;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        }

        .question-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16);
        }

        .question-option.selected {
            background: linear-gradient(135deg, oklch(0.72 0.19 58.318) 0%, oklch(0.52 0.19 58.318) 100%);
            border-color: transparent;
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
                                <button type="button" wire:click="toggleDoubtful" wire:loading.attr="disabled"
                                    wire:target="toggleDoubtful" class="{{ $flagClasses }}"
                                    aria-pressed="{{ $currentDoubtful ? 'true' : 'false' }}">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M5.5 3a1.5 1.5 0 00-1.5 1.5v15a1 1 0 102 0v-4.146l1.276-.638a3 3 0 012.536.026l1.715.8a5 5 0 004.018.063l4.091-1.636a1.5 1.5 0 00.936-1.384V4.5A1.5 1.5 0 0018.5 3h-13z" />
                                    </svg>
                                    <span>{{ $currentDoubtful ? 'Ditandai' : 'Ragu-ragu' }}</span>
                                </button>
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

                        <div class="mt-8 space-y-4">
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
                                @foreach ($options as $code => $text)
                                    @php
                                        if (is_array($text)) {
                                            $optionCode = $text['kode'] ?? $code;
                                            $optionText = $text['teks'] ?? '';
                                        } else {
                                            $optionCode = $code;
                                            $optionText = $text;
                                        }
                                        $isSelected = $currentAnswer === $optionCode;
                                        $optionClasses = 'question-option';
                                        if ($isSelected) {
                                            $optionClasses .= ' selected';
                                        }
                                    @endphp
                                    <label wire:key="option-{{ $this->currentQuestion->id }}-{{ $optionCode }}"
                                        wire:click="saveAnswer('{{ $optionCode }}')" class="{{ $optionClasses }}">
                                        <div class="option-letter">
                                            {{ $optionCode }}
                                        </div>
                                        <div class="flex-1 option-text">{!! $optionText !!}</div>
                                        <input type="radio" name="answer" value="{{ $optionCode }}" class="hidden"
                                            @checked($isSelected) onclick="event.stopPropagation()">
                                    </label>
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
                    <button wire:click="prevQuestion" wire:loading.attr="disabled" @disabled($currentQuestionIndex === 0)
                        class="exam-action-secondary px-6 py-3 rounded-xl text-sm font-semibold uppercase tracking-wide">
                        ← Sebelumnya
                    </button>

                    <div wire:loading class="text-blue-500 font-semibold text-sm animate-pulse">
                        Menyimpan...
                    </div>

                    <button wire:click="nextQuestion" wire:loading.attr="disabled" @disabled($currentQuestionIndex === $totalQuestions - 1)
                        class="exam-action-primary px-6 py-3 rounded-xl text-sm font-semibold uppercase tracking-wide">
                        @if ($currentQuestionIndex === $totalQuestions - 1)
                            Selesai →
                        @else
                            Selanjutnya →
                        @endif
                    </button>
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
            var componentName = 'exam.exam-page';
            var timerInterval = null;

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

            if (document.readyState !== 'loading') {
                initialiseEnhancements();
            } else {
                document.addEventListener('DOMContentLoaded', initialiseEnhancements);
            }

            document.addEventListener('livewire:load', function() {
                initialiseEnhancements();

                if (typeof Livewire !== 'undefined') {
                    Livewire.hook('message.processed', function(message, component) {
                        if (component && component.fingerprint && component.fingerprint.name ===
                            componentName) {
                            requestAnimationFrame(initialiseEnhancements);
                        }
                    });

                    Livewire.on('answer-saved', function() {
                        showSaveIndicator();
                        requestAnimationFrame(renderMath);
                    });
                    Livewire.on('question-changed', function() {
                        requestAnimationFrame(initialiseEnhancements);
                    });
                }
            });
        })();
    </script>
@endpush
