<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-1 mt-1">
    {{-- 1. Ujian Sedang Berlangsung Tanpa Aktivitas (Critical Warning) --}}
    @if ($examsOngoingNoActivity > 0)
        <div wire:click="mountAction('viewNoActivity')"
            class="group cursor-pointer relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 hover:ring-warning-500/50">

            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sedang Berlangsung Tanpa Aktivitas
                    </p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span
                            class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $examsOngoingNoActivity }}</span>
                        <span class="text-xs text-gray-400">ujian bermasalah</span>
                    </div>
                </div>
                <div
                    class="relative p-2 bg-warning-50 dark:bg-warning-500/10 rounded-lg text-warning-600 dark:text-warning-400 group-hover:bg-warning-600 group-hover:text-white transition-colors">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 animate-pulse" />
                    <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-warning-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-warning-500"></span>
                    </span>
                </div>
            </div>

            {{-- Decorative stripe --}}
            <div
                class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-warning-400 to-warning-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300">
            </div>
        </div>
    @endif

    {{-- 2. Ujian Mendekati Waktu Mulai (Info) --}}
    @if ($examsStartingSoon > 0)
        <div wire:click="mountAction('viewStartingSoon')"
            class="group cursor-pointer relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 hover:ring-info-500/50">

            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Segera Mulai < 1 Jam</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span
                                    class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $examsStartingSoon }}</span>
                                <span class="text-xs text-gray-400">ujian</span>
                            </div>
                </div>
                <div
                    class="p-2 bg-info-50 dark:bg-info-500/10 rounded-lg text-info-600 dark:text-info-400 group-hover:bg-info-600 group-hover:text-white transition-colors">
                    <x-heroicon-o-clock class="w-6 h-6" />
                </div>
            </div>

            <div
                class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-info-400 to-info-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300">
            </div>
        </div>
    @endif

    {{-- 4. Peserta Terdeteksi Logout (Danger) --}}
    @if ($participantsLoggedOut > 0)
        <div wire:click="mountAction('viewLoggedOut')"
            class="group cursor-pointer relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 hover:ring-danger-500/50">

            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Terdeteksi Logout</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span
                            class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $participantsLoggedOut }}</span>
                        <span class="text-xs text-gray-400">peserta</span>
                    </div>
                </div>
                <div
                    class="p-2 bg-danger-50 dark:bg-danger-500/10 rounded-lg text-danger-600 dark:text-danger-400 group-hover:bg-danger-600 group-hover:text-white transition-colors">
                    <x-heroicon-o-user-minus class="w-6 h-6" />
                </div>
            </div>

            <div
                class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-danger-400 to-danger-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300">
            </div>
        </div>
    @endif
</div>
