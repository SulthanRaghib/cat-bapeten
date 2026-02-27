<div
    style="position: fixed; inset: 0; background: #f9fafb; z-index: 99999; overflow-y: auto; font-family: 'Poppins', sans-serif;">
    <div style="min-height: 100%; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div style="width: 100%; max-width: 800px;">
            <div
                style="background: white; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); overflow: hidden; position: relative;">
                <!-- Brand Accent Bar -->
                <div style="height: 5px; background: #f9a825;"></div>

                <div style="padding: 24px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <!-- Logo Instansi -->
                        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo"
                            style="height: 60px; margin-bottom: 12px; object-fit: contain;">

                        <h2
                            style="font-size: 21px; font-weight: 700; color: #1f2937; margin: 0 0 4px 0; letter-spacing: -0.5px;">
                            Ujian Selesai</h2>
                        <p style="color: #6b7280; font-size: 13px; margin: 0; line-height: 1.4;">Jawaban Anda
                            telah berhasil disimpan.<br>Terima kasih telah berpartisipasi.</p>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; align-items: stretch;">
                        
                        <!-- Exam Summary Section (Left) -->
                        <div style="flex: 1 1 350px; text-align: left;">
                            <div
                                style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); height: 100%; box-sizing: border-box;">

                                <!-- NIP & Nama -->
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                    <div>
                                        <div
                                            style="font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">
                                            NIP</div>
                                        <div style="font-size: 13px; font-weight: 600; color: #111827;">
                                            {{ $candidateIdentifier ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div
                                            style="font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">
                                            Nama Peserta</div>
                                        <div style="font-size: 13px; font-weight: 600; color: #111827;">
                                            {{ $candidateName ?? '-' }}</div>
                                    </div>
                                </div>

                                <!-- Judul Ujian -->
                                <div style="margin-bottom: 12px;">
                                    <div
                                        style="font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">
                                        Judul Ujian</div>
                                    <div style="font-size: 13px; font-weight: 600; color: #111827;">
                                        {{ $examTitle ?? '-' }}</div>
                                </div>

                                <!-- Waktu Mulai & Selesai -->
                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                                    <div>
                                        <div
                                            style="font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">
                                            Waktu Mulai</div>
                                        <div style="font-size: 12px; font-weight: 500; color: #374151;">
                                            {{ isset($resultStats['start_time']) ? \Carbon\Carbon::parse($resultStats['start_time'])->format('d M Y, H:i') : '-' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div
                                            style="font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px;">
                                            Waktu Selesai</div>
                                        <div style="font-size: 12px; font-weight: 500; color: #374151;">
                                            {{ isset($resultStats['end_time']) ? \Carbon\Carbon::parse($resultStats['end_time'])->format('d M Y, H:i') : '-' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Pelanggaran -->
                                <div
                                    style="background-color: {{ ($resultStats['violation_count'] ?? 0) > 0 ? '#fef2f2' : '#f0fdf4' }}; border: 1px solid {{ ($resultStats['violation_count'] ?? 0) > 0 ? '#fee2e2' : '#dcfce7' }}; border-radius: 6px; padding: 8px; display: flex; align-items: center; justify-content: space-between;">
                                    <div
                                        style="font-size: 13px; font-weight: 600; color: {{ ($resultStats['violation_count'] ?? 0) > 0 ? '#991b1b' : '#166534' }};">
                                        Total Pelanggaran
                                    </div>
                                    <div
                                        style="background-color: {{ ($resultStats['violation_count'] ?? 0) > 0 ? '#ef4444' : '#22c55e' }}; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 9999px;">
                                        {{ $resultStats['violation_count'] ?? 0 }}
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Score Card (Right) -->
                        <div style="flex: 1 1 200px;">
                            <div
                                style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 16px; text-align: center; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; justify-content: center;">
                                <div
                                    style="color: #64748b; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">
                                    Nilai Akhir</div>
                                <div
                                    style="font-size: 56px; font-weight: 900; color: black; line-height: 1; letter-spacing: -2px;">
                                    {{ $resultStats['total_score'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center;">
                        <button wire:click="finishAndLogout"
                            style="width: 100%; padding: 12px 20px; background-color: #f9a825; color: white; border-radius: 10px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"
                            onmouseover="this.style.backgroundColor='#f9a825'; this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.backgroundColor='#f9a825'; this.style.transform='translateY(0)'">
                            <span>Kembali ke Halaman Utama</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
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
