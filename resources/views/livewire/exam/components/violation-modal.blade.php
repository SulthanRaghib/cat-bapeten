@if ($showViolationModal)
    <div class="violation-modal">
        <div class="violation-content">
            <div class="violation-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" style="width: 40px; height: 40px;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-2">Peringatan Pelanggaran</h2>
            <p class="text-gray-600 mb-6 font-medium">{{ $violationMessage }}</p>

            <div class="bg-red-50 p-4 rounded-lg mb-6 text-left border border-red-100">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-red-600 font-semibold uppercase tracking-wider">Total
                        Pelanggaran</span>
                    <span
                        class="bg-red-200 text-red-800 text-xs font-bold px-2 py-1 rounded-full">{{ $violationCount }}x</span>
                </div>

                @if ($violationCount >= 3)
                    <p class="text-red-700 text-sm font-semibold mt-2">
                        ⚠️ Perhatian: Admin berhak memberhentikan ujian Anda jika pelanggaran berlanjut.
                    </p>
                @else
                    <p class="text-gray-500 text-xs mt-1">
                        Harap patuhi tata tertib ujian. Segala aktivitas Anda dipantau oleh sistem.
                    </p>
                @endif
            </div>

            <button wire:click="closeViolationModal"
                class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                SAYA MENGERTI
            </button>
        </div>
    </div>
@endif
