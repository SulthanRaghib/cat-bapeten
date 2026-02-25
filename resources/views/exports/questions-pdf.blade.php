<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bank Soal - Export PDF</title>
    <style>
        @page { margin: 18mm 14mm 18mm 14mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.55;
        }

        /* ── Header ──────────────────────────────────────────── */
        .cover {
            text-align: center;
            padding: 24px 0 16px;
            border-bottom: 3px double #0d47a1;
            margin-bottom: 16px;
        }
        .cover-org  { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #0d47a1; font-weight: bold; }
        .cover-title { font-size: 20px; font-weight: bold; color: #0d47a1; margin: 4px 0; }
        .cover-sub  { font-size: 9.5px; color: #666; }

        /* ── Filter Strip ────────────────────────────────────── */
        .filter-strip {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            padding: 6px 12px;
            margin-bottom: 14px;
            font-size: 9px;
            color: #1565c0;
        }
        .filter-strip strong { color: #0d47a1; }
        .filter-item { display: inline-block; margin-right: 14px; }

        /* ── Stats ───────────────────────────────────────────── */
        .stats { margin-bottom: 14px; page-break-inside: avoid; }
        .stats-heading {
            font-size: 10px; font-weight: bold; color: #0d47a1;
            border-bottom: 2px solid #0d47a1;
            padding-bottom: 2px; margin-bottom: 8px;
        }
        .stats-grid { width: 100%; border-collapse: collapse; }
        .stats-grid td { vertical-align: top; padding: 0 6px 0 0; width: 33.33%; }
        .stat-box {
            border: 1px solid #e0e0e0;
            padding: 6px 8px;
            margin-bottom: 4px;
            background: #fafafa;
        }
        .stat-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.4px; color: #999; }
        .stat-val   { font-size: 16px; font-weight: bold; color: #0d47a1; }
        .stat-row   { font-size: 9px; color: #555; padding: 1px 0; border-bottom: 1px dotted #e0e0e0; }
        .stat-row:last-child { border-bottom: none; }
        .stat-row-label { display: inline-block; width: 68%; }
        .stat-row-count { display: inline-block; width: 30%; text-align: right; font-weight: bold; color: #333; }

        .divider { border: none; border-top: 2px solid #0d47a1; margin: 14px 0 12px; }

        /* ── Question Card ───────────────────────────────────── */
        .q-card {
            border: 1px solid #cfd8dc;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .q-head {
            background: #0d47a1;
            color: #fff;
            padding: 5px 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .q-head .q-id { float: right; font-weight: normal; font-size: 8px; color: rgba(255,255,255,.6); }
        .q-meta {
            background: #eceff1;
            padding: 4px 12px;
            font-size: 8.5px;
            color: #455a64;
            border-bottom: 1px solid #ddd;
        }
        .q-meta-item { display: inline-block; margin-right: 12px; }
        .q-badge {
            display: inline-block;
            padding: 0 6px;
            font-size: 8px;
            font-weight: bold;
        }
        .q-badge-type { background: #0d47a1; color: #fff; }
        .q-badge-easy { background: #c8e6c9; color: #2e7d32; }
        .q-badge-medium { background: #fff3e0; color: #e65100; }
        .q-badge-hard { background: #ffcdd2; color: #c62828; }

        /* Body */
        .q-body { padding: 8px 12px; }
        .q-text { font-size: 10px; line-height: 1.6; margin-bottom: 6px; }

        /* Image links */
        .img-links {
            margin: 4px 0 6px;
            padding: 4px 8px;
            background: #f5f5f5;
            border-left: 3px solid #1565c0;
            font-size: 8.5px;
            color: #1565c0;
            word-break: break-all;
        }
        .img-links-label { font-weight: bold; color: #0d47a1; font-size: 8px; text-transform: uppercase; }

        /* Options */
        .opts-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .opts-table tr { border-bottom: 1px solid #f0f0f0; }
        .opts-table tr:last-child { border-bottom: none; }
        .opt-ltr {
            width: 24px; padding: 4px 2px; text-align: center; vertical-align: top;
        }
        .opt-circle {
            display: inline-block;
            width: 20px; height: 20px; line-height: 20px;
            text-align: center; border-radius: 50%;
            font-weight: bold; font-size: 9px; color: #fff;
            background: #546e7a;
        }
        .opt-circle-correct { background: #2e7d32; }
        .opt-txt { padding: 4px; vertical-align: top; font-size: 9.5px; color: #333; }
        .opt-score { width: 60px; padding: 4px; text-align: right; vertical-align: top; font-size: 8px; color: #888; }
        .opt-correct-tag {
            display: inline-block;
            background: #e8f5e9; color: #2e7d32;
            padding: 0 4px; font-size: 7px; font-weight: bold;
        }

        /* ── Answer Key ──────────────────────────────────────── */
        .ak-section { page-break-before: always; }
        .ak-title {
            font-size: 13px; font-weight: bold; color: #0d47a1;
            text-align: center; padding: 12px 0 8px;
            border-bottom: 3px solid #0d47a1; margin-bottom: 12px;
        }
        .ak-table { width: 100%; border-collapse: collapse; font-size: 9px; }
        .ak-table thead th {
            background: #0d47a1; color: #fff;
            padding: 5px 6px; text-align: left;
            font-size: 8px; text-transform: uppercase;
        }
        .ak-table tbody td { padding: 4px 6px; border-bottom: 1px solid #e0e0e0; }
        .ak-table tbody tr:nth-child(even) { background: #f5f5f5; }
        .ak-correct { font-weight: bold; color: #2e7d32; }

        /* Footer */
        .footer {
            text-align: center; font-size: 7.5px; color: #bbb;
            margin-top: 16px; padding-top: 8px; border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

    {{-- ── HEADER ──────────────────────────────────────────── --}}
    <div class="cover">
        <div class="cover-org">Badan Pengawas Tenaga Nuklir</div>
        <div class="cover-title">Bank Soal</div>
        <div class="cover-sub">Diekspor pada {{ now()->translatedFormat('l, d F Y &mdash; H:i') }} WIB &bull; {{ $questions->count() }} soal</div>
    </div>

    {{-- ── FILTER STRIP ────────────────────────────────────── --}}
    @if (!empty($filterMeta))
        <div class="filter-strip">
            <strong>Filter:</strong>
            @foreach ($filterMeta as $label => $value)
                <span class="filter-item">{{ $label }}: <strong>{{ $value }}</strong></span>
            @endforeach
        </div>
    @endif

    {{-- ── STATISTICS ──────────────────────────────────────── --}}
    <div class="stats">
        <div class="stats-heading">Ringkasan</div>
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-box">
                        <div class="stat-label">Total Soal</div>
                        <div class="stat-val">{{ $stats['total'] ?? $questions->count() }}</div>
                    </div>
                </td>
                <td>
                    <div class="stat-box">
                        <div class="stat-label">Per Tipe</div>
                        @foreach (($stats['by_type'] ?? []) as $name => $cnt)
                            <div class="stat-row">
                                <span class="stat-row-label">{{ $name }}</span>
                                <span class="stat-row-count">{{ $cnt }}</span>
                            </div>
                        @endforeach
                    </div>
                </td>
                <td>
                    <div class="stat-box">
                        <div class="stat-label">Per Kategori</div>
                        @foreach (($stats['by_category'] ?? []) as $name => $cnt)
                            <div class="stat-row">
                                <span class="stat-row-label">{{ $name }}</span>
                                <span class="stat-row-count">{{ $cnt }}</span>
                            </div>
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <hr class="divider">

    {{-- ── QUESTIONS ───────────────────────────────────────── --}}
    @foreach ($questions as $index => $question)
        @php
            $catBadge = match($question->category) {
                'easy'   => ['cls' => 'q-badge-easy',   'lbl' => 'Mudah'],
                'medium' => ['cls' => 'q-badge-medium', 'lbl' => 'Sedang'],
                'hard'   => ['cls' => 'q-badge-hard',   'lbl' => 'Sulit'],
                default  => ['cls' => '',               'lbl' => '-'],
            };
            $options    = $question->options ?? [];
            $imageLinks = $question->question_image_links ?? [];
        @endphp

        <div class="q-card">
            <div class="q-head">
                Soal {{ $index + 1 }}
                <span class="q-id">ID: {{ $question->id }}</span>
            </div>

            <div class="q-meta">
                <span class="q-meta-item"><span class="q-badge q-badge-type">{{ $question->examType?->name ?? '-' }}</span></span>
                @if ($question->questionUnit)
                    <span class="q-meta-item"><strong>Unit:</strong> {{ $question->questionUnit->name }}</span>
                @endif
                @if ($question->questionSubUnit)
                    <span class="q-meta-item"><strong>Sub:</strong> {{ $question->questionSubUnit->name }}</span>
                @endif
                @if ($question->category)
                    <span class="q-meta-item"><span class="q-badge {{ $catBadge['cls'] }}">{{ $catBadge['lbl'] }}</span></span>
                @endif
            </div>

            <div class="q-body">
                {{-- Question text (plain text, no HTML/images) --}}
                <div class="q-text">{{ $question->question_text }}</div>

                {{-- Image links --}}
                @if (!empty($imageLinks))
                    <div class="img-links">
                        <div class="img-links-label">Lampiran Gambar:</div>
                        @foreach ($imageLinks as $url)
                            {{ $url }}<br>
                        @endforeach
                    </div>
                @endif

                {{-- Options --}}
                @if (count($options) > 0)
                    <table class="opts-table">
                        @foreach ($options as $oi => $opt)
                            @php
                                $letter    = chr(65 + (int) $oi);
                                $isCorrect = !empty($opt['is_correct']);
                                $score     = $opt['score'] ?? null;
                                $optImgLinks = $opt['image_links'] ?? [];
                            @endphp
                            <tr>
                                <td class="opt-ltr">
                                    <span class="opt-circle {{ ($includeAnswerKey && $isCorrect) ? 'opt-circle-correct' : '' }}">{{ $letter }}</span>
                                </td>
                                <td class="opt-txt">
                                    {{ $opt['answer_text'] ?? '' }}
                                    @if (!empty($optImgLinks))
                                        <div class="img-links" style="margin-top: 2px;">
                                            <span class="img-links-label">Gambar:</span>
                                            @foreach ($optImgLinks as $url)
                                                {{ $url }}<br>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                @if ($includeAnswerKey)
                                    <td class="opt-score">
                                        @if ($isCorrect)
                                            <span class="opt-correct-tag">BENAR</span>
                                        @endif
                                        @if ($score !== null && $score !== '')
                                            <br>Skor: {{ $score }}
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>
        </div>
    @endforeach

    {{-- ── ANSWER KEY ──────────────────────────────────────── --}}
    @if ($includeAnswerKey && $questions->count() > 0)
        <div class="ak-section">
            <div class="ak-title">Kunci Jawaban</div>
            <table class="ak-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No.</th>
                        <th style="width:50px;">ID</th>
                        <th>Tipe</th>
                        <th>Unit</th>
                        <th style="width:55px;">Kategori</th>
                        <th style="width:65px;">Jawaban</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($questions as $idx => $q)
                        @php
                            $corrects = [];
                            foreach (($q->options ?? []) as $oi => $o) {
                                if (!empty($o['is_correct'])) {
                                    $corrects[] = chr(65 + (int) $oi);
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $q->id }}</td>
                            <td>{{ $q->examType?->name ?? '-' }}</td>
                            <td>{{ $q->questionUnit?->name ?? '-' }}</td>
                            <td>{{ match($q->category) { 'easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit', default => '-' } }}</td>
                            <td class="ak-correct">{{ !empty($corrects) ? implode(', ', $corrects) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── FOOTER ──────────────────────────────────────────── --}}
    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem CAT BAPETEN &bull; {{ now()->translatedFormat('d F Y, H:i') }}
    </div>

</body>
</html>
