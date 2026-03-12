<div x-data="{ confirmForceFinish: false }">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: Details --}}
        <div class="col-span-1 space-y-4">
            <x-filament::section>
                <x-slot name="heading">
                    Detail Peserta
                </x-slot>

                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500">Nama:</span>
                        <span>{{ $record->user->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500">NIP:</span>
                        <span>{{ $record->user->nip ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-500">Status:</span>
                        <x-filament::badge color="success">
                            {{ ucfirst($record->status) }}
                        </x-filament::badge>
                    </div>
                    <div class="pt-4 border-t">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-lg">Skor Sementara:</span>
                            <span class="text-2xl font-bold text-primary-600">
                                {{ $record->answers()->sum('score') }}
                            </span>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            <div class="flex flex-col gap-2">
                <x-filament::button color="danger" icon="heroicon-m-stop" x-on:click="confirmForceFinish = true">
                    Paksa Selesai
                </x-filament::button>
            </div>
        </div>

        {{-- Modal Verifikasi (Custom Alpine Implementation) --}}
        <template x-teleport="body">
            <div x-show="confirmForceFinish" x-cloak class="fixed inset-0 z-[999] overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="confirmForceFinish" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                        aria-hidden="true" @click="confirmForceFinish = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="confirmForceFinish" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <span class="h-6 w-6 text-red-600">⚠️</span>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Konfirmasi Paksa Selesai
                                    </h3>
                                    <div class="mt-2">
                                        <div class="space-y-4 text-sm">
                                            <p class="text-red-600 font-semibold">
                                                Tindakan ini akan:
                                            </p>

                                            <ul class="list-disc pl-5 space-y-1 text-gray-700">
                                                <li>Mengakhiri ujian peserta secara paksa</li>
                                                <li>Menyimpan jawaban yang ada saat ini</li>
                                                <li>Tidak dapat dibatalkan</li>
                                            </ul>

                                            <div class="rounded-md bg-red-50 border border-red-200 p-3 text-red-700">
                                                Pastikan tindakan ini dilakukan karena pelanggaran serius atau kondisi
                                                darurat.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-filament::button color="danger" icon="heroicon-m-stop"
                                wire:click="todoForceFinish({{ $record->id }})"
                                x-on:click="confirmForceFinish = false">
                                Ya, Akhiri Ujian
                            </x-filament::button>
                            <x-filament::button color="gray" x-on:click="confirmForceFinish = false">
                                Batal
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        </template>


        {{-- Right: Activity Log Timeline (polls independently) --}}
        <div class="col-span-2" wire:poll.5s>
            <x-filament::section>
                <x-slot name="heading">
                    Log Aktivitas (Realtime)
                </x-slot>

                <div class="space-y-4 overflow-y-auto pr-2" style="max-height: 420px;">
                    @forelse(\App\Models\ExamActivityLog::where('exam_session_id', $record->id)->latest()->limit(50)->get() as $log)
                        <div
                            class="flex gap-4 p-3 rounded-lg border {{ match ($log->severity) {
                                'critical' => 'bg-red-50 border-red-200',
                                'danger' => 'bg-red-50 border-red-200',
                                'warning' => 'bg-orange-50 border-orange-200',
                                default => 'bg-gray-50 border-gray-200',
                            } }}">
                            <div class="mt-1">
                                @if ($log->severity == 'critical' || $log->severity == 'danger')
                                    <x-filament::icon icon="heroicon-m-exclamation-triangle"
                                        class="h-6 w-6 text-red-500" />
                                @elseif($log->severity == 'warning')
                                    <x-filament::icon icon="heroicon-m-exclamation-circle"
                                        class="h-6 w-6 text-orange-500" />
                                @else
                                    <x-filament::icon icon="heroicon-m-information-circle"
                                        class="h-6 w-6 text-blue-500" />
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-center">
                                    <h4
                                        class="font-bold text-sm uppercase {{ match ($log->severity) {
                                            'critical' => 'text-red-700',
                                            'danger' => 'text-red-700',
                                            'warning' => 'text-orange-700',
                                            default => 'text-gray-700',
                                        } }}">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </h4>
                                    <span class="text-xs text-gray-400">{{ $log->created_at->format('H:i:s') }}</span>
                                </div>
                                <p class="text-sm mt-1 text-gray-600">{{ $log->message }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            Belum ada aktivitas mencurigakan tercatat.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    </div>
</div>
