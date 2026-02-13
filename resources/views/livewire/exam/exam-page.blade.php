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
            align-items: start;
            /* Prevents question box from stretching to match sidebar height */
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
            top: 110px;
            /* Adjusted for Fixed Header 100px + 10px Gap */
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
    <span wire:poll.keep-alive.5s="monitorSessionStatus" style="display: none;"></span>
    @if ($step === 'verification')
        <div style="position: fixed; top: 100px; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: #f9fafb; z-index: 50;">
            <div style="background: white; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); width: 100%; max-width: 850px; height: auto; max-height: 500px; overflow: hidden; display: flex; flex-direction: row; margin: 20px;">
                
                {{-- Left Side: Instructions & Actions --}}
                <div style="flex: 1; padding: 32px; display: flex; flex-direction: column; justify-content: center; min-width: 320px; border-right: 1px solid #f3f4f6;">
                    <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 8px; color: #1f2937;">Verifikasi Kamera</h2>
                    <p style="color: #6b7280; margin-bottom: 24px; font-size: 14px; line-height: 1.6;">
                        Sistem perlu memverifikasi kamera Anda aktif dan berfungsi dengan baik sebelum ujian dapat dimulai.
                    </p>

                    <div wire:ignore x-data="{
                        cameraActive: false,
                        error: null,
                        initCamera() {
                            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                                navigator.mediaDevices.getUserMedia({ video: true })
                                    .then(stream => {
                                        this.$refs.video.srcObject = stream;
                                        window.activeExamStream = stream; // Store globally
                                        this.cameraActive = true;
                                        this.error = null;
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        if (err.name === 'NotAllowedError') {
                                            this.error = 'Akses kamera ditolak.';
                                        } else {
                                            this.error = 'Error: ' + err.message;
                                        }
                                        this.cameraActive = false;
                                    });
                            } else {
                                this.error = 'Browser tidak support.';
                            }
                        }
                    }" x-init="initCamera()" style="width: 100%;">

                        <div style="margin-top: auto;">
                            <!-- Changed to @click for Alpine-Livewire interop inside wire:ignore -->
                            <button type="button" x-show="cameraActive" @click="$wire.verifyCameraSuccess()"
                                style="width: 100%; padding: 12px 16px; background-color: #2563eb; color: white; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="M22 4L12 14.01l-3-3"></path></svg>
                                Lanjutkan Ujian
                            </button>

                            <button type="button" x-show="!cameraActive" @click="initCamera()"
                                style="width: 100%; padding: 12px 16px; background-color: #4b5563; color: white; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                Coba Lagi
                            </button>
                            
                            <div x-show="error" x-text="error" style="margin-top: 10px; font-size: 12px; color: #ef4444; background: #fee2e2; padding: 8px; border-radius: 4px;"></div>
                        </div>

                        {{-- Hidden on desktop and moved --}}
                        <template x-teleport="#video-teleport-target">
                             <div style="width: 100%; height: 100%; background: #000; position: relative;">
                                <video x-ref="video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                                <div x-show="!cameraActive && !error" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 14px; display: flex; flex-direction: column; align-items: center; gap: 8px; pointer-events: none;">
                                    <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>
                                    <span>Menghubungkan...</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Right Side: Video --}}
                <div wire:ignore id="video-teleport-target" style="flex: 1.3; background: #000; display: flex; flex-direction: column; min-height: 280px;">
                    {{-- Video teleported here --}}
                </div>
            </div>
            
            <style>
                /* Remove body scroll when in verification step */
                body { overflow: hidden; }
                
                @media (max-width: 768px) {
                    body { overflow: auto; }
                    div[style*="flex-direction: row"] {
                        flex-direction: column !important;
                        height: auto !important;
                        max-height: none !important;
                    }
                    div[style*="border-right: 1px solid"] {
                        border-right: none !important;
                        border-bottom: 1px solid #f3f4f6 !important;
                    }
                    #video-teleport-target {
                        height: 250px;
                        flex: none !important;
                    }
                }
            </style>
        </div>
    @elseif($step === 'rules')
        <div style="max-width: 900px; margin: 0 auto; padding: 30px 20px 0 20px; min-height: 100vh;">
            
            {{-- Header --}}
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="font-size: 28px; font-weight: 800; margin: 0 0 12px 0; color: #1f2937; letter-spacing: -0.025em;">
                    Peraturan & Tata Tertib Ujian
                </h2>
                <p style="margin: 0; color: #6b7280; font-size: 16px; max-width: 600px; margin: 0 auto;">Harap membaca dan mematuhi seluruh peraturan di bawah ini demi kelancaran proses ujian Anda.</p>
            </div>

            {{-- Content List --}}
            <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 50px;">
                
                <!-- Item 1 -->
                <div style="display: flex; gap: 20px; padding: 24px; background: white; border: 1px solid #e2e8f0; border-radius: 16px; align-items: flex-start; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <div style="color: #0284c7; margin-top: 4px; flex-shrink: 0; background: #f0f9ff; padding: 12px; border-radius: 12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 28px; height: 28px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </div>
                    <div>
                        <h4 style="font-size: 18px; font-weight: 700; margin: 0 0 6px 0; color: #1e293b;">1. Wajib Kamera On</h4>
                        <p style="font-size: 15px; margin: 0; color: #64748b; line-height: 1.6;">Peserta wajib menyalakan kamera dan memastikan wajah terlihat jelas oleh pengawas sepanjang waktu ujian.</p>
                    </div>
                </div>

                <!-- Item 2 -->
                <div style="display: flex; gap: 20px; padding: 24px; background: white; border: 1px solid #e2e8f0; border-radius: 16px; align-items: flex-start; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <div style="color: #0284c7; margin-top: 4px; flex-shrink: 0; background: #f0f9ff; padding: 12px; border-radius: 12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 28px; height: 28px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    </div>
                    <div>
                        <h4 style="font-size: 18px; font-weight: 700; margin: 0 0 6px 0; color: #1e293b;">2. Dilarang Membuka Tab Lain</h4>
                        <p style="font-size: 15px; margin: 0; color: #64748b; line-height: 1.6;">Dilarang keras membuka browser tab baru, window baru, atau aplikasi lain selain halaman ujian ini.</p>
                    </div>
                </div>

                <!-- Item 3 -->
                <div style="display: flex; gap: 20px; padding: 24px; background: white; border: 1px solid #e2e8f0; border-radius: 16px; align-items: flex-start; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <div style="color: #0284c7; margin-top: 4px; flex-shrink: 0; background: #f0f9ff; padding: 12px; border-radius: 12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 28px; height: 28px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25v-.008zm2.25-2.25h.008v.008H10.5v-.008zm0 2.25h.008v.008H10.5v-.008zm2.25-2.25h.008v.008H12.75v-.008zm0 2.25h.008v.008H12.75v-.008zm2.25-2.25h.008v.008H15v-.008zm0 2.25h.008v.008H15v-.008zM7.5 10.5h3v-3h-3v3zm0-3h3v-3h-3v3zm-4.5 9h18v6h-18v-6z" /></svg>
                    </div>
                    <div>
                        <h4 style="font-size: 18px; font-weight: 700; margin: 0 0 6px 0; color: #1e293b;">3. Dilarang Alat Bantu</h4>
                        <p style="font-size: 15px; margin: 0; color: #64748b; line-height: 1.6;">Tidak diperkenankan menggunakan kalkulator, catatan fisik, buku, smartphone, atau alat bantu hitung lainnya.</p>
                    </div>
                </div>

                <!-- Item 4 -->
                <div style="display: flex; gap: 20px; padding: 24px; background: white; border: 1px solid #e2e8f0; border-radius: 16px; align-items: flex-start; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <div style="color: #0284c7; margin-top: 4px; flex-shrink: 0; background: #f0f9ff; padding: 12px; border-radius: 12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 28px; height: 28px;"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.675.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.675-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" /></svg>
                    </div>
                    <div>
                        <h4 style="font-size: 18px; font-weight: 700; margin: 0 0 6px 0; color: #1e293b;">4. Tetap di Tempat</h4>
                        <p style="font-size: 15px; margin: 0; color: #64748b; line-height: 1.6;">Dilarang meninggalkan tempat duduk atau menghilang dari pantauan kamera selama ujian berlangsung.</p>
                    </div>
                </div>

                 <!-- Item 5 -->
                <div style="display: flex; gap: 20px; padding: 24px; background: white; border: 1px solid #e2e8f0; border-radius: 16px; align-items: flex-start; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <div style="color: #0284c7; margin-top: 4px; flex-shrink: 0; background: #f0f9ff; padding: 12px; border-radius: 12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 28px; height: 28px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg>
                    </div>
                    <div>
                        <h4 style="font-size: 18px; font-weight: 700; margin: 0 0 6px 0; color: #1e293b;">5. Dilarang Screenshot</h4>
                        <p style="font-size: 15px; margin: 0; color: #64748b; line-height: 1.6;">Dilarang memotret, screenshot, atau merekam layar soal ujian dengan cara apapun.</p>
                    </div>
                </div>

                 <!-- Item 6 -->
                <div style="display: flex; gap: 20px; padding: 24px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 16px; align-items: flex-start; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <div style="color: #e11d48; margin-top: 4px; flex-shrink: 0; background: #ffe4e6; padding: 12px; border-radius: 12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 28px; height: 28px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    </div>
                    <div>
                        <h4 style="font-size: 18px; font-weight: 700; margin: 0 0 6px 0; color: #9f1239;">6. Sanksi Tegas</h4>
                        <p style="font-size: 15px; margin: 0; color: #9f1239; line-height: 1.6;">Pelanggaran terhadap tata tertib di atas dapat mengakibatkan diskualifikasi atau pembatalan hasil ujian secara sepihak.</p>
                    </div>
                </div>

            </div>

            {{-- Footer Action --}}
            <div style="margin-top: 30px; padding-bottom: 60px; display: flex; flex-direction: column; align-items: center;">
                 <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 24px; max-width: 900px;">
                    <input type="checkbox" id="agreeRules" wire:model.live="rulesAgreed"
                        style="width: 18px; height: 18px; cursor: pointer; flex-shrink: 0; accent-color: #0284c7;">
                    <label for="agreeRules" style="font-size: 14px; font-weight: 500; cursor: pointer; color: #334155; line-height: 1.2;">
                        Saya menyatakan telah membaca, memahami, dan bersedia mematuhi seluruh peraturan serta tata tertib ujian yang berlaku.
                    </label>
                </div>

                <button wire:click="startExam"
                    style="padding: 16px 80px; font-size: 16px; font-weight: 700; color: white; border-radius: 50px; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"
                    :style="!$wire.rulesAgreed ? 'background-color: #94a3b8; cursor: not-allowed; transform: scale(0.98); opacity: 0.8;' : 'background-color: #0284c7; transform: scale(1); opacity: 1; box-shadow: 0 10px 15px -3px rgba(2, 132, 199, 0.3);'"
                    :disabled="!$wire.rulesAgreed"
                    onmouseover="if(this.disabled) return; this.style.backgroundColor='#0369a1'; this.style.transform='scale(1.02)'"
                    onmouseout="if(this.disabled) return; this.style.backgroundColor='#0284c7'; this.style.transform='scale(1)'">
                    Mulai Kerjakan Ujian
                </button>
            </div>
        </div>
    @elseif($step === 'exam')
        {{-- CONFIRM FINISH MODAL --}}
        @if ($showConfirmFinish)
            <div
                style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div
                    style="background: white; width: 400px; padding: 25px; border-radius: 12px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                    <div
                        style="width: 60px; height: 60px; background: #feebc8; color: #c05621; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" style="width: 30px; height: 30px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>

                    <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #1f2937;">Konfirmasi
                        Selesai</h3>
                    <p style="color: #4b5563; margin-bottom: 25px; line-height: 1.5;">Apakah Anda yakin ingin
                        menyelesaikan ujian? <br>Jawaban akan dikunci dan tidak dapat diubah.</p>

                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button wire:click="cancelFinish"
                            style="background: #e5e7eb; color: #374151; padding: 10px 20px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
                            Batal
                        </button>
                        <button wire:click="submitFinish"
                            style="background: #16a34a; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
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
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px;">
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
                        <button type="button" @click="toggle()" class="flag-toggle"
                            :class="localDoubtful ? 'active' : ''">
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
                        <button type="button" wire:click="prevQuestion" @disabled($currentQuestionIndex === 0)
                            class="secondary" wire:loading.class="opacity-70"
                            wire:target="prevQuestion,nextQuestion">
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

                    <button type="button" wire:click="confirmFinish" class="finish"
                        style="margin-top: 20px;">Selesai Ujian</button>
                </aside>
            </div>
        @else
            <div style="background: white; border-radius: 12px; padding: 40px; text-align: center;">
                <h2 style="font-size: 20px; font-weight: 600; color: #c62828; margin-bottom: 8px;">Belum ada soal
                    tersedia.
                </h2>
                <p style="color: #666;">Silakan hubungi pengawas atau refresh halaman.</p>
            </div>
        @endif
    @elseif($step === 'result')
        <div
            style="position: fixed; inset: 0; background: #f9fafb; z-index: 99999; overflow-y: auto; font-family: 'Poppins', sans-serif;">
            <div style="min-height: 100%; display: flex; align-items: center; justify-content: center; padding: 10px;">
                <div style="width: 100%; max-width: 480px;">
                    <div
                        style="background: white; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); overflow: hidden; position: relative;">
                        <!-- Brand Accent Bar -->
                        <div style="height: 6px; background: #f9a825;"></div>

                        <div style="padding: 40px;">
                            <div style="text-align: center; margin-bottom: 35px;">
                                <!-- Logo Instansi -->
                                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo"
                                    style="height: 60px; margin-bottom: 24px; object-fit: contain;">

                                <h2
                                    style="font-size: 24px; font-weight: 800; color: #1f2937; margin: 0 0 8px 0; letter-spacing: -0.5px;">
                                    Ujian Selesai</h2>
                                <p style="color: #6b7280; font-size: 14px; margin: 0; line-height: 1.5;">Jawaban Anda
                                    telah berhasil disimpan.<br>Terima kasih telah berpartisipasi.</p>
                            </div>

                            <!-- Score Card -->
                            <div
                                style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px 20px; text-align: center; margin-bottom: 32px; position: relative;">
                                <div
                                    style="color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px;">
                                    Nilai Akhir</div>
                                <div
                                    style="font-size: 72px; font-weight: 900; color: black; line-height: 1; letter-spacing: -2px;">
                                    {{ $resultStats['total_score'] ?? 0 }}
                                </div>
                            </div>

                            <div style="text-align: center;">
                                <button wire:click="finishAndLogout"
                                    style="width: 100%; padding: 16px 24px; background-color: #f9a825; color: white; border-radius: 12px; border: none; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"
                                    onmouseover="this.style.backgroundColor='#f9a825'; this.style.transform='translateY(-1px)'"
                                    onmouseout="this.style.backgroundColor='#f9a825'; this.style.transform='translateY(0)'">
                                    <span>Kembali ke Halaman Utama</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

            function lockExamUI() {
                document.querySelectorAll('input, button, a.nav-btn').forEach(function(el) {
                    el.disabled = true;
                    el.style.pointerEvents = 'none';
                    el.style.opacity = '0.6';
                });
            }

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
                        lockExamUI();

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
                        console.warn('Timer element not found or invalid end time', {
                            timerEl: !!timerEl,
                            endTime: endTime
                        });
                    }
                });

                Livewire.on('exam-stopped', function(data) {
                    if (timerInterval) {
                        clearInterval(timerInterval);
                        timerInterval = null;
                    }

                    lockExamUI();

                    var timerEl = document.getElementById('exam-timer');

                    if (timerEl) {
                        var forcedEnd = null;

                        if (data && data.endTime) {
                            forcedEnd = data.endTime;
                        } else if (Array.isArray(data) && data.length > 0 && data[0].endTime) {
                            forcedEnd = data[0].endTime;
                        }

                        if (!forcedEnd) {
                            forcedEnd = new Date().toISOString();
                        }

                        timerEl.setAttribute('data-end-time', forcedEnd);
                        timerEl.textContent = '00:00';
                        timerEl.setAttribute('data-state', 'danger');

                        var container = timerEl.closest('[data-timer-container]');
                        if (container) {
                            container.setAttribute('data-state', 'danger');
                            container.classList.add('timer-warning');
                        }
                    }
                });

                Livewire.on('question-changed', function() {
                    requestAnimationFrame(initialiseEnhancements);
                });

                Livewire.on('exam-finished', function() {
                    if (timerInterval) {
                        clearInterval(timerInterval);
                        timerInterval = null;
                    }

                    // Stop global stream if exists
                    if (window.activeExamStream) {
                        window.activeExamStream.getTracks().forEach(track => track.stop());
                        window.activeExamStream = null;
                    }

                    // Stop all camera streams
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({
                                video: true,
                                audio: false
                            })
                            .then(function(stream) {
                                stream.getTracks().forEach(function(track) {
                                    track.stop();
                                });
                            })
                            .catch(function(e) {
                                // Ignore errors if no stream active
                            });
                    }

                    // Also try to stop any video elements on page
                    document.querySelectorAll('video').forEach(function(vid) {
                        if (vid.srcObject) {
                            vid.srcObject.getTracks().forEach(track => track.stop());
                        }
                        vid.pause();
                        vid.src = "";
                    });
                });
            });

            // Fallback for initial page load
            document.addEventListener('livewire:navigated', function() {
                initialiseEnhancements();
            });
        })();
    </script>
@endpush
