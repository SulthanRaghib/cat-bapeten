@php
    use App\Services\ExamSessionService;

    /** @var \App\Models\ExamSession $record */
    $session = $getRecord();
    $unitResults = app(ExamSessionService::class)->calculateWeightedResult($session);
    $allPassing = !empty($unitResults) && collect($unitResults)->every(fn($u) => $u['is_passing']);
    $passingCount = collect($unitResults)->filter(fn($u) => $u['is_passing'])->count();
    $totalUnits = count($unitResults);
@endphp

<div class="space-y-4">
    {{-- ── Overall badge ─────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3">
        <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Hasil Penilaian Mansoskul</span>
        @if ($allPassing)
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-success-100 px-3 py-1 text-xs font-bold text-success-700 dark:bg-success-950 dark:text-success-300">
                <x-heroicon-m-check-badge class="h-4 w-4" />
                SEMUA UNIT KOMPETEN ({{ $passingCount }}/{{ $totalUnits }})
            </span>
        @else
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-danger-100 px-3 py-1 text-xs font-bold text-danger-700 dark:bg-danger-950 dark:text-danger-300">
                <x-heroicon-m-x-circle class="h-4 w-4" />
                ADA UNIT TIDAK KOMPETEN ({{ $passingCount }}/{{ $totalUnits }} Lulus)
            </span>
        @endif
    </div>

    {{-- ── Per-unit table ──────────────────────────────────────────────── --}}
    @if (!empty($unitResults))
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5 text-left">
                        <th class="px-4 py-2.5 font-semibold text-gray-600 dark:text-gray-300 w-8 text-center">#</th>
                        <th class="px-4 py-2.5 font-semibold text-gray-600 dark:text-gray-300">Nama Unit Ujian</th>
                        <th class="px-4 py-2.5 font-semibold text-gray-600 dark:text-gray-300 text-right">Skor Diperoleh
                        </th>
                        <th class="px-4 py-2.5 font-semibold text-gray-600 dark:text-gray-300">Indikator Dicapai</th>
                        <th class="px-4 py-2.5 font-semibold text-gray-600 dark:text-gray-300 text-center">Status
                            Kompetensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($unitResults as $i => $unit)
                        <tr
                            class="{{ $unit['is_passing'] ? 'bg-success-50/40 dark:bg-success-950/20' : 'bg-danger-50/40 dark:bg-danger-950/20' }}">
                            {{-- # --}}
                            <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ $i + 1 }}</td>

                            {{-- Nama Unit --}}
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $unit['unit_name'] }}
                            </td>

                            {{-- Skor --}}
                            <td class="px-4 py-3 text-right">
                                <span
                                    class="font-bold text-{{ $unit['is_passing'] ? 'success' : 'danger' }}-600 dark:text-{{ $unit['is_passing'] ? 'success' : 'danger' }}-400">
                                    {{ number_format((float) ($unit['total_score'] ?? 0), 2, ',', '.') }}
                                </span>
                            </td>

                            {{-- Indikator --}}
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                @if (!empty($unit['achieved_indicator']))
                                    <span class="inline-flex items-center gap-1">
                                        <x-heroicon-m-trophy class="h-4 w-4 text-warning-500" />
                                        {{ $unit['achieved_indicator'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 italic">— belum tercapai</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3 text-center">
                                @if ($unit['is_passing'])
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-bold text-success-700 dark:bg-success-950 dark:text-success-300">
                                        <x-heroicon-m-check-circle class="h-3.5 w-3.5" />
                                        KOMPETEN
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-danger-100 px-2.5 py-0.5 text-xs font-bold text-danger-700 dark:bg-danger-950 dark:text-danger-300">
                                        <x-heroicon-m-x-circle class="h-3.5 w-3.5" />
                                        BELUM KOMPETEN
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-white/5 border-t-2 border-gray-300 dark:border-white/20">
                        <td colspan="2"
                            class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-200 text-right">
                            Total Nilai Gabungan:
                        </td>
                        <td class="px-4 py-2.5 text-right font-black text-lg text-gray-900 dark:text-white">
                            {{ number_format((float) ($session->total_score ?? 0), 2, ',', '.') }}
                        </td>
                        <td colspan="2" class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">
                            Kompetensi Unit: {{ $passingCount }}/{{ $totalUnits }} lulus
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <p class="text-sm text-gray-400 italic">Data hasil unit belum tersedia.</p>
    @endif
</div>
