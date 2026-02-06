@push('styles')
    <style>
        * {
            box-sizing: border-box;
        }

        /* ================= LAYOUT ================= */
        .container {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 20px;
            padding: 20px;
            padding-top: 10px;
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 140px);
            align-items: start; /* Prevents question box from stretching to match sidebar height */
        }

        /* ================= SECTION SOAL ================= */
        .question-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .question-number {
            font-weight: 700;
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
        }

        .question-content {
            margin-top: 12px;
        }

        .question-text {
            font-size: 16px;
            margin-bottom: 16px;
            line-height: 1.6;
            color: #333;
        }

        /* ================= OPSI JAWABAN ================= */
        .options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .option {
            border: 1px solid #dcdcdc;
            padding: 10px 12px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            background: #ffffff;
            min-height: auto;
        }

        .option:hover {
            background: #f5f5f5;
            border-color: #999;
        }

        .option.selected {
            background: #e8f5e9;
            border-color: #2e7d32;
        }

        .option input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            flex-shrink: 0;
            accent-color: #2e7d32;
        }

        .option-text {
            flex: 1;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            color: #333;
        }

        .option.selected .option-text {
            color: #1b5e20;
            font-weight: 500;
        }

        /* ================= RAGU-RAGU ================= */
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
            margin-top: 18px;
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
            top: 110px; /* Adjusted for Fixed Header 100px + 10px Gap */
            align-self: start;
            
            /* Full height display (no internal scroll) */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #eee;
        }

        /* Scrollbar styles removed as sidebar is full height */

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

        .status-belum {
            background: #bdbdbd;
        }

        .status-ragu {
            background: #f9a825;
        }

        .status-jawab {
            background: #2e7d32;
        }

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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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

        .answer-stats>div {
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

        /* ================= RESPONSIVE ================= */
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                margin-top: 0;
                top: auto;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 12px;
                gap: 12px;
            }

            .question-section {
                padding: 16px;
            }

            .question-list {
                grid-template-columns: repeat(4, 1fr) !important;
            }
        }

        @media (max-width: 640px) {
            .question-list {
                grid-template-columns: repeat(3, 1fr) !important;
            }

            .navigation {
                flex-direction: column;
            }

            button {
                width: 100%;
            }
        }

        /* ==============whitewhite#9ca3af=== LOADING ANIMATION ================= */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
@endpush

<div>
    @if($step === 'verification')
        <div style="min-height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f9fafb;">
            <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 20px; color: #1f2937;">Verifikasi Kamera</h2>
                <p style="color: #6b7280; margin-bottom: 30px;">Sistem perlu memverifikasi kamera Anda aktif sebelum ujian dimulai.</p>
                
                <div x-data="{ 
                    cameraActive: false,
                    error: null,
                    initCamera() {
                        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                            navigator.mediaDevices.getUserMedia({ video: true })
                                .then(stream => {
                                    this.$refs.video.srcObject = stream;
                                    this.cameraActive = true;
                                    this.error = null;
                                })
                                .catch(err => {
                                    console.error(err);
                                    if(err.name === 'NotAllowedError') {
                                        this.error = 'Akses kamera ditolak. Harap izinkan akses kamera di browser Anda.';
                                    } else {
                                        this.error = 'Tidak dapat mengakses kamera: ' + err.message;
                                    }
                                    this.cameraActive = false;
                                });
                        } else {
                            this.error = 'Browser tidak mendukung akses kamera.';
                        }
                    }
                }" x-init="initCamera()">
                    
                    <div style="position: relative; width: 480px; height: 360px; background: #000; margin: 0 auto 20px auto; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                         <video x-ref="video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                         <div x-show="!cameraActive && !error" style="position: absolute; color: white;">Memuat Kamera...</div>
                         <div x-show="error" x-text="error" style="position: absolute; color: #fca5a5; padding: 20px; text-align: center;"></div>
                    </div>

                    <div style="text-align: center;">
                        <button 
                            type="button"
                            x-show="cameraActive" 
                            wire:click="verifyCameraSuccess"
                            style="padding: 12px 24px; background-color: #2563eb; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 16px;">
                            Kamera Berfungsi - Lanjutkan
                        </button>
                        
                        <button 
                            type="button"
                            x-show="!cameraActive" 
                            @click="initCamera()"
                            style="padding: 12px 24px; background-color: #4b5563; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 16px;">
                            Coba Lagi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @elseif($step === 'rules')
        <div style="min-height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f9fafb;">
            <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); max-width: 800px; width: 100%;">
                <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 20px; color: #1f2937; text-align: center;">Peraturan & Tata Tertib Ujian</h2>
                
                <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: left; color: #374151; line-height: 1.6;">
                    <ol style="margin-left: 20px; list-style-type: decimal;">
                        <li style="margin-bottom: 10px;">Peserta wajib menyalakan kamera selama ujian berlangsung.</li>
                        <li style="margin-bottom: 10px;">Dilarang membuka tab, browser, atau aplikasi lain selain halaman ujian.</li>
                        <li style="margin-bottom: 10px;">Dilarang menggunakan alat bantu hitung, komunikasi, atau catatan selain yang diperbolehkan.</li>
                        <li style="margin-bottom: 10px;">Dilarang meninggalkan tempat duduk selama ujian berlangsung.</li>
                        <li style="margin-bottom: 10px;">Dilarang capture layar atau menyebarkan soal ujian.</li>
                        <li>Segala bentuk kecurangan akan mengakibatkan diskualifikasi.</li>
                    </ol>
                </div>
                
                <div style="margin-bottom: 30px; display: flex; align-items: center; justify-content: center; gap: 12px;">
                    <input type="checkbox" id="agreeRules" wire:model.live="rulesAgreed" style="width: 20px; height: 20px; cursor: pointer;">
                    <label for="agreeRules" style="font-size: 16px; font-weight: 500; cursor: pointer; color: #1f2937;">Saya telah membaca dan menyetujui seluruh peraturan ujian.</label>
                </div>
                
                <div style="text-align: center;">
                    <button 
                        wire:click="startExam"
                        style="padding: 14px 32px; font-size: 16px; font-weight: bold; color: white; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s;"
                        :style="!$wire.rulesAgreed ? 'background-color: #9ca3af; cursor: not-allowed;' : 'background-color: #16a34a;'"
                        :disabled="!$wire.rulesAgreed">
                        Mulai Ujian
                    </button>
                </div>
            </div>
        </div>
    @elseif($step === 'exam')
    {{-- CONFIRM FINISH MODAL --}}
    @if ($showConfirmFinish)
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; width: 400px; padding: 25px; border-radius: 12px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <div style="width: 60px; height: 60px; background: #feebc8; color: #c05621; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 30px; height: 30px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                
                <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #1f2937;">Konfirmasi Selesai</h3>
                <p style="color: #4b5563; margin-bottom: 25px; line-height: 1.5;">Apakah Anda yakin ingin menyelesaikan ujian? <br>Jawaban akan dikunci dan tidak dapat diubah.</p>
                
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button wire:click="cancelFinish" style="background: #e5e7eb; color: #374151; padding: 10px 20px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
                        Batal
                    </button>
                    <button wire:click="submitFinish" style="background: #16a34a; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
                        Ya, Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif
    
    {{-- OVERLAY RESULT MODAL --}}
    @if ($showResults)
        <div class="result-overlay">
            <div class="result-card">
                <div class="result-header">
                    <div class="result-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" style="width: 48px; height: 48px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2>Ujian Selesai</h2>
                    <p>Waktu ujian telah berakhir atau Anda telah menyelesaikan ujian.</p>
                </div>

                <div class="score-display">
                    <span class="score-label">Total Skor</span>
                    <span class="score-value">{{ $resultStats['total_score'] ?? 0 }}</span>
                </div>

                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-label">Total Soal</span>
                        <span class="stat-value">{{ $resultStats['total_questions'] ?? 0 }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Dijawab</span>
                        <span class="stat-value">{{ $resultStats['answered'] ?? 0 }}</span>
                    </div>
                    <div class="stat-item correct">
                        <span class="stat-label">Benar</span>
                        <span class="stat-value">{{ $resultStats['correct'] ?? 0 }}</span>
                    </div>
                    <div class="stat-item wrong">
                        <span class="stat-label">Salah</span>
                        <span class="stat-value">{{ $resultStats['wrong'] ?? 0 }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Kosong</span>
                        <span class="stat-value">{{ $resultStats['unanswered'] ?? 0 }}</span>
                    </div>
                </div>

                <div class="result-actions">
                    <button wire:click="finishAndLogout" class="finish-btn">
                        Selesai & Keluar
                    </button>
                    <!-- Small helper text -->
                    <div style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                        Klik tombol di atas untuk mengakhiri sesi dan keluar dari aplikasi.
                    </div>
                </div>
            </div>
        </div>

        <style>
            .result-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(243, 244, 246, 0.95);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                backdrop-filter: blur(5px);
            }

            .result-card {
                background: white;
                border-radius: 20px;
                padding: 40px;
                width: 100%;
                max-width: 500px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                text-align: center;
                border: 1px solid #e5e7eb;
            }

            .result-icon {
                color: #059669;
                margin-bottom: 20px;
                display: inline-flex;
                background: #ecfdf5;
                padding: 15px;
                border-radius: 50%;
            }

            .result-header h2 {
                color: #111827;
                font-size: 24px;
                font-weight: 700;
                margin-bottom: 10px;
            }

            .result-header p {
                color: #6b7280;
                margin-bottom: 30px;
                font-size: 14px;
            }

            .score-display {
                background: #f9fafb;
                padding: 20px;
                border-radius: 12px;
                margin-bottom: 30px;
                border: 1px solid #f3f4f6;
            }

            .score-label {
                display: block;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #6b7280;
                margin-bottom: 5px;
            }

            .score-value {
                font-size: 48px;
                font-weight: 800;
                color: #059669;
                /* Green score */
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
                margin-bottom: 30px;
            }

            .stat-item {
                background: white;
                padding: 10px;
                border-radius: 8px;
                border: 1px solid #e5e7eb;
            }

            .stat-item.correct {
                border-top: 3px solid #059669;
                background: #f0fdf4;
            }

            .stat-item.wrong {
                border-top: 3px solid #ef4444;
                background: #fef2f2;
            }

            .stat-label {
                display: block;
                font-size: 11px;
                color: #6b7280;
                margin-bottom: 4px;
                font-weight: 600;
            }

            .stat-value {
                font-size: 18px;
                display: block;
                font-weight: 700;
                color: #1f2937;
            }

            .finish-btn {
                background: #1f2937;
                color: white;
                width: 100%;
                padding: 14px;
                border-radius: 8px;
                font-weight: 600;
                border: none;
                cursor: pointer;
                transition: background 0.2s;
                font-size: 16px;
            }

            .finish-btn:hover {
                background: #111827;
            }
        </style>
    @endif

    @if ($totalQuestions > 0 && $this->currentQuestion)
        <div class="container"
            @if ($showResults) style="filter: blur(5px); pointer-events: none;" @endif>
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
                            <path
                                d="M5.5 3a1.5 1.5 0 00-1.5 1.5v15a1 1 0 102 0v-4.146l1.276-.638a3 3 0 012.536.026l1.715.8a5 5 0 004.018.063l4.091-1.636a1.5 1.5 0 00.936-1.384V4.5A1.5 1.5 0 0018.5 3h-13z" />
                        </svg>
                        <span x-text="localDoubtful ? 'Ditandai ragu-ragu' : 'Tandai ragu-ragu'"></span>
                    </button>
                </div>

                <!-- SAVE INDICATOR -->
                <div id="save-indicator" class="save-indicator" style="margin-top: 12px;">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Jawaban tersimpan</span>
                </div>

                <!-- NAVIGASI -->
                <div class="navigation">
                    <button type="button" wire:click="prevQuestion" @disabled($currentQuestionIndex === 0) class="secondary"
                        wire:loading.class="opacity-70" wire:target="prevQuestion,nextQuestion">
                        <span wire:loading.remove wire:target="prevQuestion">Sebelumnya</span>
                        <span wire:loading wire:target="prevQuestion">Memuat...</span>
                    </button>
                    <button type="button" wire:click="nextQuestion" @disabled($currentQuestionIndex === $totalQuestions - 1) class="primary"
                        wire:loading.class="opacity-70" wire:target="prevQuestion,nextQuestion">
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
                        <button type="button" wire:click="goToQuestion({{ $status['index'] }})"
                            wire:key="nav-{{ $status['question_id'] }}" class="{{ $classes }}">
                            {{ $status['number'] }}
                        </button>
                    @empty
                        <p style="grid-column: span 5; text-align: center; color: #999; font-size: 14px;">Belum ada
                            daftar soal.</p>
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
            <h2 style="font-size: 20px; font-weight: 600; color: #c62828; margin-bottom: 8px;">Belum ada soal tersedia.
            </h2>
            <p style="color: #666;">Silakan hubungi pengawas atau refresh halaman.</p>
        </div>
    @endif

    @endif
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
            var fiveMinuteWarningShown = false;

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
                        // Update timer box class based on state
                        container.classList.remove('timer-warning');
                        if (state === 'warning' || state === 'danger') {
                            container.classList.add('timer-warning');
                        }
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

                        // UX: Disable interactions immediately
                        document.querySelectorAll('input, button, a.nav-btn').forEach(function(el) {
                            el.disabled = true;
                            el.style.pointerEvents = 'none';
                            el.style.opacity = '0.6';
                        });

                        // Clear interval to stop ticking
                        if (timerInterval) clearInterval(timerInterval);

                        // Call Livewire to handle expiration and show results
                        @this.call('handleTimeExpiry');

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

                    // 5-minute warning notification
                    if (remaining <= 5 * 60 * 1000 && !fiveMinuteWarningShown) {
                        fiveMinuteWarningShown = true;
                        
                        var warningDiv = document.createElement('div');
                        warningDiv.id = 'timer-warning-notif';
                        warningDiv.innerHTML = `
                            <div style="
                                position: fixed; 
                                top: 20px; 
                                left: 50%; 
                                transform: translateX(-50%); 
                                background: #c62828; 
                                color: white; 
                                padding: 16px 24px; 
                                border-radius: 12px; 
                                box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
                                z-index: 9999; 
                                display: flex; 
                                align-items: center; 
                                gap: 16px; 
                                min-width: 320px;
                                animation: slideDown 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                            ">
                                <span style="background: rgba(255,255,255,0.2); padding: 8px; border-radius: 50%;">
                                    <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                                <div style="flex: 1;">
                                    <strong style="display: block; font-size: 16px; margin-bottom: 4px;">Sisa Waktu 5 Menit!</strong>
                                    <span style="font-size: 14px; opacity: 0.9;">Segera selesaikan ujian Anda.</span>
                                </div>
                                <button onclick="document.getElementById('timer-warning-notif').remove()" style="
                                    background: none; 
                                    border: none; 
                                    color: white; 
                                    padding: 4px;
                                    cursor: pointer;
                                    opacity: 0.8;
                                    transition: opacity 0.2s;
                                ">
                                    <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <style>
                                @keyframes slideDown {
                                    from { top: -100px; opacity: 0; }
                                    to { top: 20px; opacity: 1; }
                                }
                            </style>
                        `;
                        document.body.appendChild(warningDiv);
                        
                        // Auto-dismiss after 10 seconds
                        setTimeout(function() {
                            if (document.body.contains(warningDiv)) {
                                warningDiv.remove();
                            }
                        }, 10000);
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
                
                // NEW: Listen for exam start event to kickoff timer
                Livewire.on('exam-started', function(data) {
                    console.log('Exam Started Event:', data);
                    
                    var endTime = null;
                    // Handle object {endTime: ...} or array [{endTime: ...}]
                    if (data && data.endTime) {
                        endTime = data.endTime;
                    } else if (Array.isArray(data) && data.length > 0 && data[0].endTime) {
                         endTime = data[0].endTime;
                    } else if (typeof data === 'string') {
                        endTime = data;
                    }
                    
                    var timerEl = document.getElementById('exam-timer');
                    
                    if (timerEl && endTime) {
                        // Manually update the attribute since wire:ignore prevents it
                        timerEl.setAttribute('data-end-time', endTime);
                        
                        // Restart timer
                        initTimer();
                    } else {
                        console.warn('Timer element not found or invalid end time', { timerEl: !!timerEl, endTime: endTime });
                    }
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
