@if ($totalQuestions > 0 && $this->currentQuestion)
    <div class="container" @if ($showResults) style="filter: blur(5px); pointer-events: none;" @endif>
        <!-- AREA SOAL -->
        <section class="question-section">
            <div class="question-number">Soal {{ $currentQuestionIndex + 1 }}</div>

            <!-- KONTEN SOAL -->
            <div class="question-content">
                <p class="question-text">{!! $this->currentQuestion->question_text !!}</p>
            </div>

            <!-- OPSI JAWABAN -->
            <div class="options" x-data="{
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
                            $optionLabel = chr(65 + $index);
                            $optionValue = (string) $index;

                            if (is_array($optionData)) {
                                $optionText = $optionData['answer_text'] ?? ($optionData['teks'] ?? '');
                            } else {
                                $optionText = $optionData;
                            }
                        @endphp
                        <label wire:key="option-{{ $this->currentQuestion->id }}-{{ $index }}"
                            @click="selectAnswer('{{ $optionValue }}')" class="option cursor-pointer"
                            :class="localAnswer === '{{ $optionValue }}' ? 'selected' : ''">
                            <input type="radio" name="answer" value="{{ $optionValue }}"
                                :checked="localAnswer === '{{ $optionValue }}'">
                            <span class="option-text">{!! $optionText !!}</span>
                        </label>
                    @endforeach
                @else
                    <p style="color: #999; font-style: italic;">Pilihan jawaban belum tersedia.</p>
                @endif
            </div>

            <!-- RAGU-RAGU -->
            <div x-data="{
                localDoubtful: @entangle('currentDoubtful').live,
                toggle() {
                    this.localDoubtful = !this.localDoubtful;
                    $wire.toggleDoubtful();
                }
            }" wire:ignore.self>
                <button type="button" @click="toggle()" class="flag-toggle" :class="localDoubtful ? 'active' : ''">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5.5 3a1.5 1.5 0 00-1.5 1.5v15a1 1 0 102 0v-4.146l1.276-.638a3 3 0 012.536.026l1.715.8a5 5 0 004.018.063l4.091-1.636a1.5 1.5 0 00.936-1.384V4.5A1.5 1.5 0 0018.5 3h-13z" />
                    </svg>
                    <span x-text="localDoubtful ? 'Ditandai ragu-ragu' : 'Tandai ragu-ragu'"></span>
                </button>
            </div>

            <!-- SAVE INDICATOR -->
            <div id="save-indicator" class="save-indicator" style="margin-top: 12px;">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Jawaban tersimpan</span>
            </div>

            <!-- NAVIGASI -->
            <div class="navigation">
                <button type="button" wire:click="prevQuestion" @disabled($currentQuestionIndex === 0) class="secondary" wire:loading.class="opacity-70" wire:target="prevQuestion,nextQuestion">
                    <span wire:loading.remove wire:target="prevQuestion">Sebelumnya</span>
                    <span wire:loading wire:target="prevQuestion">Memuat...</span>
                </button>
                <button type="button" wire:click="nextQuestion" @disabled($currentQuestionIndex === $totalQuestions - 1) class="primary" wire:loading.class="opacity-70" wire:target="prevQuestion,nextQuestion">
                    <span wire:loading.remove wire:target="nextQuestion">Selanjutnya</span>
                    <span wire:loading wire:target="nextQuestion">Memuat...</span>
                </button>
            </div>
        </section>

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <h3>Daftar Soal</h3>

            <div class="legend">
                <div class="legend-item">
                    <span class="box status-belum"></span>
                    <span class="text">Belum</span>
                </div>
                <div class="legend-item">
                    <span class="box status-ragu"></span>
                    <span class="text">Ragu</span>
                </div>
                <div class="legend-item">
                    <span class="box status-jawab"></span>
                    <span class="text">Dijawab</span>
                </div>
            </div>

            <div class="question-list">
                @forelse ($questionStatuses as $status)
                    @php
                        $classes = '';
                        if ($status['answered']) {
                            $classes .= ' answered';
                        }
                        if ($status['doubtful']) {
                            $classes .= ' doubt';
                        }
                        if ($status['current']) {
                            $classes .= ' current';
                        }
                    @endphp
                    <button type="button" wire:click="goToQuestion({{ $status['index'] }})" wire:key="nav-{{ $status['question_id'] }}" class="{{ $classes }}">
                        {{ $status['number'] }}
                    </button>
                @empty
                    <p style="grid-column: span 5; text-align: center; color: #999; font-size: 14px;">Belum ada daftar soal.</p>
                @endforelse
            </div>

            <div class="answer-stats">
                <div>
                    <span>Dijawab</span>
                    <span style="font-weight: 700; color: #2e7d32;">{{ $answeredCount }}</span>
                </div>
                <div>
                    <span>Ragu-ragu</span>
                    <span style="font-weight: 700; color: #f9a825;">{{ $doubtfulCount }}</span>
                </div>
                <div>
                    <span>Belum dijawab</span>
                    <span style="font-weight: 700; color: #333;">{{ $unansweredCount }}</span>
                </div>
                <div>
                    <span>Total Soal</span>
                    <span style="font-weight: 700; color: #000;">{{ $totalQuestions }}</span>
                </div>
            </div>

            <button type="button" wire:click="confirmFinish" class="finish" style="margin-top: 20px;">Selesai Ujian</button>
        </aside>
    </div>
@else
    <div style="background: white; border-radius: 12px; padding: 40px; text-align: center;">
        <h2 style="font-size: 20px; font-weight: 600; color: #c62828; margin-bottom: 8px;">Belum ada soal tersedia.</h2>
        <p style="color: #666;">Silakan hubungi pengawas atau refresh halaman.</p>
    </div>
@endif
