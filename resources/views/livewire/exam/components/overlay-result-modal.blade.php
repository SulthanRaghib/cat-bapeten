@if ($showResults)
    <div style="position: fixed; inset: 0; background: #f9fafb; z-index: 99999; overflow-y: auto; font-family: 'Poppins', sans-serif;">
        <div style="min-height: 100%; display: flex; align-items: center; justify-content: center; padding: 10px;">
            <div style="width: 100%; max-width: 480px;">
                <div style="background: white; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); overflow: hidden; position: relative;">
                    <!-- Brand Accent Bar - Red for Warning -->
                    <div style="height: 6px; background: #ef4444;"></div>

                    <div style="padding: 40px;">
                        <div style="text-align: center; margin-bottom: 35px;">
                            <!-- Logo Instansi -->
                            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" style="height: 60px; margin-bottom: 24px; object-fit: contain;">

                            <h2 style="font-size: 24px; font-weight: 800; color: #1f2937; margin: 0 0 8px 0; letter-spacing: -0.5px;">
                                Ujian Dihentikan</h2>
                            <p style="color: #ef4444; font-size: 14px; font-weight: 600; margin: 0; line-height: 1.5;">
                                Sesi ujian Anda telah berakhir otomatis.<br>
                                <span style="color: #6b7280; font-weight: 400;">(Waktu habis atau dihentikan oleh pengawas)</span>
                            </p>
                        </div>

                        <!-- Score Card -->
                        <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 16px; padding: 32px 20px; text-align: center; margin-bottom: 32px; position: relative;">
                            <div style="color: #991b1b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px;">
                                Nilai Akhir</div>
                            <div style="font-size: 72px; font-weight: 900; color: #7f1d1d; line-height: 1; letter-spacing: -2px;">
                                {{ $resultStats['total_score'] ?? 0 }}
                            </div>
                        </div>

                        <div style="text-align: center;">
                            <button wire:click="finishAndLogout" style="width: 100%; padding: 16px 24px; background-color: #ef4444; color: white; border-radius: 12px; border: none; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);" onmouseover="this.style.backgroundColor='#dc2626'; this.style.transform='translateY(-1px)'" onmouseout="this.style.backgroundColor='#ef4444'; this.style.transform='translateY(0)'">
                                <span>Kembali ke Halaman Utama</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
