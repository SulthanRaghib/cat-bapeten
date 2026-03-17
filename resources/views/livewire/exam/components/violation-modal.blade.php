<div x-show="$wire.showViolationModal" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black/70 backdrop-blur-md z-50 flex items-center justify-center">

    <div x-show="$wire.showViolationModal" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="bg-white rounded-[28px] max-w-md md:max-w-lg shadow-2xl overflow-hidden w-[92%]">

        <!-- HEADER dengan solid color -->
        <div :class="violationCounter === 1 ? 'bg-amber-400' :
            (violationCounter === 2 ? 'bg-orange-500' :
                (violationCounter === 3 ? 'bg-red-600' : 'bg-gray-800'))"
            class="text-white px-6 pt-6 pb-5">

            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <p class="text-xs uppercase tracking-wider font-bold text-white/80 mb-2">
                        Status Pengawasan
                    </p>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1.5 text-xs font-bold">
                        <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                        <span
                            x-text="violationCounter === 1 ? 'Peringatan Awal' :
                            (violationCounter === 2 ? 'Peringatan Terakhir' :
                            (violationCounter === 3 ? 'Batas Toleransi' : 'Risiko Tinggi'))"></span>
                    </span>
                </div>

                <div class="rounded-2xl bg-white/20 px-4 py-3 text-right">
                    <p class="text-xs uppercase tracking-wide text-white/70 mb-1">
                        Pelanggaran
                    </p>
                    <p class="text-2xl font-black leading-none" x-text="violationCounter + 'x'"></p>
                </div>
            </div>

            <!-- Icon + Message -->
            <div class="flex items-center gap-4">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/20 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.9"
                        stroke="currentColor" class="h-10 w-10 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider font-bold text-white/80 mb-2">
                        Jenis Pelanggaran
                    </p>
                    <h2 class="text-2xl font-black leading-tight mb-1"
                        x-text="$wire.violationAction || 'Aktivitas mencurigakan'"></h2>
                    <p class="text-sm leading-relaxed text-white/90" x-text="$wire.violationMessage"></p>
                </div>
            </div>
        </div>

        <!-- BODY / CONTENT -->
        <div class="p-6 bg-white">

            <!-- Level Boxes -->
            <div class="grid grid-cols-4 gap-2 mb-5">
                <!-- Level 1 -->
                <div :class="violationCounter >= 1 ?
                    'border-amber-300 bg-amber-50 text-amber-800' :
                    'border-slate-200 bg-slate-50 text-slate-400'"
                    class="rounded-2xl border px-3 py-3 text-center">
                    <p class="text-xs font-bold uppercase tracking-wide mb-1">1</p>
                    <p class="text-xs font-bold">Awal</p>
                </div>

                <!-- Level 2 -->
                <div :class="violationCounter >= 2 ?
                    'border-orange-300 bg-orange-50 text-orange-800' :
                    'border-slate-200 bg-slate-50 text-slate-400'"
                    class="rounded-2xl border px-3 py-3 text-center">
                    <p class="text-xs font-bold uppercase tracking-wide mb-1">2</p>
                    <p class="text-xs font-bold">Terakhir</p>
                </div>

                <!-- Level 3 -->
                <div :class="violationCounter >= 3 ?
                    'border-red-300 bg-red-50 text-red-800' :
                    'border-slate-200 bg-slate-50 text-slate-400'"
                    class="rounded-2xl border px-3 py-3 text-center">
                    <p class="text-xs font-bold uppercase tracking-wide mb-1">3</p>
                    <p class="text-xs font-bold">Batas</p>
                </div>

                <!-- Level 4+ -->
                <div :class="violationCounter >= 4 ?
                    'border-gray-700 bg-gray-800 text-white' :
                    'border-slate-200 bg-slate-50 text-slate-400'"
                    class="rounded-2xl border px-3 py-3 text-center">
                    <p class="text-xs font-bold uppercase tracking-wide mb-1">4+</p>
                    <p class="text-xs font-bold">Kritis</p>
                </div>
            </div>

            <!-- Escalation Message Box -->
            <div :class="violationCounter === 1 ?
                'border-amber-300 bg-amber-50 text-amber-900' :
                (violationCounter === 2 ?
                    'border-orange-300 bg-orange-50 text-orange-900' :
                    (violationCounter === 3 ?
                        'border-red-300 bg-red-50 text-red-900' :
                        'border-gray-700 bg-gray-900 text-white'))"
                class="rounded-2xl border px-4 py-3 mb-5">

                <p :class="violationCounter >= 4 ? 'text-white/70' : 'text-slate-600'"
                    class="text-xs uppercase tracking-wide font-black mb-2"
                    x-text="violationCounter === 1 ? 'Peringatan Awal' :
                        (violationCounter === 2 ? 'Peringatan Terakhir' :
                        (violationCounter === 3 ? 'Pelanggaran Ke-3' : 'Pelanggaran Berulang'))">
                </p>

                <template x-if="violationCounter === 1">
                    <p class="text-sm font-bold leading-relaxed">
                        Ini adalah peringatan awal. Aktivitas Anda sudah tercatat. Lanjutkan ujian dengan tertib agar
                        tidak masuk ke tahap berikutnya.
                    </p>
                </template>

                <template x-if="violationCounter === 2">
                    <p class="text-sm font-bold leading-relaxed">
                        Ini peringatan terakhir. Jika terjadi 1 pelanggaran lagi, admin berhak menghentikan ujian Anda.
                    </p>
                </template>

                <template x-if="violationCounter === 3">
                    <p class="text-sm font-bold leading-relaxed">
                        Anda sudah mencapai pelanggaran ke-3. Jika ujian dihentikan oleh admin, keputusan tersebut final
                        dan tidak dapat diprotes.
                    </p>
                </template>

                <template x-if="violationCounter >= 4">
                    <p class="text-sm font-bold leading-relaxed text-white">
                        Pelanggaran berulang terus terdeteksi. Ujian Anda berada pada risiko tinggi dan dapat dihentikan
                        sewaktu-waktu tanpa peringatan tambahan.
                    </p>
                </template>
            </div>

            <!-- Metadata -->
            <div class="grid grid-cols-2 gap-3 mb-5">
                <div class="border border-slate-200 bg-slate-50 rounded-2xl px-3 py-3">
                    <p class="text-xs uppercase tracking-wide font-black text-slate-500 mb-1">
                        Waktu Terdeteksi
                    </p>
                    <p class="text-sm font-bold text-slate-700" x-text="$wire.violationDetectedAt || '-'"></p>
                </div>
                <div class="border border-slate-200 bg-slate-50 rounded-2xl px-3 py-3">
                    <p class="text-xs uppercase tracking-wide font-black text-slate-500 mb-1">
                        Catatan Sistem
                    </p>
                    <p class="text-xs font-bold leading-tight text-slate-700">
                        Setiap pelanggaran tersimpan dan ditinjau pengawas ujian.
                    </p>
                </div>
            </div>

            <!-- Action Button -->
            <button @click="$wire.showViolationModal = false"
                :class="violationCounter >= 4 ?
                    'bg-gray-800 hover:bg-gray-900 shadow-[0_14px_28px_-14px_rgba(17,24,39,0.95)]' :
                    'bg-red-600 hover:bg-red-700 shadow-[0_14px_28px_-14px_rgba(220,38,38,0.9)]'"
                class="w-full text-white font-black text-sm tracking-wide px-4 py-3.5 rounded-lg transition-all duration-200">
                Saya Mengerti, Lanjutkan Ujian
            </button>
        </div>
    </div>
</div>
