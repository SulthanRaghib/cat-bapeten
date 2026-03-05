@php
    /** @var \App\Models\ExamSession $record */
    $record = $getRecord();
    $config = $record->examPackage?->technical_scoring_config ?? [];

    $cbtWeight = (float) ($config['cbt_weight'] ?? 100);
    $cbtScore = (float) ($record->cbt_score ?? 0);
    $stageScores = $record->stage_scores ?? [];

    // Build stages — support both new format and legacy interview_weight
    $stages = $config['stages'] ?? null;
    if (empty($stages) && isset($config['interview_weight'])) {
        $stages = [['label' => 'Wawancara', 'weight' => (float) $config['interview_weight']]];
    }
    $stages = (array) $stages;

    // Build rows
    $rows = [];
    $totalContribution = 0;

    // CBT row
    $cbtContrib = round(($cbtScore * $cbtWeight) / 100, 2);
    $totalContribution += $cbtContrib;
    $rows[] = [
        'label' => 'CBT (Tes Tertulis)',
        'score' => $cbtScore,
        'weight' => $cbtWeight,
        'contrib' => $cbtContrib,
        'color' => 'blue',
        'icon' =>
            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', // document-text
        'bar_color' => '#3b82f6',
    ];

    // Stage rows — colour palette cycles
    $palette = [
        ['color' => 'purple', 'bar' => '#8b5cf6'],
        ['color' => 'emerald', 'bar' => '#10b981'],
        ['color' => 'amber', 'bar' => '#f59e0b'],
        ['color' => 'rose', 'bar' => '#f43f5e'],
        ['color' => 'cyan', 'bar' => '#06b6d4'],
    ];

    // One icon per stage slot
    $stageIcons = [
        // microphone
        'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z',
        // presentation-chart-bar
        'M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z',
        // users
        'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        // star
        'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        // lightning-bolt
        'M13 10V3L4 14h7v7l9-11h-7z',
    ];

    foreach ($stages as $i => $stage) {
        $label = $stage['label'] ?? 'Tahap ' . ($i + 1);
        $weight = (float) ($stage['weight'] ?? 0);
        $key = 'stage_' . $i;
        $score = (float) ($stageScores[$key] ?? 0);
        $contrib = round(($score * $weight) / 100, 2);
        $totalContribution += $contrib;
        $p = $palette[$i % count($palette)];

        $rows[] = [
            'label' => $label,
            'score' => $score,
            'weight' => $weight,
            'contrib' => $contrib,
            'color' => $p['color'],
            'icon' => $stageIcons[$i % count($stageIcons)],
            'bar_color' => $p['bar'],
        ];
    }

    $finalScore = (float) $record->total_score;
    $passingGrade = (float) ($record->examPackage?->passing_grade ?? 0);
    $isLulus = $finalScore >= $passingGrade;
@endphp

<div class="space-y-4 text-sm">

    {{-- ── Header card ──────────────────────────────────────────────────── --}}
    <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700">

        {{-- Gradient header --}}
        <div
            class="px-5 py-4 bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-700 dark:from-indigo-800 dark:via-violet-800 dark:to-purple-900">
            <div class="flex items-center gap-3">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-xl bg-white/20 backdrop-blur-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <div class="text-white font-semibold text-base leading-tight">Rincian Tahap Seleksi Lanjutan</div>
                    <div class="text-indigo-200 text-xs mt-0.5">Komponen nilai berbobot — {{ count($rows) }} tahap
                        penilaian</div>
                </div>

                {{-- Lulus/Tidak badge --}}
                <div class="ml-auto">
                    @if ($isLulus)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/30 border border-emerald-400/50 text-emerald-100 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            LULUS
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-500/30 border border-red-400/50 text-red-100 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            TIDAK LULUS
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Row-by-row breakdown ─────────────────────────────────────── --}}
        <div class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800/60">
            @foreach ($rows as $idx => $row)
                @php
                    $barPct = min(100, $row['weight']);
                @endphp
                <div class="px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="flex items-center gap-3">

                        {{-- Icon bubble --}}
                        <div class="flex-shrink-0">
                            @php
                                $bubbleBg = match ($row['color']) {
                                    'blue' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300',
                                    'purple'
                                        => 'bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300',
                                    'emerald'
                                        => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300',
                                    'amber' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300',
                                    'rose' => 'bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300',
                                    default => 'bg-cyan-100 dark:bg-cyan-900/40 text-cyan-600 dark:text-cyan-300',
                                };
                            @endphp
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl {{ $bubbleBg }}">
                                <svg class="w-4.5 h-4.5 w-[18px] h-[18px]" fill="none" stroke="currentColor"
                                    stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $row['icon'] }}" />
                                </svg>
                            </div>
                        </div>

                        {{-- Label & progress bar --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline justify-between mb-1">
                                <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm truncate">
                                    {{ $row['label'] }}
                                    <span class="ml-1.5 text-xs font-normal text-gray-400 dark:text-gray-500">
                                        bobot {{ $row['weight'] }}%
                                    </span>
                                </span>
                                <span class="ml-3 flex-shrink-0 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                    {{ number_format($row['score'], 0) }} poin
                                </span>
                            </div>
                            {{-- Progress bar --}}
                            <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700"
                                    style="width: {{ $barPct }}%; background-color: {{ $row['bar_color'] }}; opacity: 0.85;">
                                </div>
                            </div>
                        </div>

                        {{-- Formula chip --}}
                        <div class="flex-shrink-0 text-right min-w-[100px]">
                            <div class="text-[10px] text-gray-400 dark:text-gray-500 font-mono leading-tight">
                                {{ number_format($row['score'], 0) }} × {{ $row['weight'] }}%
                            </div>
                            @php
                                $contribBg = match ($row['color']) {
                                    'blue'
                                        => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-700',
                                    'purple'
                                        => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-700',
                                    'emerald'
                                        => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-700',
                                    'amber'
                                        => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-700',
                                    'rose'
                                        => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-700',
                                    default
                                        => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-700',
                                };
                            @endphp
                            <div
                                class="inline-flex items-center px-2 py-0.5 mt-0.5 rounded-md border text-xs font-semibold font-mono {{ $contribBg }}">
                                +{{ number_format($row['contrib'], 2) }}
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

            {{-- ── Total row ─────────────────────────────────────────────── --}}
            <div
                class="px-5 py-4 {{ $isLulus ? 'bg-emerald-50 dark:bg-emerald-950/30' : 'bg-red-50 dark:bg-red-950/30' }}">
                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-xl {{ $isLulus ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            @if ($isLulus)
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            @endif
                        </svg>
                    </div>

                    <div class="flex-1">
                        <div
                            class="text-xs font-medium {{ $isLulus ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                            Nilai Akhir (total terbobot)
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            {{-- Stacked progress bar showing component weights --}}
                            <div class="flex-1 h-2 rounded-full overflow-hidden flex gap-0.5">
                                @foreach ($rows as $r)
                                    <div class="h-full rounded-sm" title="{{ $r['label'] }}: {{ $r['weight'] }}%"
                                        style="width: {{ $r['weight'] }}%; background-color: {{ $r['bar_color'] }};">
                                    </div>
                                @endforeach
                            </div>
                            <span
                                class="text-xs {{ $isLulus ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} font-mono">
                                NAB {{ number_format($passingGrade, 0) }}
                            </span>
                        </div>
                    </div>

                    <div class="text-right">
                        <div
                            class="text-2xl font-black {{ $isLulus ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-400' }} font-mono tracking-tight leading-none">
                            {{ number_format($finalScore, 2) }}
                        </div>
                        <div
                            class="text-[10px] {{ $isLulus ? 'text-emerald-500' : 'text-red-400' }} mt-0.5 font-medium uppercase tracking-wide">
                            poin
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- end divide container --}}
    </div>{{-- end outer card --}}

</div>
