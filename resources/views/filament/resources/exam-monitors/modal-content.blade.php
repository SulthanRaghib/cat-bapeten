<div wire:poll.3s>
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
                <x-filament::button color="danger" wire:click="todoForceFinish({{ $record->id }})"
                    icon="heroicon-m-stop">
                    Paksa Selesai
                </x-filament::button>
            </div>
        </div>

        {{-- Right: Activity Log Timeline --}}
        <div class="col-span-2">
            <x-filament::section>
                <x-slot name="heading">
                    Log Aktivitas (Realtime)
                </x-slot>

                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                    @forelse(\App\Models\ExamActivityLog::where('exam_session_id', $record->id)->latest()->get() as $log)
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
