@php
    /** @var \App\Models\ExamSession $record */
    $record = $getRecord();
    $violations = $record->activityLogs()
        ->whereIn('severity', ['warning', 'danger', 'critical'])
        ->orderBy('created_at', 'asc')
        ->get();
    $total = $violations->count();
@endphp

<div class="space-y-3">
    {{-- Total --}}
    <div class="flex items-center gap-2 rounded-lg border px-4 py-3
        {{ $total > 0 ? 'border-danger-300 bg-danger-50 dark:border-danger-600 dark:bg-danger-950' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900' }}">
        <x-heroicon-m-exclamation-triangle class="h-6 w-6 {{ $total > 0 ? 'text-danger-500' : 'text-gray-400' }}" />
        <div>
            <span class="text-2xl font-black {{ $total > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-500' }}">{{ $total }}</span>
            <span class="ml-1 text-sm font-medium text-gray-600 dark:text-gray-400">Total Pelanggaran</span>
        </div>
    </div>

    {{-- Detail list --}}
    @if($total > 0)
        <div class="max-h-40 overflow-y-auto space-y-1.5 rounded-lg border border-gray-200 p-2 dark:border-gray-700">
            @foreach($violations as $index => $log)
                <div class="flex items-start gap-2 rounded-md border px-3 py-2 text-sm
                    {{ match($log->severity) {
                        'critical' => 'border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-950',
                        'danger'   => 'border-red-200 bg-red-50 dark:border-red-700 dark:bg-red-950',
                        'warning'  => 'border-orange-200 bg-orange-50 dark:border-orange-700 dark:bg-orange-950',
                        default    => 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900',
                    } }}">
                    {{-- Number --}}
                    <span class="flex-shrink-0 mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold
                        {{ match($log->severity) {
                            'critical' => 'bg-red-600 text-white',
                            'danger'   => 'bg-red-500 text-white',
                            'warning'  => 'bg-orange-500 text-white',
                            default    => 'bg-gray-400 text-white',
                        } }}">
                        {{ $index + 1 }}
                    </span>

                    <div class="flex-1 min-w-0">
                        {{-- Action label --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-bold uppercase tracking-wide
                                {{ match($log->severity) {
                                    'critical' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                    'danger'   => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                    'warning'  => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
                                    default    => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                } }}">
                                {{ match($log->severity) {
                                    'critical' => 'KRITIS',
                                    'danger'   => 'BAHAYA',
                                    'warning'  => 'PERINGATAN',
                                    default    => 'INFO',
                                } }}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $log->created_at->format('H:i:s') }}
                            </span>
                        </div>
                        {{-- Message --}}
                        <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $log->message ?? $log->action }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-400 italic">Tidak ada pelanggaran tercatat.</p>
    @endif
</div>
