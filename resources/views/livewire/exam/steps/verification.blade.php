<div style="position: fixed; top: 100px; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: #f9fafb; z-index: 50; padding: 20px;">
    <div class="verification-card" style="background: white; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); width: 100%; max-width: 850px; height: auto; max-height: 500px; overflow: hidden; display: flex; flex-direction: row; margin: 0 auto;">

        {{-- Left Side: Instructions & Actions --}}
        <div class="verification-content" style="flex: 1; padding: 32px; display: flex; flex-direction: column; justify-content: center; min-width: 320px; border-right: 1px solid #f3f4f6;">
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
                    <button type="button" x-show="cameraActive" @click="$wire.verifyCameraSuccess()" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded w-full flex items-center justify-center gap-2"
                        style="width: 100%; padding: 12px 16px; background-color: #2e7d32; color: white !important; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 8px;">
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
                        <!-- Adjusted to flip video horizontally if needed, though user requested standard view. Adding transform: scaleX(-1) often rectifies a mirrored front camera. -->
                        <video x-ref="video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
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
            .verification-card {
                flex-direction: column !important;
                height: auto !important;
                max-height: 85vh !important;
                overflow-y: auto !important;
            }
            .verification-content {
                min-width: 100% !important;
                border-right: none !important;
                border-bottom: 1px solid #f3f4f6 !important;
                padding: 24px !important;
                order: 2; /* Put content below video on mobile if preferred, or remove to keep top */
            }
             #video-teleport-target {
                flex: none !important;
                height: 300px !important;
                width: 100% !important;
                order: 1; /* Put video on top */
            }
        }
    </style>
</div>
