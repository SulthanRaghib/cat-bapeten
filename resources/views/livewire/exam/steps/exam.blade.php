@if ($totalQuestions > 0 && count($questionsJson) > 0)
    <style>
        body {
            background-color: #f9fafb;
        }

        /* Prevent flicker */
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="container" x-data="createExamClient(@js($questionsJson), @js($initialAnswers), @entangle('currentQuestionIndex').live)" x-cloak wire:ignore.self
        @if ($showResults) style="filter: blur(5px); pointer-events: none;" @endif>

        <!-- Hidden Video Removed -->

        <!-- AREA SOAL -->
        <section class="question-section">
            <div class="question-number">Soal <span x-text="currentIndex + 1"></span></div>

            <!-- KONTEN SOAL -->
            <div class="question-content">
                <p class="question-text" x-html="currentQuestion.question_text"></p>
            </div>

            <!-- OPSI JAWABAN -->
            <div class="options">
                <template x-for="(option, index) in normalizedOptions" :key="index">
                    <label class="option cursor-pointer" :class="isAnswerSelected(index) ? 'selected' : ''"
                        @click.prevent="selectExample(index)">

                        <input type="radio" :name="'q-' + currentQuestion.id" :checked="isAnswerSelected(index)"
                            style="pointer-events: none">

                        <span class="option-text" x-html="option.text"></span>
                    </label>
                </template>

                <template x-if="normalizedOptions.length === 0">
                    <p style="color: #999; font-style: italic;">Pilihan jawaban belum tersedia.</p>
                </template>
            </div>

            <!-- RAGU-RAGU -->
            <div>
                <button type="button" @click="toggleDoubtful()" class="flag-toggle"
                    :class="isDoubtful ? 'active' : ''">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M5.5 3a1.5 1.5 0 00-1.5 1.5v15a1 1 0 102 0v-4.146l1.276-.638a3 3 0 012.536.026l1.715.8a5 5 0 004.018.063l4.091-1.636a1.5 1.5 0 00.936-1.384V4.5A1.5 1.5 0 0018.5 3h-13z" />
                    </svg>
                    <span x-text="isDoubtful ? 'Ditandai ragu-ragu' : 'Tandai ragu-ragu'"></span>
                </button>
            </div>

            <!-- NAVIGASI -->
            <div class="navigation">
                <button type="button" @click="prev()" :disabled="currentIndex === 0" class="secondary"
                    :class="currentIndex === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                    style="padding: 10px 16px; font-size: 14px;">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            style="width: 18px; height: 18px;">
                            <path fill-rule="evenodd"
                                d="M11.78 4.22a.75.75 0 010 1.06L7.06 10l4.72 4.72a.75.75 0 11-1.06 1.06l-5.25-5.25a.75.75 0 010-1.06l5.25-5.25a.75.75 0 011.06 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Sebelumnya</span>
                    </span>
                </button>
                <button type="button" @click="next()" :disabled="currentIndex === totalQuestions - 1" class="primary"
                    :class="currentIndex === totalQuestions - 1 ? 'opacity-50 cursor-not-allowed' : ''"
                    style="padding: 10px 16px; font-size: 14px;">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <span>Selanjutnya</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            style="width: 18px; height: 18px;">
                            <path fill-rule="evenodd"
                                d="M8.22 4.22a.75.75 0 011.06 0l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 01-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 010-1.06z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
            </div>
        </section>

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <h3>Daftar Soal</h3>

            <div class="legend">
                <div class="legend-item"><span class="box status-belum"></span><span class="text">Belum</span></div>
                <div class="legend-item"><span class="box status-ragu"></span><span class="text">Ragu</span></div>
                <div class="legend-item"><span class="box status-jawab"></span><span class="text">Dijawab</span></div>
                <div class="legend-item"><span class="box status-aktif"></span><span class="text">Sedang dikerjakan</span></div>
            </div>

            <div class="question-list">
                <template x-for="(q, idx) in questions" :key="q.id">
                    <button type="button" @click="jumpTo(idx)" :class="getSidebarClass(q.id, idx)">
                        <span x-text="idx + 1"></span>
                    </button>
                </template>
            </div>

            <div class="answer-stats">
                <div><span>Dijawab</span><span style="font-weight: 700; color: #2e7d32;" x-text="stats.answered"></span>
                </div>
                <div><span>Ragu-ragu</span><span style="font-weight: 700; color: #f9a825;"
                        x-text="stats.doubtful"></span></div>
                <div><span>Belum dijawab</span><span style="font-weight: 700; color: #333;"
                        x-text="stats.unanswered"></span></div>
                <div><span>Total Soal</span><span style="font-weight: 700; color: #000;" x-text="totalQuestions"></span>
                </div>
            </div>

            <button type="button" @click="showConfirmFinishLocal = true" class="finish" style="margin-top: 20px;">Selesai
                Ujian</button>
        </aside>
    </div>

    <div class="result-stats" style="margin-top: 20px;"></div>
@else
    <div style="background: white; border-radius: 12px; padding: 40px; text-align: center;">
        <h2 style="font-size: 20px; font-weight: 600; color: #c62828; margin-bottom: 8px;">Belum ada soal tersedia.</h2>
        <p style="color: #666;">Silakan hubungi pengawas atau refresh halaman.</p>
    </div>
@endif
