<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bank Soal — Export PDF</title>
    <style>
        @page {
            margin: 20mm 15mm 16mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5px;
            color: #1a1a2e;
            line-height: 1.6;
            background: #fff;
        }

        /* ── COVER ──────────────────────────────────────────────────────────── */
        .cover {
            background: #16213e;
            color: #fff;
            padding: 20px 24px 18px;
            margin-bottom: 0;
        }

        .cover-top {
            border-bottom: 1px solid rgba(255, 255, 255, .15);
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .cover-org {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: #90caf9;
            font-weight: bold;
        }

        .cover-title {
            font-size: 22px;
            font-weight: bold;
            color: #fff;
            margin: 3px 0 2px;
            letter-spacing: -0.3px;
        }

        .cover-subtitle {
            font-size: 10px;
            color: rgba(255, 255, 255, .55);
        }

        .cover-meta-row {
            font-size: 8.5px;
            color: rgba(255, 255, 255, .65);
        }

        .cover-meta-row strong {
            color: #fff;
        }

        .cover-badge {
            display: inline-block;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .2);
            color: #fff;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: bold;
            margin-right: 8px;
            vertical-align: middle;
        }

        .cover-badge-label {
            display: inline-block;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, .45);
            vertical-align: middle;
        }

        /* ── ACCENT BAR ──────────────────────────────────────────────────────── */
        .accent-bar {
            background: #0d47a1;
            height: 4px;
            margin-bottom: 14px;
        }

        /* ── FILTER STRIP ────────────────────────────────────────────────────── */
        .filter-strip {
            border: 1px solid #bbdefb;
            background: #e3f2fd;
            padding: 7px 12px;
            margin-bottom: 14px;
            font-size: 8.5px;
        }

        .filter-strip-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0d47a1;
            margin-bottom: 4px;
        }

        .filter-table {
            width: 100%;
            border-collapse: collapse;
        }

        .filter-table td {
            padding: 1.5px 6px 1.5px 0;
            vertical-align: top;
        }

        .filter-key {
            color: #546e7a;
            width: 110px;
        }

        .filter-val {
            color: #0d47a1;
            font-weight: bold;
        }

        /* ── STATS ───────────────────────────────────────────────────────────── */
        .stats {
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #0d47a1;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #0d47a1;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        .stats-outer {
            width: 100%;
            border-collapse: collapse;
        }

        .stats-outer td {
            vertical-align: top;
            padding-right: 8px;
        }

        .stats-outer td:last-child {
            padding-right: 0;
        }

        .stat-total-box {
            background: #0d47a1;
            color: #fff;
            padding: 10px 14px;
            text-align: center;
        }

        .stat-total-num {
            font-size: 28px;
            font-weight: bold;
            line-height: 1;
        }

        .stat-total-lbl {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, .7);
            margin-top: 2px;
        }

        .stat-group-box {
            border: 1px solid #e0e0e0;
            padding: 6px 10px 4px;
            height: 100%;
        }

        .stat-group-title {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #90a4ae;
            font-weight: bold;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        .stat-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .stat-row td {
            padding: 1.5px 0;
            font-size: 8.5px;
            border-bottom: 1px dotted #f0f0f0;
            vertical-align: middle;
        }

        .stat-row tr:last-child td {
            border-bottom: none;
        }

        .stat-row-dot {
            width: 8px;
        }

        .stat-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #0d47a1;
            vertical-align: middle;
        }

        .stat-row-name {
            color: #455a64;
        }

        .stat-row-num {
            text-align: right;
            font-weight: bold;
            color: #1a1a2e;
            white-space: nowrap;
            padding-left: 6px;
        }

        .divider {
            border: none;
            border-top: 2px solid #e8eaf6;
            margin: 14px 0 12px;
        }

        /* ── QUESTION CARD ───────────────────────────────────────────────────── */
        .q-card {
            border: 1px solid #e0e0e0;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .q-head {
            background: #0d47a1;
            color: #fff;
            padding: 5px 10px;
            overflow: hidden;
        }

        .q-head-left {
            float: left;
            font-size: 9.5px;
            font-weight: bold;
        }

        .q-head-right {
            float: right;
            font-size: 7.5px;
            color: rgba(255, 255, 255, .55);
            padding-top: 1px;
        }

        .q-head-clear {
            clear: both;
        }

        .q-meta {
            background: #f8f9ff;
            border-bottom: 1px solid #e8eaf6;
            padding: 4px 10px;
            font-size: 8px;
            color: #546e7a;
            overflow: hidden;
        }

        .q-meta-inner {
            overflow: hidden;
        }

        .q-meta-item {
            float: left;
            margin-right: 14px;
            padding: 1px 0;
        }

        .q-meta-item:last-child {
            margin-right: 0;
        }

        .q-meta-label {
            color: #90a4ae;
            margin-right: 3px;
        }

        .q-meta-clear {
            clear: both;
        }

        .q-badge {
            display: inline-block;
            padding: 1px 7px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 2px;
        }

        .q-badge-type {
            background: #e3f2fd;
            color: #0d47a1;
            border: 1px solid #90caf9;
        }

        .q-badge-easy {
            background: #e8f5e9;
            color: #1b5e20;
            border: 1px solid #a5d6a7;
        }

        .q-badge-medium {
            background: #fff8e1;
            color: #f57f17;
            border: 1px solid #ffe082;
        }

        .q-badge-hard {
            background: #fce4ec;
            color: #880e4f;
            border: 1px solid #f48fb1;
        }

        .q-badge-unknown {
            background: #f5f5f5;
            color: #9e9e9e;
            border: 1px solid #e0e0e0;
        }

        .q-body {
            padding: 8px 10px 6px;
        }

        .q-text {
            font-size: 9.5px;
            line-height: 1.65;
            margin-bottom: 8px;
            color: #1a1a2e;
        }

        /* Image links */
        .img-links {
            margin: 4px 0 6px;
            padding: 4px 8px;
            background: #f1f8e9;
            border-left: 3px solid #7cb342;
            font-size: 8px;
            color: #33691e;
            word-break: break-all;
        }

        .img-links-label {
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        /* Options */
        .opts-wrap {
            background: #fafafa;
            border: 1px solid #f0f0f0;
            padding: 4px 2px;
        }

        .opts-table {
            width: 100%;
            border-collapse: collapse;
        }

        .opts-table tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .opts-table tr:last-child {
            border-bottom: none;
        }

        .opts-table tr.opt-correct-row {
            background: #f1f8e9;
        }

        .opt-ltr {
            width: 26px;
            padding: 4px 4px 4px 6px;
            vertical-align: top;
            text-align: center;
        }

        .opt-circle {
            display: inline-block;
            width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 8.5px;
            color: #fff;
            background: #78909c;
        }

        .opt-circle-correct {
            background: #2e7d32;
        }

        .opt-txt {
            padding: 4px 4px;
            vertical-align: top;
            font-size: 9px;
            color: #263238;
        }

        .opt-score {
            width: 55px;
            padding: 4px 8px 4px 0;
            text-align: right;
            vertical-align: top;
            font-size: 8px;
        }

        .opt-correct-tag {
            display: inline-block;
            background: #2e7d32;
            color: #fff;
            padding: 1px 5px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 2px;
        }

        .opt-score-val {
            color: #546e7a;
        }

        /* ── ANSWER KEY ──────────────────────────────────────────────────────── */
        .ak-section {
            page-break-before: always;
            padding-top: 4px;
        }

        .ak-title-wrap {
            background: #16213e;
            color: #fff;
            padding: 10px 14px;
            margin-bottom: 12px;
            text-align: center;
        }

        .ak-title {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: -0.2px;
        }

        .ak-subtitle {
            font-size: 8px;
            color: rgba(255, 255, 255, .55);
            margin-top: 2px;
        }

        .ak-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        .ak-table thead tr {
            background: #0d47a1;
        }

        .ak-table thead th {
            color: #fff;
            padding: 5px 7px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ak-table tbody td {
            padding: 4px 7px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .ak-table tbody tr:nth-child(even) {
            background: #f8f9ff;
        }

        .ak-table tbody tr:hover {
            background: #e3f2fd;
        }

        .ak-correct {
            font-weight: bold;
            color: #2e7d32;
            font-size: 9.5px;
        }

        .ak-num {
            color: #9e9e9e;
            font-size: 8px;
        }

        .ak-id {
            color: #78909c;
            font-size: 8px;
        }

        .ak-diff {
            font-size: 8px;
        }

        /* ── FOOTER ──────────────────────────────────────────────────────────── */
        .page-footer {
            text-align: center;
            font-size: 7px;
            color: #bdbdbd;
            margin-top: 18px;
            padding-top: 6px;
            border-top: 1px solid #eee;
        }

        .page-footer strong {
            color: #90a4ae;
        }
    </style>
</head>

<body>

    {{-- ── COVER ───────────────────────────────────────────────────────────── --}}
    <div class="cover">
        <div class="cover-top">
            <div class="cover-org">Badan Pengawas Tenaga Nuklir &nbsp;·&nbsp; Sistem CAT BAPETEN</div>
            <div class="cover-title">Bank Soal</div>
            <div class="cover-subtitle">Dokumen Ekspor Resmi &mdash; Hanya untuk Penggunaan Internal</div>
        </div>
        <div>
            <span class="cover-badge">{{ $questions->count() }}</span>
            <span class="cover-badge-label">Soal</span>
            &nbsp;&nbsp;
            <span class="cover-meta-row">
                Diekspor pada <strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong>
            </span>
        </div>
    </div>
    <div class="accent-bar"></div>

    {{-- ── FILTER STRIP ────────────────────────────────────────────────────── --}}
    @if (!empty($filterMeta))
        <div class="filter-strip">
            <div class="filter-strip-title">&#9906; Filter Aktif</div>
            <table class="filter-table">
                @foreach ($filterMeta as $label => $value)
                    <tr>
                        <td class="filter-key">{{ $label }}</td>
                        <td style="color:#546e7a; padding-right:6px;">:</td>
                        <td class="filter-val">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- ── STATISTICS ──────────────────────────────────────────────────────── --}}
    <div class="stats">
        <div class="section-title">Ringkasan Soal</div>
        <table class="stats-outer">
            <tr>
                {{-- Total --}}
                <td style="width: 80px;">
                    <div class="stat-total-box">
                        <div class="stat-total-num">{{ $stats['total'] ?? $questions->count() }}</div>
                        <div class="stat-total-lbl">Total Soal</div>
                    </div>
                </td>

                {{-- Per Tipe --}}
                <td>
                    <div class="stat-group-box">
                        <div class="stat-group-title">Per Tipe Soal</div>
                        <table class="stat-row">
                            @forelse ($stats['by_type'] ?? [] as $name => $cnt)
                                <tr>
                                    <td class="stat-row-dot"><span class="stat-dot"></span></td>
                                    <td class="stat-row-name">{{ $name }}</td>
                                    <td class="stat-row-num">{{ $cnt }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="color:#bbb; font-size:8px;">—</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </td>

                {{-- Per Tingkat Kesulitan --}}
                <td>
                    <div class="stat-group-box">
                        <div class="stat-group-title">Per Tingkat Kesulitan</div>
                        <table class="stat-row">
                            @forelse ($stats['by_difficulty'] ?? [] as $name => $cnt)
                                @php
                                    $dotColor = match ($name) {
                                        'Mudah' => '#2e7d32',
                                        'Sedang' => '#f57f17',
                                        'Sulit' => '#c62828',
                                        default => '#90a4ae',
                                    };
                                @endphp
                                <tr>
                                    <td class="stat-row-dot"><span class="stat-dot"
                                            style="background:{{ $dotColor }}"></span></td>
                                    <td class="stat-row-name">{{ $name }}</td>
                                    <td class="stat-row-num">{{ $cnt }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="color:#bbb; font-size:8px;">—</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </td>

                {{-- Per Unit --}}
                <td>
                    <div class="stat-group-box">
                        <div class="stat-group-title">Per Unit (Materi)</div>
                        <table class="stat-row">
                            @forelse ($stats['by_unit'] ?? [] as $name => $cnt)
                                <tr>
                                    <td class="stat-row-dot"><span class="stat-dot" style="background:#546e7a"></span>
                                    </td>
                                    <td class="stat-row-name">{{ $name }}</td>
                                    <td class="stat-row-num">{{ $cnt }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="color:#bbb; font-size:8px;">—</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <hr class="divider">

    {{-- ── QUESTIONS ───────────────────────────────────────────────────────── --}}
    <div class="section-title">Daftar Soal</div>

    @foreach ($questions as $index => $question)
        @php
            $catBadge = match ($question->category) {
                'easy' => ['cls' => 'q-badge-easy', 'lbl' => 'Mudah'],
                'medium' => ['cls' => 'q-badge-medium', 'lbl' => 'Sedang'],
                'hard' => ['cls' => 'q-badge-hard', 'lbl' => 'Sulit'],
                default => ['cls' => 'q-badge-unknown', 'lbl' => '—'],
            };
            $options = $question->options ?? [];
            $imageLinks = $question->question_image_links ?? [];
        @endphp

        <div class="q-card">

            {{-- Head band --}}
            <div class="q-head">
                <span class="q-head-left">Soal &nbsp;{{ $index + 1 }}</span>
                <span class="q-head-right">ID #{{ $question->id }}</span>
                <div class="q-head-clear"></div>
            </div>

            {{-- Meta bar --}}
            <div class="q-meta">
                <div class="q-meta-inner">
                    {{-- Type badge --}}
                    <span class="q-meta-item">
                        <span class="q-badge q-badge-type">{{ $question->examType?->name ?? 'Tipe —' }}</span>
                    </span>

                    {{-- Unit --}}
                    @if ($question->questionUnit)
                        <span class="q-meta-item">
                            <span class="q-meta-label">Unit</span>{{ $question->questionUnit->name }}
                        </span>
                    @endif

                    {{-- Sub Unit --}}
                    @if ($question->questionSubUnit)
                        <span class="q-meta-item">
                            <span class="q-meta-label">Sub Unit</span>{{ $question->questionSubUnit->name }}
                        </span>
                    @endif

                    {{-- Difficulty --}}
                    <span class="q-meta-item">
                        <span class="q-meta-label">Tingkat Kesulitan</span>
                        <span class="q-badge {{ $catBadge['cls'] }}">{{ $catBadge['lbl'] }}</span>
                    </span>
                </div>
                <div class="q-meta-clear"></div>
            </div>

            {{-- Body --}}
            <div class="q-body">
                <div class="q-text">{{ $question->question_text }}</div>

                {{-- Image links --}}
                @if (!empty($imageLinks))
                    <div class="img-links">
                        <div class="img-links-label">&#128248; Lampiran Gambar</div>
                        @foreach ($imageLinks as $url)
                            {{ $url }}<br>
                        @endforeach
                    </div>
                @endif

                {{-- Options --}}
                @if (count($options) > 0)
                    <div class="opts-wrap">
                        <table class="opts-table">
                            @foreach ($options as $oi => $opt)
                                @php
                                    $letter = chr(65 + (int) $oi);
                                    $isCorrect = !empty($opt['is_correct']);
                                    $score = $opt['score'] ?? null;
                                    $optImgLinks = $opt['image_links'] ?? [];
                                @endphp
                                <tr class="{{ $includeAnswerKey && $isCorrect ? 'opt-correct-row' : '' }}">
                                    <td class="opt-ltr">
                                        <span
                                            class="opt-circle {{ $includeAnswerKey && $isCorrect ? 'opt-circle-correct' : '' }}">{{ $letter }}</span>
                                    </td>
                                    <td class="opt-txt">
                                        {{ $opt['answer_text'] ?? '' }}
                                        @if (!empty($optImgLinks))
                                            <div class="img-links" style="margin-top: 3px;">
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
                                                <span class="opt-correct-tag">BENAR</span><br>
                                            @endif
                                            @if ($score !== null && $score !== '')
                                                <span class="opt-score-val">Skor: {{ $score }}</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    {{-- ── ANSWER KEY ──────────────────────────────────────────────────────── --}}
    @if ($includeAnswerKey && $questions->count() > 0)
        <div class="ak-section">
            <div class="ak-title-wrap">
                <div class="ak-title">Kunci Jawaban</div>
                <div class="ak-subtitle">{{ $questions->count() }} soal &nbsp;&bull;&nbsp; Diekspor
                    {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
            </div>

            <table class="ak-table">
                <thead>
                    <tr>
                        <th style="width:32px;">No.</th>
                        <th style="width:42px;">ID</th>
                        <th style="width:90px;">Tipe</th>
                        <th>Unit</th>
                        <th>Sub Unit</th>
                        <th style="width:68px;">Tk. Kesulitan</th>
                        <th style="width:60px;">Jawaban</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($questions as $idx => $q)
                        @php
                            $corrects = [];
                            foreach ($q->options ?? [] as $oi => $o) {
                                if (!empty($o['is_correct'])) {
                                    $corrects[] = chr(65 + (int) $oi);
                                }
                            }
                            $diffLabel = match ($q->category) {
                                'easy' => 'Mudah',
                                'medium' => 'Sedang',
                                'hard' => 'Sulit',
                                default => '—',
                            };
                            $diffColor = match ($q->category) {
                                'easy' => '#2e7d32',
                                'medium' => '#f57f17',
                                'hard' => '#c62828',
                                default => '#9e9e9e',
                            };
                        @endphp
                        <tr>
                            <td class="ak-num">{{ $idx + 1 }}</td>
                            <td class="ak-id">{{ $q->id }}</td>
                            <td>{{ $q->examType?->name ?? '—' }}</td>
                            <td>{{ $q->questionUnit?->name ?? '—' }}</td>
                            <td>{{ $q->questionSubUnit?->name ?? '—' }}</td>
                            <td class="ak-diff" style="color:{{ $diffColor }}; font-weight:bold;">
                                {{ $diffLabel }}</td>
                            <td class="ak-correct">{{ !empty($corrects) ? implode(', ', $corrects) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── FOOTER ──────────────────────────────────────────────────────────── --}}
    <div class="page-footer">
        Dokumen ini dihasilkan secara otomatis &bull; <strong>Sistem CAT BAPETEN</strong> &bull;
        {{ now()->translatedFormat('d F Y, H:i') }} WIB
    </div>

</body>

</html>

<head>
    <meta charset="UTF-8">
    <title>Bank Soal - Export PDF</title>
    <style>
        @page {
            margin: 18mm 14mm 18mm 14mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .cover-org {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #0d47a1;
            font-weight: bold;
        }

        .cover-title {
            font-size: 20px;
            font-weight: bold;
            color: #0d47a1;
            margin: 4px 0;
        }

        .cover-sub {
            font-size: 9.5px;
            color: #666;
        }

        /* ── Filter Strip ────────────────────────────────────── */
        .filter-strip {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            padding: 6px 12px;
            margin-bottom: 14px;
            font-size: 9px;
            color: #1565c0;
        }

        .filter-strip strong {
            color: #0d47a1;
        }

        .filter-item {
            display: inline-block;
            margin-right: 14px;
        }

        /* ── Stats ───────────────────────────────────────────── */
        .stats {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .stats-heading {
            font-size: 10px;
            font-weight: bold;
            color: #0d47a1;
            border-bottom: 2px solid #0d47a1;
            padding-bottom: 2px;
            margin-bottom: 8px;
        }

        .stats-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .stats-grid td {
            vertical-align: top;
            padding: 0 6px 0 0;
            width: 33.33%;
        }

        .stat-box {
            border: 1px solid #e0e0e0;
            padding: 6px 8px;
            margin-bottom: 4px;
            background: #fafafa;
        }

        .stat-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #999;
        }

        .stat-val {
            font-size: 16px;
            font-weight: bold;
            color: #0d47a1;
        }

        .stat-row {
            font-size: 9px;
            color: #555;
            padding: 1px 0;
            border-bottom: 1px dotted #e0e0e0;
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-row-label {
            display: inline-block;
            width: 68%;
        }

        .stat-row-count {
            display: inline-block;
            width: 30%;
            text-align: right;
            font-weight: bold;
            color: #333;
        }

        .divider {
            border: none;
            border-top: 2px solid #0d47a1;
            margin: 14px 0 12px;
        }

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

        .q-head .q-id {
            float: right;
            font-weight: normal;
            font-size: 8px;
            color: rgba(255, 255, 255, .6);
        }

        .q-meta {
            background: #eceff1;
            padding: 4px 12px;
            font-size: 8.5px;
            color: #455a64;
            border-bottom: 1px solid #ddd;
        }

        .q-meta-item {
            display: inline-block;
            margin-right: 12px;
        }

        .q-badge {
            display: inline-block;
            padding: 0 6px;
            font-size: 8px;
            font-weight: bold;
        }

        .q-badge-type {
            background: #0d47a1;
            color: #fff;
        }

        .q-badge-easy {
            background: #c8e6c9;
            color: #2e7d32;
        }

        .q-badge-medium {
            background: #fff3e0;
            color: #e65100;
        }

        .q-badge-hard {
            background: #ffcdd2;
            color: #c62828;
        }

        /* Body */
        .q-body {
            padding: 8px 12px;
        }

        .q-text {
            font-size: 10px;
            line-height: 1.6;
            margin-bottom: 6px;
        }

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

        .img-links-label {
            font-weight: bold;
            color: #0d47a1;
            font-size: 8px;
            text-transform: uppercase;
        }

        /* Options */
        .opts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .opts-table tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .opts-table tr:last-child {
            border-bottom: none;
        }

        .opt-ltr {
            width: 24px;
            padding: 4px 2px;
            text-align: center;
            vertical-align: top;
        }

        .opt-circle {
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 9px;
            color: #fff;
            background: #546e7a;
        }

        .opt-circle-correct {
            background: #2e7d32;
        }

        .opt-txt {
            padding: 4px;
            vertical-align: top;
            font-size: 9.5px;
            color: #333;
        }

        .opt-score {
            width: 60px;
            padding: 4px;
            text-align: right;
            vertical-align: top;
            font-size: 8px;
            color: #888;
        }

        .opt-correct-tag {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 0 4px;
            font-size: 7px;
            font-weight: bold;
        }

        /* ── Answer Key ──────────────────────────────────────── */
        .ak-section {
            page-break-before: always;
        }

        .ak-title {
            font-size: 13px;
            font-weight: bold;
            color: #0d47a1;
            text-align: center;
            padding: 12px 0 8px;
            border-bottom: 3px solid #0d47a1;
            margin-bottom: 12px;
        }

        .ak-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .ak-table thead th {
            background: #0d47a1;
            color: #fff;
            padding: 5px 6px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
        }

        .ak-table tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #e0e0e0;
        }

        .ak-table tbody tr:nth-child(even) {
            background: #f5f5f5;
        }

        .ak-correct {
            font-weight: bold;
            color: #2e7d32;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 7.5px;
            color: #bbb;
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }
    </style>
</head>

<body>

    {{-- ── HEADER ──────────────────────────────────────────── --}}
    <div class="cover">
        <div class="cover-org">Badan Pengawas Tenaga Nuklir</div>
        <div class="cover-title">Bank Soal</div>
        <div class="cover-sub">Diekspor pada {{ now()->translatedFormat('l, d F Y &mdash; H:i') }} WIB &bull;
            {{ $questions->count() }} soal</div>
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
                        @foreach ($stats['by_type'] ?? [] as $name => $cnt)
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
                        @foreach ($stats['by_category'] ?? [] as $name => $cnt)
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
            $catBadge = match ($question->category) {
                'easy' => ['cls' => 'q-badge-easy', 'lbl' => 'Mudah'],
                'medium' => ['cls' => 'q-badge-medium', 'lbl' => 'Sedang'],
                'hard' => ['cls' => 'q-badge-hard', 'lbl' => 'Sulit'],
                default => ['cls' => '', 'lbl' => '-'],
            };
            $options = $question->options ?? [];
            $imageLinks = $question->question_image_links ?? [];
        @endphp

        <div class="q-card">
            <div class="q-head">
                Soal {{ $index + 1 }}
                <span class="q-id">ID: {{ $question->id }}</span>
            </div>

            <div class="q-meta">
                <span class="q-meta-item"><span
                        class="q-badge q-badge-type">{{ $question->examType?->name ?? '-' }}</span></span>
                @if ($question->questionUnit)
                    <span class="q-meta-item"><strong>Unit:</strong> {{ $question->questionUnit->name }}</span>
                @endif
                @if ($question->questionSubUnit)
                    <span class="q-meta-item"><strong>Sub:</strong> {{ $question->questionSubUnit->name }}</span>
                @endif
                @if ($question->category)
                    <span class="q-meta-item"><span
                            class="q-badge {{ $catBadge['cls'] }}">{{ $catBadge['lbl'] }}</span></span>
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
                                $letter = chr(65 + (int) $oi);
                                $isCorrect = !empty($opt['is_correct']);
                                $score = $opt['score'] ?? null;
                                $optImgLinks = $opt['image_links'] ?? [];
                            @endphp
                            <tr>
                                <td class="opt-ltr">
                                    <span
                                        class="opt-circle {{ $includeAnswerKey && $isCorrect ? 'opt-circle-correct' : '' }}">{{ $letter }}</span>
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
                            foreach ($q->options ?? [] as $oi => $o) {
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
                            <td>{{ match ($q->category) {'easy' => 'Mudah','medium' => 'Sedang','hard' => 'Sulit',default => '-'} }}
                            </td>
                            <td class="ak-correct">{{ !empty($corrects) ? implode(', ', $corrects) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── FOOTER ──────────────────────────────────────────── --}}
    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem CAT BAPETEN &bull;
        {{ now()->translatedFormat('d F Y, H:i') }}
    </div>

</body>

</html>
