@if ($totalQuestions > 0 && count($questionsJson) > 0)
    <style>
        body { background-color: #f9fafb; }
        /* Prevent flicker */
        [x-cloak] { display: none !important; }
    </style>
    
    <script>
    if (!window.createExamClient) {
        window.createExamClient = (initialQuestions, initialAnswers, wireEntangle) => {
            return {
                questions: initialQuestions || [],
                answersMap: initialAnswers || {},
                currentIndex: wireEntangle, 
                
                init() {
                    // Start camera
                    this.startCamera();
                },

                get totalQuestions() { return this.questions.length; },
                
                get currentQuestion() {
                    return (this.questions && this.questions[this.currentIndex]) || { 
                        id: null, 
                        question_text: '', 
                        options: [] 
                    };
                },
                
                get normalizedOptions() {
                    let options = this.currentQuestion.options;
                    if (!options) return [];
                    
                    if (typeof options === 'string') {
                        try { options = JSON.parse(options); } catch(e) { options = []; }
                    }
                    
                    let result = [];
                    if (Array.isArray(options)) {
                        options.forEach((opt, idx) => {
                            let text = '';
                            if (typeof opt === 'string') text = opt;
                            else if (opt && opt.answer_text) text = opt.answer_text;
                            else if (opt && opt.teks) text = opt.teks;
                            
                            result.push({ value: String(idx), text: text });
                        });
                    }
                    return result;
                },
                
                get currentAnswerData() {
                    return this.answersMap[this.currentQuestion.id] || { answer: null, doubtful: false };
                },
                
                get isDoubtful() {
                    return !!this.currentAnswerData.doubtful;
                },
                
                get stats() {
                    let answered = 0;
                    let doubtful = 0;
                    let total = this.totalQuestions;
                    
                    Object.values(this.answersMap).forEach(a => {
                        if (a.doubtful) doubtful++;
                        else if (a.answer !== null && a.answer !== '') answered++;
                    });
                    
                    return {
                        answered: answered,
                        doubtful: doubtful,
                        unanswered: total - answered - doubtful
                    };
                },

                isAnswerSelected(val) {
                    let saved = this.currentAnswerData.answer;
                    return saved !== null && saved === String(val);
                },
                
                selectExample(val) {
                    let valStr = String(val);
                    let qId = this.currentQuestion.id;
                    
                    if (!this.answersMap[qId]) this.answersMap[qId] = { answer: null, doubtful: false };
                    this.answersMap[qId].answer = valStr;
                    
                    this.$wire.saveAnswerClient(qId, valStr);
                },
                
                toggleDoubtful() {
                    let qId = this.currentQuestion.id;
                    if (!this.answersMap[qId]) this.answersMap[qId] = { answer: null, doubtful: false };
                    
                    let newState = !this.answersMap[qId].doubtful;
                    this.answersMap[qId].doubtful = newState;
                    
                    this.$wire.toggleDoubtfulClient(qId, newState);
                },
                
                next() {
                    if (this.currentIndex < this.totalQuestions - 1) {
                        this.currentIndex++;
                        this.scrollToTop();
                    }
                },
                
                prev() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                        this.scrollToTop();
                    }
                },
                
                jumpTo(idx) {
                    this.currentIndex = idx;
                    this.scrollToTop();
                },
                
                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                
                getSidebarClass(qId, idx) {
                    let data = this.answersMap[qId];
                    let classes = [];
                    
                    if (data && data.doubtful) classes.push('doubt');
                    else if (data && data.answer !== null && data.answer !== '') classes.push('answered');
                    
                    if (idx === this.currentIndex) classes.push('current');
                    
                    return classes.join(' ');
                },
                
                async startCamera() {
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        try {
                            if (window.activeExamStream) {
                                window.activeExamStream.getTracks().forEach(track => track.stop());
                            }
                            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                            if (this.$refs.proctorVideo) {
                                this.$refs.proctorVideo.srcObject = stream;
                            }
                            window.activeExamStream = stream;
                        } catch (err) {
                            console.error("Proctoring Camera Failed:", err);
                        }
                    }
                }
            };
        };
    }
    </script>
    
    <div class="container" 
        x-data="createExamClient(@js($questionsJson), @js($initialAnswers), @entangle('currentQuestionIndex').live)"
        x-cloak
        wire:ignore.self
        @if ($showResults) style="filter: blur(5px); pointer-events: none;" @endif>

        <!-- Hidden Video for Proctoring -->
        <video x-ref="proctorVideo" autoplay playsinline muted style="display: none;"></video>
        
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
                    <label class="option cursor-pointer"
                        :class="isAnswerSelected(index) ? 'selected' : ''"
                        @click.prevent="selectExample(index)">
                        
                        <input type="radio" 
                            :name="'q-' + currentQuestion.id" 
                            :checked="isAnswerSelected(index)"
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
                <button type="button" @click="toggleDoubtful()" class="flag-toggle" :class="isDoubtful ? 'active' : ''">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5.5 3a1.5 1.5 0 00-1.5 1.5v15a1 1 0 102 0v-4.146l1.276-.638a3 3 0 012.536.026l1.715.8a5 5 0 004.018.063l4.091-1.636a1.5 1.5 0 00.936-1.384V4.5A1.5 1.5 0 0018.5 3h-13z" />
                    </svg>
                    <span x-text="isDoubtful ? 'Ditandai ragu-ragu' : 'Tandai ragu-ragu'"></span>
                </button>
            </div>

            <!-- NAVIGASI -->
            <div class="navigation">
                <button type="button" @click="prev()" :disabled="currentIndex === 0" class="secondary" :class="currentIndex === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                    <span>Sebelumnya</span>
                </button>
                <button type="button" @click="next()" :disabled="currentIndex === totalQuestions - 1" class="primary" :class="currentIndex === totalQuestions - 1 ? 'opacity-50 cursor-not-allowed' : ''">
                    <span>Selanjutnya</span>
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
            </div>

            <div class="question-list">
                <template x-for="(q, idx) in questions" :key="q.id">
                    <button type="button" 
                        @click="jumpTo(idx)"
                        :class="getSidebarClass(q.id, idx)">
                        <span x-text="idx + 1"></span>
                    </button>
                </template>
            </div>

            <div class="answer-stats">
                <div><span>Dijawab</span><span style="font-weight: 700; color: #2e7d32;" x-text="stats.answered"></span></div>
                <div><span>Ragu-ragu</span><span style="font-weight: 700; color: #f9a825;" x-text="stats.doubtful"></span></div>
                <div><span>Belum dijawab</span><span style="font-weight: 700; color: #333;" x-text="stats.unanswered"></span></div>
                <div><span>Total Soal</span><span style="font-weight: 700; color: #000;" x-text="totalQuestions"></span></div>
            </div>

            <button type="button" wire:click="confirmFinish" class="finish" style="margin-top: 20px;">Selesai Ujian</button>
        </aside>
    </div>

    <div class="result-stats" style="margin-top: 20px;"></div>
@else
    <div style="background: white; border-radius: 12px; padding: 40px; text-align: center;">
        <h2 style="font-size: 20px; font-weight: 600; color: #c62828; margin-bottom: 8px;">Belum ada soal tersedia.</h2>
        <p style="color: #666;">Silakan hubungi pengawas atau refresh halaman.</p>
    </div>
@endif
