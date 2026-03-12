<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-m-chart-pie" class="h-5 w-5 text-primary-500" />
                {{ __('Exam Result Distribution by Type') }}
            </div>
        </x-slot>
        <x-slot name="description">{{ __('Pass rate based on completed exam sessions') }}</x-slot>

        {{-- Filter Periode --}}
        <div
            class="mb-6 flex flex-wrap items-center justify-between gap-4 pb-5 border-b border-gray-100 dark:border-gray-700">

            {{-- Tombol periode --}}
            <div class="flex flex-wrap gap-2">
                @foreach (['today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All')] as $val => $label)
                    <button type="button" wire:click="$set('period', '{{ $val }}')"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition
                                   {{ $period === $val
                                       ? 'bg-primary-600 text-white shadow-sm'
                                       : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Tombol filter custom --}}
            <div x-data="{
                open: false,
                btnRect: {},
                toggle() {
                    this.btnRect = this.$refs.btn.getBoundingClientRect();
                    this.open = !this.open;
                }
            }">

                <button type="button" x-ref="btn" @click="toggle()"
                    class="relative flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium transition
                               {{ $period === 'custom'
                                   ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300'
                                   : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    <span>{{ __('Date Range') }}</span>
                    @if ($period === 'custom' && ($customFrom || $customTo))
                        <span
                            class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary-600 text-[10px] font-bold text-white">1</span>
                    @endif
                </button>

                <template x-teleport="body">
                    <div x-show="open" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        :style="(() => {
                            const spaceBelow = window.innerHeight - btnRect.bottom;
                            const popH = 280;
                            const top =
                                spaceBelow >= popH + 8 ? btnRect.bottom + 8 : btnRect.top - popH -
                                8;
                            return `position:fixed; top:${top}px; right:${window.innerWidth - btnRect.right}px; z-index:9999;`;
                        })
                        ()"
                        @click.outside="open = false"
                        class="w-72 origin-top-right rounded-xl border border-gray-200 bg-white p-4 shadow-xl
                                dark:border-gray-700 dark:bg-gray-900"
                        style="display:none;">

                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Select Date Range') }}
                        </p>

                        <div class="space-y-3">
                            <div>
                                <label
                                    class="mb-1 block text-xs text-gray-500 dark:text-gray-400">{{ __('From') }}</label>
                                <input type="date" wire:model="customFrom"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm
                                              dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs text-gray-500 dark:text-gray-400">{{ __('Until') }}</label>
                                <input type="date" wire:model="customTo"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm
                                              dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button type="button" @click="open = false" wire:click="$set('period', 'custom')"
                                class="flex-1 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-700 transition">
                                {{ __('Apply') }}
                            </button>
                            <button type="button" @click="open = false" wire:click="resetCustomFilter"
                                class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                {{ __('Reset') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        @if (empty($chartData))
            <div class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <x-filament::icon icon="heroicon-o-chart-pie" class="h-8 w-8 opacity-50" />
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ __('No exam data for this period') }}
                </p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('Try selecting a different period') }}
                </p>
            </div>
        @else
            <div class="grid gap-5" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                @foreach ($chartData as $data)
                    @php
                        $total = $data['lulus'] + $data['tidakLulus'];
                        $passRate = $total > 0 ? round(($data['lulus'] / $total) * 100) : 0;

                        // Color palette based on pass rate
                        if ($passRate >= 70) {
                            $accentColor = '#10b981'; // emerald
                            $badgeBg = 'bg-emerald-50 dark:bg-emerald-950';
                            $badgeText = 'text-emerald-700 dark:text-emerald-300';
                            $rateTextColor = 'text-emerald-600 dark:text-emerald-400';
                        } elseif ($passRate >= 40) {
                            $accentColor = '#f59e0b'; // amber
                            $badgeBg = 'bg-amber-50 dark:bg-amber-950';
                            $badgeText = 'text-amber-700 dark:text-amber-300';
                            $rateTextColor = 'text-amber-500 dark:text-amber-400';
                        } else {
                            $accentColor = '#ef4444'; // red
                            $badgeBg = 'bg-red-50 dark:bg-red-950';
                            $badgeText = 'text-red-700 dark:text-red-300';
                            $rateTextColor = 'text-red-500 dark:text-red-400';
                        }
                    @endphp

                    <div
                        class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm
                                dark:border-gray-700 dark:bg-gray-900 transition hover:shadow-md">

                        {{-- Colored accent bar --}}
                        <div class="h-1 w-full" style="background-color: {{ $accentColor }};"></div>

                        {{-- Card header --}}
                        <div
                            class="flex items-start justify-between gap-3 px-5 pt-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ $data['name'] }}</p>
                                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $total }}
                                    {{ __('participants completed') }}</p>
                            </div>
                            <span
                                class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $badgeBg }} {{ $badgeText }}">
                                {{ $passRate }}%
                            </span>
                        </div>

                        {{-- Pie chart --}}
                        <div class="flex items-center justify-center px-6 py-4">
                            <div wire:key="pie-{{ $period }}-{{ $loop->index }}" x-data x-init="$nextTick(() => window.examPieInit($el.querySelector('canvas'), {{ json_encode([$data['lulus'], $data['tidakLulus']]) }}))"
                                style="width: 190px; height: 190px; position: relative;">
                                <canvas></canvas>
                            </div>
                        </div>

                        {{-- Footer stats --}}
                        <div
                            class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700 border-t border-gray-100 dark:border-gray-700 mt-auto">
                            <div class="flex flex-col items-center py-3 px-2">
                                <span
                                    class="text-base font-bold text-emerald-600 dark:text-emerald-400">{{ $data['lulus'] }}</span>
                                <span
                                    class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">{{ __('Passed') }}</span>
                            </div>
                            <div class="flex flex-col items-center py-3 px-2">
                                <span
                                    class="text-base font-bold text-red-500 dark:text-red-400">{{ $data['tidakLulus'] }}</span>
                                <span
                                    class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">{{ __('Not Passed') }}</span>
                            </div>
                            <div class="flex flex-col items-center py-3 px-2">
                                <span class="text-base font-bold {{ $rateTextColor }}">{{ $passRate }}%</span>
                                <span
                                    class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">{{ __('Pass Rate') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>

@script
    <script>
        /**
         * Didefinisikan sekali saat komponen mount.
         * Dipanggil dari Alpine x-init setiap kali card muncul di DOM.
         */
        window.examPieInit = function(canvas, values) {
            function draw() {
                new Chart(canvas, {
                    type: 'pie',
                    data: {
                        labels: ['{{ __('Passed') }}', '{{ __('Not Passed') }}'],
                        datasets: [{
                            data: values,
                            backgroundColor: ['#10b981', '#ef4444'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 12
                                    },
                                    padding: 12
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        var total = ctx.dataset.data.reduce(function(a, b) {
                                            return a + b;
                                        }, 0);
                                        var pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                        return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                                    },
                                },
                            },
                        },
                    },
                });
            }

            if (window.Chart) {
                draw();
            } else {
                if (!document.getElementById('chartjs-cdn')) {
                    var s = document.createElement('script');
                    s.id = 'chartjs-cdn';
                    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
                    document.head.appendChild(s);
                }
                document.getElementById('chartjs-cdn').addEventListener('load', draw);
            }
        };
    </script>
@endscript
