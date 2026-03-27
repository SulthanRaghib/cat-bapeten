@php
    /** @var \App\Models\ExamSession $session */
    $session = $getRecord();

    // ── 1. Determine the ordered question list ────────────────────────────────
    // answers_meta stores the shuffled question IDs used during the exam
    $questionIdOrder = $session->resolveQuestionIds();

    // Fall back to exam package question order if meta is missing
    if (empty($questionIdOrder)) {
        $questionIdOrder =
            $session->examPackage?->questions()->orderByPivot('sort_order')->pluck('questions.id')->toArray() ?? [];
    }

    if (empty($questionIdOrder)) {
        $rows = [];
        $total = 0;
        $benar = $salah = $tdkJawab = 0;
        $evalMethod = 'correct_wrong';
    } else {
        // ── 2. Load questions & answers ───────────────────────────────────────
        $questions = \App\Models\Question::whereIn('id', $questionIdOrder)->get()->keyBy('id');
        $answersByQId = $session->answers()->get()->keyBy('question_id');

        // ── 3. Evaluation method ──────────────────────────────────────────────
        $evalMethod = $session->examPackage?->examType?->evaluation_method ?? 'correct_wrong';
        $isWeighted = $evalMethod === 'weighted';

        // ── 4. Build row data ─────────────────────────────────────────────────
        $rows = [];
        $benar = 0;
        $salah = 0;
        $tdkJawab = 0;

        foreach ($questionIdOrder as $idx => $qid) {
            $q = $questions->get($qid);
            if (!$q) {
                continue;
            }

            $ans = $answersByQId->get($qid);
            $answered = $ans && $ans->answer !== null && $ans->answer !== '';
            $rawKey = $ans?->answer ?? null;
            $score = $ans?->score ?? 0;
            $isDoubtful = (bool) ($ans?->is_doubtful ?? false);
            $options = $q->options ?? [];
            $sc = $q->scoring_config ?? [];

            // Normalise selected key to uppercase letter (A, B, C …)
            $selectedLabel = null;
            $selectedText = null;

            if ($answered) {
                if (preg_match('/^[A-Za-z]$/', $rawKey)) {
                    $selectedLabel = strtoupper($rawKey);
                } else {
                    $selectedLabel = is_numeric($rawKey) ? chr(65 + (int) $rawKey) : strtoupper($rawKey);
                }

                foreach ($options as $k => $opt) {
                    $letter = is_numeric($k) ? chr(65 + (int) $k) : strtoupper($k);
                    if ($letter === $selectedLabel) {
                        $raw = is_array($opt)
                            ? $opt['answer_text'] ?? ($opt['teks'] ?? ($opt['text'] ?? ''))
                            : (string) $opt;
                        $selectedText = mb_strimwidth(strip_tags($raw), 0, 80, '…');
                        break;
                    }
                }
            }

            // Correct key (technical)
            $correctLabel = null;
            $correctText = null;
            if (!$isWeighted) {
                if (!empty($sc['correct'])) {
                    $correctLabel = strtoupper($sc['correct']);
                }
                if (!$correctLabel) {
                    foreach ($options as $k => $opt) {
                        if (is_array($opt) && !empty($opt['is_correct'])) {
                            $correctLabel = is_numeric($k) ? chr(65 + (int) $k) : strtoupper($k);
                            break;
                        }
                    }
                }
                if ($correctLabel) {
                    foreach ($options as $k => $opt) {
                        $letter = is_numeric($k) ? chr(65 + (int) $k) : strtoupper($k);
                        if ($letter === $correctLabel) {
                            $raw = is_array($opt)
                                ? $opt['answer_text'] ?? ($opt['teks'] ?? ($opt['text'] ?? ''))
                                : (string) $opt;
                            $correctText = mb_strimwidth(strip_tags($raw), 0, 60, '…');
                            break;
                        }
                    }
                }
            }

            // Bobot for selected option (structural / weighted)
            $selectedBobot = null;
            if ($isWeighted && $answered && $selectedLabel) {
                $selectedBobot = $sc['bobot'][$selectedLabel] ?? null;
                if ($selectedBobot === null && isset($sc['list'])) {
                    foreach ($sc['list'] as $cfg) {
                        if (isset($cfg['kode']) && strtoupper($cfg['kode']) === $selectedLabel) {
                            $selectedBobot = $cfg['skor'] ?? 0;
                            break;
                        }
                    }
                }
            }

            // Max points available (technical)
            $maxPoints = null;
            if (!$isWeighted && $correctLabel) {
                $maxPoints = $sc['skor'] ?? ($sc['bobot'][$correctLabel] ?? null);
                if ($maxPoints === null && isset($sc['list'])) {
                    foreach ($sc['list'] as $cfg) {
                        if (isset($cfg['kode']) && strtoupper($cfg['kode']) === $correctLabel) {
                            $maxPoints = $cfg['skor'] ?? null;
                            break;
                        }
                    }
                }
            }

            // Status
            if (!$answered) {
                $status = 'skip';
                $tdkJawab++;
            } elseif ($score > 0) {
                $status = 'correct';
                $benar++;
            } else {
                $status = 'wrong';
                $salah++;
            }

            $rows[] = compact(
                'idx',
                'qid',
                'status',
                'score',
                'isDoubtful',
                'selectedLabel',
                'selectedText',
                'correctLabel',
                'correctText',
                'selectedBobot',
                'maxPoints',
                'isWeighted',
            );
        }

        $total = count($rows);
    }

    // Progress percentages
    $pctBenar = $total > 0 ? round(($benar / $total) * 100) : 0;
    $pctSalah = $total > 0 ? round(($salah / $total) * 100) : 0;
    $pctTdkJawab = $total > 0 ? round(($tdkJawab / $total) * 100) : 0;
    $hasDoubt = collect($rows)->contains('isDoubtful', true);
@endphp

<div class="space-y-5 text-sm">

    {{-- ── Empty state ────────────────────────────────────────────────────── --}}
    @if ($total === 0)
        <div class="flex flex-col items-center justify-center py-12 text-gray-400 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 opacity-40" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="font-medium">Belum ada data soal untuk sesi ini.</p>
        </div>
    @else
        {{-- ── Ragu-ragu indicator (only non-duplicate info not in stats) ─────── --}}
        @if ($hasDoubt)
            <div class="flex items-center gap-1.5 text-xs text-violet-600 dark:text-violet-400 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01" />
                </svg>
                Terdapat soal yang ditandai ragu-ragu
            </div>
        @endif

        {{-- ── Detail table (scrollable) ───────────────────────────────────────── --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="overflow-y-auto" style="max-height: 480px;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">
                            <th
                                class="px-3 py-2.5 w-12 text-center font-semibold border-b border-gray-200 dark:border-gray-700">
                                No.</th>
                            <th class="px-3 py-2.5 font-semibold border-b border-gray-200 dark:border-gray-700">Jawaban
                                Dipilih</th>
                            @if (!($rows[0]['isWeighted'] ?? false))
                                <th
                                    class="px-3 py-2.5 font-semibold border-b border-gray-200 dark:border-gray-700 hidden sm:table-cell">
                                    Kunci Jawaban</th>
                            @endif
                            <th
                                class="px-3 py-2.5 w-32 text-center font-semibold border-b border-gray-200 dark:border-gray-700">
                                Status</th>
                            <th
                                class="px-3 py-2.5 w-20 text-center font-semibold border-b border-gray-200 dark:border-gray-700">
                                Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach ($rows as $row)
                            @php
                                $isWeightedRow = $row['isWeighted'];
                                $rowBg = match ($row['status']) {
                                    'correct' => 'bg-emerald-50/50 dark:bg-emerald-900/10',
                                    'wrong' => 'bg-red-50/50 dark:bg-red-900/10',
                                    default => 'bg-amber-50/40 dark:bg-amber-900/10',
                                };
                                $noBadgeBg = match ($row['status']) {
                                    'correct' => 'bg-emerald-500 text-white',
                                    'wrong' => 'bg-red-400 text-white',
                                    default => 'bg-amber-300 text-amber-900',
                                };
                                $statusChip = match ($row['status']) {
                                    'correct' => [
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
                                        'Benar',
                                    ],
                                    'wrong' => [
                                        'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
                                        'Salah',
                                    ],
                                    default => [
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
                                        'Tdk Dijawab',
                                    ],
                                };
                                $scoreColor = match ($row['status']) {
                                    'correct' => 'text-emerald-600 dark:text-emerald-400 font-bold',
                                    'wrong' => 'text-red-500 dark:text-red-400',
                                    default => 'text-gray-400',
                                };
                                $no = $row['idx'] + 1;
                            @endphp
                            <tr
                                class="{{ $rowBg }} hover:brightness-95 dark:hover:brightness-110 transition-colors">

                                {{-- No. --}}
                                <td class="px-3 py-2.5 text-center align-top">
                                    <span
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $noBadgeBg }}">
                                        {{ $no }}
                                    </span>
                                    @if ($row['isDoubtful'])
                                        <span
                                            class="block mt-0.5 text-[9px] leading-none font-medium text-violet-500 dark:text-violet-400">ragu</span>
                                    @endif
                                </td>

                                {{-- Jawaban dipilih --}}
                                <td class="px-3 py-2.5 align-top">
                                    @if ($row['selectedLabel'])
                                        <div class="flex items-start gap-2">
                                            <span
                                                class="shrink-0 inline-flex items-center justify-center w-6 h-6 mt-0.5 rounded-md
                                        {{ $row['status'] === 'correct' ? 'bg-emerald-500 text-white' : 'bg-red-400 text-white' }}
                                        text-xs font-bold leading-none">
                                                {{ $row['selectedLabel'] }}
                                            </span>
                                            @if ($row['selectedText'])
                                                <span
                                                    class="text-gray-700 dark:text-gray-300 text-xs leading-snug line-clamp-2">{{ $row['selectedText'] }}</span>
                                            @else
                                                <span class="text-gray-400 text-xs italic">Opsi
                                                    {{ $row['selectedLabel'] }}</span>
                                            @endif
                                        </div>
                                        @if ($isWeightedRow && $row['selectedBobot'] !== null)
                                            <span class="mt-1 inline-block text-[10px] text-gray-400">bobot:
                                                {{ $row['selectedBobot'] }} poin</span>
                                        @endif
                                    @else
                                        <span class="flex items-center gap-1.5 text-gray-400 italic text-xs">
                                            <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 12H4" />
                                            </svg>
                                            Tidak dijawab
                                        </span>
                                    @endif
                                </td>

                                {{-- Kunci jawaban (technical only) --}}
                                @if (!$isWeightedRow)
                                    <td class="px-3 py-2.5 hidden sm:table-cell align-top">
                                        @if ($row['correctLabel'])
                                            <div class="flex items-start gap-2">
                                                <span
                                                    class="shrink-0 inline-flex items-center justify-center w-6 h-6 mt-0.5 rounded-md bg-emerald-500 text-white text-xs font-bold leading-none">
                                                    {{ $row['correctLabel'] }}
                                                </span>
                                                @if ($row['correctText'])
                                                    <span
                                                        class="text-gray-600 dark:text-gray-400 text-xs leading-snug line-clamp-2">{{ $row['correctText'] }}</span>
                                                @else
                                                    <span class="text-gray-400 text-xs italic">Opsi
                                                        {{ $row['correctLabel'] }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- Status --}}
                                <td class="px-3 py-2.5 text-center align-top">
                                    <span
                                        class="inline-flex items-center justify-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusChip[0] }}">
                                        @if ($row['status'] === 'correct')
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        @elseif ($row['status'] === 'wrong')
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 12H4" />
                                            </svg>
                                        @endif
                                        {{ $statusChip[1] }}
                                    </span>
                                    @if ($row['isDoubtful'])
                                        <span
                                            class="mt-1 flex items-center justify-center gap-0.5 text-[10px] text-violet-500 dark:text-violet-400">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01" />
                                            </svg>
                                            Ragu-ragu
                                        </span>
                                    @endif
                                </td>

                                {{-- Nilai --}}
                                <td class="px-3 py-2.5 text-center align-top">
                                    @if ($row['status'] === 'skip')
                                        <span class="text-gray-300 dark:text-gray-600 font-medium">—</span>
                                    @else
                                        <span class="text-base {{ $scoreColor }}">
                                            {{ $row['score'] > 0 ? '+' . $row['score'] : $row['score'] }}
                                        </span>
                                        @if (!$isWeightedRow && $row['maxPoints'] !== null && $row['status'] === 'wrong')
                                            <span class="block text-[10px] text-gray-400">(maks
                                                +{{ $row['maxPoints'] }})</span>
                                        @endif
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Footer total row --}}
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300 font-semibold">
                            <td colspan="{{ $rows[0]['isWeighted'] ?? false ? 3 : 4 }}"
                                class="px-3 py-2.5 border-t border-gray-200 dark:border-gray-700 text-right">
                                Total Nilai Diperoleh
                            </td>
                            <td
                                class="px-3 py-2.5 border-t border-gray-200 dark:border-gray-700 text-center text-base font-bold text-gray-800 dark:text-white">
                                {{ $session->total_score ?? collect($rows)->sum('score') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>{{-- end scroll wrapper --}}
    @endif {{-- end empty state --}}

</div>
