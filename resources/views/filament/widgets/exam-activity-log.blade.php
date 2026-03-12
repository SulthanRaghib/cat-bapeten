{{-- resources/views/filament/widgets/exam-activity-log.blade.php --}}
<x-filament-widgets::widget wire:poll.30s class="fi-wi-exam-activity-log">
    <x-filament::section icon="heroicon-m-clock" :heading="__('Exam Activity Log')" :description="__('Latest participant activities, auto-refreshes every 30 seconds')" collapsible>
        <x-slot name="headerEnd">
            {{-- Filter tabs --}}
            <div class="flex items-center gap-2">
                <x-filament::badge color="gray" class="tabular-nums">
                    {{ $logs->count() }} {{ __('activities') }}
                </x-filament::badge>

                <div class="flex rounded-lg border border-gray-200 dark:border-white/10 overflow-hidden text-sm">
                    <button wire:click="$set('filter', 'all')" @class([
                        'px-3 py-1 font-medium transition-colors',
                        'bg-primary-500 text-white' => $filter === 'all',
                        'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5' =>
                            $filter !== 'all',
                    ])>
                        {{ __('All Activities') }}
                    </button>
                    <button wire:click="$set('filter', 'violations')" @class([
                        'px-3 py-1 font-medium transition-colors border-l border-gray-200 dark:border-white/10',
                        'bg-red-500 text-white' => $filter === 'violations',
                        'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5' =>
                            $filter !== 'violations',
                    ])>
                        {{ __('Violations Only') }}
                    </button>
                </div>
            </div>
        </x-slot>

        @if ($logs->isEmpty())
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400 dark:text-gray-500">
                <x-filament::icon icon="heroicon-o-clipboard-document-list" class="mb-3 h-12 w-12 opacity-40" />
                <p class="text-sm font-medium">{{ __('No activity recorded yet') }}</p>
                <p class="mt-1 text-xs">{{ __('Activity feed will appear once exams are in progress.') }}</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/5 overflow-y-auto" style="max-height: 400px;">
                @foreach ($logs as $log)
                    <div
                        class="flex items-start gap-3 py-2.5 px-1 hover:bg-gray-50 dark:hover:bg-white/5 rounded-lg transition-colors">
                        {{-- Severity dot --}}
                        <div class="mt-1 flex-shrink-0">
                            <span @class([
                                'inline-flex h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-gray-900',
                                $log['dot'],
                            ])></span>
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5">
                                {{-- Severity badge --}}
                                @php
                                    $badgeColor = match ($log['severity']) {
                                        'critical' => 'purple',
                                        'danger' => 'danger',
                                        'warning' => 'warning',
                                        default => 'info',
                                    };
                                @endphp
                                <x-filament::badge :color="$badgeColor" size="sm" class="shrink-0">
                                    {{ __($log['action']) }}
                                </x-filament::badge>

                                {{-- Participant name --}}
                                <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $log['participant'] }}
                                </span>

                                @if ($log['nip'])
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                        ({{ $log['nip'] }})
                                    </span>
                                @endif

                                <span class="text-xs text-gray-400 dark:text-gray-500">—</span>

                                {{-- Package name --}}
                                <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ $log['package'] }}
                                </span>
                            </div>

                            {{-- DB message (Indonesian) --}}
                            @if ($log['message'])
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-1">
                                    {{ $log['message'] }}
                                </p>
                            @endif
                        </div>

                        {{-- Time --}}
                        <div class="flex-shrink-0 text-right">
                            <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap"
                                title="{{ $log['time']?->format('d M Y, H:i:s') }}">
                                {{ $log['time']?->diffForHumans() ?? '—' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Footer: link to monitor --}}
            <div class="mt-3 flex justify-end border-t border-gray-100 dark:border-white/5 pt-3">
                <x-filament::link :href="$monitorUrl" icon="heroicon-m-arrow-top-right-on-square" icon-position="after"
                    size="sm" color="primary">
                    {{ __('View All in Monitoring') }}
                </x-filament::link>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
