<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Distribusi Hasil Ujian per Tipe Ujian</x-slot>
        <x-slot name="description">Berdasarkan seluruh sesi ujian yang tercatat di sistem</x-slot>

        {{-- Filter Periode --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">

            {{-- Tombol periode --}}
            <div class="flex flex-wrap gap-2">
                @foreach (['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'all' => 'Semua'] as $val => $label)
                    <button type="button"
                            wire:click="$set('period', '{{ $val }}')"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition
                                   {{ $period === $val
                                        ? 'bg-primary-600 text-white shadow-sm'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Tombol filter custom (ikon corong + popover) --}}
            <div x-data="{
                    open: false,
                    btnRect: {},
                    toggle() {
                        this.btnRect = this.$refs.btn.getBoundingClientRect();
                        this.open = !this.open;
                    }
                 }">

                {{-- Tombol --}}
                <button type="button"
                        x-ref="btn"
                        @click="toggle()"
                        class="relative flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium transition
                               {{ $period === 'custom'
                                    ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300'
                                    : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    <span>Rentang Tanggal</span>
                    @if ($period === 'custom' && ($customFrom || $customTo))
                        <span class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary-600 text-[10px] font-bold text-white">1</span>
                    @endif
                </button>

                {{-- Popover di-teleport ke body agar tidak tertabrak elemen lain --}}
                <template x-teleport="body">
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         :style="`position:fixed; top:${btnRect.bottom + 8}px; right:${window.innerWidth - btnRect.right}px; z-index:9999;`"
                         @click.outside="open = false"
                         class="w-72 origin-top-right rounded-xl border border-gray-200 bg-white p-4 shadow-xl
                                dark:border-gray-700 dark:bg-gray-900"
                         style="display:none;">

                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Pilih Rentang Tanggal
                        </p>

                        <div class="space-y-3">
                            <div>
                                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Dari</label>
                                <input type="date" wire:model="customFrom"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm
                                              dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Sampai</label>
                                <input type="date" wire:model="customTo"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm
                                              dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button type="button"
                                    @click="open = false"
                                    wire:click="$set('period', 'custom')"
                                    class="flex-1 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-700 transition">
                                Terapkan
                            </button>
                            <button type="button"
                                    @click="open = false"
                                    wire:click="resetCustomFilter"
                                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                Reset
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        @if (empty($chartData))
            <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                <svg class="mb-3 h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
                <p class="text-sm">Tidak ada data ujian pada periode ini.</p>
            </div>
        @else
            <div class="grid gap-5" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                @foreach ($chartData as $data)
                    @php
                        $total      = $data['lulus'] + $data['tidakLulus'];
                        $passRate   = $total > 0 ? round(($data['lulus'] / $total) * 100) : 0;
                        $rateColor  = $passRate >= 70 ? 'text-emerald-600 dark:text-emerald-400'
                                    : ($passRate >= 40 ? 'text-amber-500 dark:text-amber-400'
                                    : 'text-red-500 dark:text-red-400');
                    @endphp

                    <div class="flex flex-col rounded-2xl border border-gray-100 bg-white shadow-sm
                                dark:border-gray-700 dark:bg-gray-900 overflow-hidden">

                        {{-- Header kartu --}}
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-5 py-3">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                {{ $data['name'] }}
                            </span>
                        </div>

                        {{-- Pie chart: wire:key berubah setiap kali periode berubah, memaksa Alpine re-init --}}
                        <div class="flex items-center justify-center px-6 py-4">
                            <div wire:key="pie-{{ $period }}-{{ $loop->index }}"
                                 x-data
                                 x-init="$nextTick(() => window.examPieInit($el.querySelector('canvas'), {{ json_encode([$data['lulus'], $data['tidakLulus']]) }}))"
                                 style="width: 200px; height: 200px; position: relative;">
                                <canvas></canvas>
                            </div>
                        </div>

                        {{-- Statistik bawah --}}
                        <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex flex-col items-center py-3 px-2">
                                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $data['lulus'] }}</span>
                                <span class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Lulus</span>
                            </div>
                            <div class="flex flex-col items-center py-3 px-2">
                                <span class="text-lg font-bold text-red-500 dark:text-red-400">{{ $data['tidakLulus'] }}</span>
                                <span class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tidak Lulus</span>
                            </div>
                            <div class="flex flex-col items-center py-3 px-2">
                                <span class="text-lg font-bold {{ $rateColor }}">{{ $passRate }}%</span>
                                <span class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Pass Rate</span>
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
    window.examPieInit = function (canvas, values) {
        function draw() {
            new Chart(canvas, {
                type: 'pie',
                data: {
                    labels: ['Lulus', 'Tidak Lulus'],
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
                            labels: { font: { size: 12 }, padding: 12 },
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                    var pct   = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
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
                var s  = document.createElement('script');
                s.id   = 'chartjs-cdn';
                s.src  = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
                document.head.appendChild(s);
            }
            document.getElementById('chartjs-cdn').addEventListener('load', draw);
        }
    };
</script>
@endscript
