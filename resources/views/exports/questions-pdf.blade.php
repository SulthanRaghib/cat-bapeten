<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bank Soal — Export PDF</title>
    <style>
        @page {
            margin: 16mm 18mm 16mm 18mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5px;
            line-height: 1.55;
            color: #1f2937;
        }

        .pdf-content {
            padding: 0 2mm;
        }

        .muted {
            color: #6b7280;
        }

        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #0f172a;
            border-bottom: 1.8px solid #0f172a;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        .page-divider {
            border: none;
            border-top: 1px solid #d1d5db;
            margin: 12px 0 10px;
        }

        /* Header / Cover */
        .cover {
            border: 1px solid #0f172a;
            margin-bottom: 10px;
        }

        .cover-top {
            background: #0f172a;
            color: #ffffff;
            padding: 10px 12px;
        }

        .cover-org {
            font-size: 7.8px;
            text-transform: uppercase;
            letter-spacing: 1.7px;
            color: #cbd5e1;
            margin-bottom: 2px;
        }

        .cover-title {
            font-size: 19px;
            font-weight: 700;
            letter-spacing: -.2px;
            margin-bottom: 2px;
        }

        .cover-sub {
            font-size: 8.3px;
            color: #e2e8f0;
        }

        .cover-bottom {
            padding: 8px 12px;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
            font-size: 8.5px;
            color: #334155;
        }

        .cover-meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cover-meta-table td {
            vertical-align: middle;
            padding: 1px 0;
        }

        .cover-meta-left {
            width: 34%;
            white-space: nowrap;
        }

        .cover-meta-mid {
            width: 46%;
            white-space: nowrap;
            color: #475569;
        }

        .cover-meta-right {
            width: 20%;
            text-align: right;
            color: #64748b;
            font-size: 8px;
        }

        .cover-total {
            display: inline-block;
            background: #0f172a;
            color: #fff;
            font-weight: 700;
            min-width: 28px;
            text-align: center;
            padding: 1px 7px;
            border-radius: 10px;
            margin-right: 6px;
            font-size: 9px;
        }

        .cover-total-wrap {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            line-height: 1;
        }

        .cover-total-label {
            display: inline-block;
            line-height: 1;
            color: #334155;
            font-size: 8.8px;
        }

        /* Filter */
        .filters {
            border: 1px solid #dbeafe;
            background: #eff6ff;
            padding: 7px 10px;
            margin-bottom: 10px;
        }

        .filters-title {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #1d4ed8;
            margin-bottom: 4px;
        }

        .filters-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        .filters-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .filters-k {
            width: 120px;
            color: #475569;
        }

        .filters-v {
            font-weight: 700;
            color: #1e40af;
        }

        /* Summary */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .summary-table td {
            vertical-align: top;
            padding-right: 8px;
        }

        .summary-table td:last-child {
            padding-right: 0;
        }

        .summary-total {
            background: #0f172a;
            color: #fff;
            border: 1px solid #0f172a;
            text-align: center;
            padding: 10px 8px;
        }

        .summary-total-num {
            font-size: 26px;
            line-height: 1;
            font-weight: 700;
        }

        .summary-total-label {
            margin-top: 3px;
            font-size: 7.4px;
            letter-spacing: .7px;
            text-transform: uppercase;
            color: #cbd5e1;
        }

        .summary-box {
            border: 1px solid #e5e7eb;
            padding: 7px 8px;
            min-height: 76px;
        }

        .summary-box-title {
            font-size: 7.4px;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: #64748b;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 2px;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .summary-row {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.4px;
        }

        .summary-row td {
            padding: 1.2px 0;
            border-bottom: 1px dotted #f1f5f9;
            vertical-align: middle;
        }

        .summary-row tr:last-child td {
            border-bottom: none;
        }

        .summary-name {
            color: #334155;
        }

        .sum-diff-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 9px;
            font-size: 7.8px;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .sum-diff-easy {
            background: #ecfdf5;
            border-color: #bbf7d0;
            color: #166534;
        }

        .sum-diff-medium {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        .sum-diff-hard {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .sum-diff-na {
            background: #f9fafb;
            border-color: #e5e7eb;
            color: #6b7280;
        }

        .summary-count {
            text-align: right;
            color: #0f172a;
            font-weight: 700;
            white-space: nowrap;
            padding-left: 6px;
        }

        /* Questions */
        .q-card {
            border: 1px solid #e5e7eb;
            margin-bottom: 9px;
            page-break-inside: avoid;
        }

        .q-head {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 9px;
            overflow: hidden;
        }

        .q-head-left {
            float: left;
            font-weight: 700;
            color: #0f172a;
            font-size: 9.4px;
        }

        .q-head-right {
            float: right;
            font-size: 7.8px;
            color: #64748b;
            padding-top: 1px;
        }

        .q-clear {
            clear: both;
        }

        .q-meta {
            border-bottom: 1px solid #f1f5f9;
            padding: 4px 9px;
            font-size: 8px;
            color: #475569;
        }

        .q-meta .chip {
            display: inline-block;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 1px 6px;
            margin-right: 5px;
            margin-bottom: 3px;
            border-radius: 2px;
        }

        .chip-type {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
            font-weight: 700;
        }

        .chip-easy {
            background: #ecfdf5;
            border-color: #bbf7d0;
            color: #166534;
            font-weight: 700;
        }

        .chip-medium {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
            font-weight: 700;
        }

        .chip-hard {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
            font-weight: 700;
        }

        .chip-na {
            background: #f9fafb;
            border-color: #e5e7eb;
            color: #6b7280;
            font-weight: 700;
        }

        .q-body {
            padding: 8px 9px 7px;
        }

        .q-text {
            font-size: 9.4px;
            color: #111827;
            margin-bottom: 7px;
            line-height: 1.62;
        }

        .img-links {
            margin: 4px 0 7px;
            padding: 4px 7px;
            border-left: 2.5px solid #3b82f6;
            background: #eff6ff;
            font-size: 8px;
            color: #1e3a8a;
            word-break: break-all;
        }

        .img-links-title {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            font-size: 7.4px;
            margin-bottom: 2px;
            color: #1d4ed8;
        }

        .opt-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .opt-table td {
            border-bottom: 1px solid #e2e8f0;
        }

        .opt-table tr:last-child td {
            border-bottom: none;
        }

        .opt-table tr:nth-child(even) td.opt-text-cell,
        .opt-table tr:nth-child(even) td.opt-info-cell {
            background: #f8fafc;
        }

        .opt-correct-row td.opt-text-cell,
        .opt-correct-row td.opt-info-cell {
            background: #f0fdf4 !important;
        }

        .opt-label-cell {
            width: 24px;
            text-align: center;
            vertical-align: middle;
            padding: 5px 3px;
            border-right: 1px solid #e2e8f0;
            background: #64748b;
            color: #ffffff;
            font-size: 8px;
            font-weight: 700;
        }

        .opt-label-correct {
            background: #15803d !important;
        }

        .opt-text-cell {
            font-size: 8.9px;
            color: #1f2937;
            padding: 5px 8px;
            vertical-align: middle;
        }

        .opt-info-cell {
            width: 60px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            color: #475569;
            padding: 5px 6px;
            border-left: 1px solid #e2e8f0;
        }

        .opt-tag {
            display: block;
            background: #15803d;
            color: #fff;
            font-size: 7px;
            font-weight: 700;
            padding: 2px 4px;
            margin-bottom: 2px;
            text-align: center;
        }

        /* Answer key */
        .ak-section {
            page-break-before: always;
        }

        .ak-header {
            border: 1px solid #0f172a;
            background: #0f172a;
            color: #fff;
            text-align: center;
            padding: 9px 10px 7px;
            margin-bottom: 9px;
        }

        .ak-title {
            font-size: 12.5px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .ak-sub {
            font-size: 8px;
            color: #cbd5e1;
        }

        .ak-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.4px;
        }

        .ak-table thead th {
            background: #0f172a;
            color: #fff;
            text-align: left;
            padding: 5px 6px;
            font-size: 7.7px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .ak-table tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .ak-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .ak-answer {
            font-weight: 700;
            color: #166534;
        }

        .ak-diff-easy {
            background: #ecfdf5;
            border-color: #bbf7d0;
            color: #166534;
        }

        .ak-diff-medium {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        .ak-diff-hard {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .ak-diff-na {
            background: #f9fafb;
            border-color: #e5e7eb;
            color: #6b7280;
        }

        .ak-diff-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 9px;
            border: 1px solid transparent;
            font-size: 7.8px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Footer */
        .footer {
            margin-top: 13px;
            padding-top: 6px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 7.4px;
            color: #9ca3af;
        }
    </style>
</head>

<body>

    <div class="pdf-content">

    <div class="cover">
        <div class="cover-top">
            <div class="cover-org">Badan Pengawas Tenaga Nuklir · Sistem CAT BAPETEN</div>
            <div class="cover-title">Bank Soal</div>
            <div class="cover-sub">Dokumen ekspor untuk kebutuhan review, validasi, dan pengelolaan bank soal.</div>
        </div>
        <div class="cover-bottom">
            <table class="cover-meta-table">
                <tr>
                    <td class="cover-meta-left">
                        <span class="cover-total-wrap">
                            <span class="cover-total">{{ $questions->count() }}</span>
                            <span class="cover-total-label">soal</span>
                        </span>
                    </td>
                    <td class="cover-meta-mid">
                        Diekspor pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </td>
                    <td class="cover-meta-right">
                        {{ !empty($filterMeta) ? 'Dengan Filter' : 'Semua Data' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if (!empty($filterMeta))
        <div class="filters">
            <div class="filters-title">Filter Aktif</div>
            <table class="filters-table">
                @foreach ($filterMeta as $label => $value)
                    <tr>
                        <td class="filters-k">{{ $label }}</td>
                        <td style="width:10px; color:#64748b;">:</td>
                        <td class="filters-v">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <div class="section-title">Ringkasan</div>
    <table class="summary-table">
        <tr>
            <td style="width:74px;">
                <div class="summary-total">
                    <div class="summary-total-num">{{ $stats['total'] ?? $questions->count() }}</div>
                    <div class="summary-total-label">Total Soal</div>
                </div>
            </td>

            <td>
                <div class="summary-box">
                    <div class="summary-box-title">Per Tipe Soal</div>
                    <table class="summary-row">
                        @forelse (($stats['by_type'] ?? []) as $name => $cnt)
                            <tr>
                                <td class="summary-name">{{ $name }}</td>
                                <td class="summary-count">{{ $cnt }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="muted">Tidak ada data</td>
                                <td class="summary-count">0</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </td>

            <td>
                <div class="summary-box">
                    <div class="summary-box-title">Per Tingkat Kesulitan</div>
                    <table class="summary-row">
                        @forelse (($stats['by_difficulty'] ?? []) as $name => $cnt)
                            @php
                                $sumDiffClass = match ($name) {
                                    'Mudah' => 'sum-diff-easy',
                                    'Sedang' => 'sum-diff-medium',
                                    'Sulit' => 'sum-diff-hard',
                                    default => 'sum-diff-na',
                                };
                            @endphp
                            <tr>
                                <td class="summary-name"><span class="sum-diff-pill {{ $sumDiffClass }}">{{ $name }}</span></td>
                                <td class="summary-count">{{ $cnt }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="muted">Tidak ada data</td>
                                <td class="summary-count">0</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </td>

            <td>
                <div class="summary-box">
                    <div class="summary-box-title">Per Unit</div>
                    <table class="summary-row">
                        @forelse (($stats['by_unit'] ?? []) as $name => $cnt)
                            <tr>
                                <td class="summary-name">{{ $name }}</td>
                                <td class="summary-count">{{ $cnt }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="muted">Tidak ada data</td>
                                <td class="summary-count">0</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <hr class="page-divider">

    <div class="section-title">Daftar Soal</div>

    @foreach ($questions as $index => $question)
        @php
            $difficulty = match ($question->category) {
                'easy' => ['label' => 'Mudah', 'chip' => 'chip-easy'],
                'medium' => ['label' => 'Sedang', 'chip' => 'chip-medium'],
                'hard' => ['label' => 'Sulit', 'chip' => 'chip-hard'],
                default => ['label' => 'Tidak Ditentukan', 'chip' => 'chip-na'],
            };

            $options = $question->options ?? [];
            $imageLinks = $question->question_image_links ?? [];
        @endphp

        <div class="q-card">
            <div class="q-head">
                <div class="q-head-left">Soal {{ $index + 1 }}</div>
                <div class="q-head-right">ID #{{ $question->id }}</div>
                <div class="q-clear"></div>
            </div>

            <div class="q-meta">
                <span class="chip chip-type">{{ $question->examType?->name ?? 'Tipe Tidak Ditentukan' }}</span>
                <span class="chip">Unit: {{ $question->questionUnit?->name ?? '-' }}</span>
                <span class="chip">Sub Unit: {{ $question->questionSubUnit?->name ?? '-' }}</span>
                <span class="chip {{ $difficulty['chip'] }}">Tingkat Kesulitan: {{ $difficulty['label'] }}</span>
            </div>

            <div class="q-body">
                <div class="q-text">{{ $question->question_text }}</div>

                @if (!empty($imageLinks))
                    <div class="img-links">
                        <div class="img-links-title">Lampiran Gambar Soal</div>
                        @foreach ($imageLinks as $url)
                            {{ $url }}<br>
                        @endforeach
                    </div>
                @endif

                @if (count($options) > 0)
                    <table class="opt-table">
                        @foreach ($options as $oi => $opt)
                            @php
                                $letter = chr(65 + (int) $oi);
                                $isCorrect = !empty($opt['is_correct']);
                                $score = $opt['score'] ?? null;
                                $optImgLinks = $opt['image_links'] ?? [];
                            @endphp
                            <tr class="{{ $includeAnswerKey && $isCorrect ? 'opt-correct-row' : '' }}">
                                <td class="opt-label-cell {{ $includeAnswerKey && $isCorrect ? 'opt-label-correct' : '' }}">{{ $letter }}</td>
                                <td class="opt-text-cell">
                                    {{ $opt['answer_text'] ?? '' }}

                                    @if (!empty($optImgLinks))
                                        <div class="img-links" style="margin-top:3px; margin-bottom:2px;">
                                            <div class="img-links-title">Lampiran Gambar Opsi</div>
                                            @foreach ($optImgLinks as $url)
                                                {{ $url }}<br>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                @if ($includeAnswerKey)
                                    <td class="opt-info-cell">
                                        @if ($isCorrect)
                                            <span class="opt-tag">BENAR</span><br>
                                        @endif
                                        @if ($score !== null && $score !== '')
                                            Skor: {{ $score }}
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

    @if ($includeAnswerKey && $questions->count() > 0)
        <div class="ak-section">
            <div class="ak-header">
                <div class="ak-title">Kunci Jawaban</div>
                <div class="ak-sub">Total {{ $questions->count() }} soal • {{ now()->translatedFormat('d F Y, H:i') }}
                    WIB</div>
            </div>

            <table class="ak-table">
                <thead>
                    <tr>
                        <th style="width:34px;">No</th>
                        <th style="width:42px;">ID</th>
                        <th style="width:90px;">Tipe</th>
                        <th>Unit</th>
                        <th>Sub Unit</th>
                        <th style="width:80px;">Tk. Kesulitan</th>
                        <th style="width:62px;">Jawaban</th>
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

                            $difficultyClass = match ($q->category) {
                                'easy' => 'ak-diff-easy',
                                'medium' => 'ak-diff-medium',
                                'hard' => 'ak-diff-hard',
                                default => 'ak-diff-na',
                            };

                            $difficultyLabel = match ($q->category) {
                                'easy' => 'Mudah',
                                'medium' => 'Sedang',
                                'hard' => 'Sulit',
                                default => 'Tidak Ditentukan',
                            };
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $q->id }}</td>
                            <td>{{ $q->examType?->name ?? '-' }}</td>
                            <td>{{ $q->questionUnit?->name ?? '-' }}</td>
                            <td>{{ $q->questionSubUnit?->name ?? '-' }}</td>
                            <td><span class="ak-diff-pill {{ $difficultyClass }}">{{ $difficultyLabel }}</span></td>
                            <td class="ak-answer">{{ !empty($corrects) ? implode(', ', $corrects) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        Dokumen dibuat otomatis oleh Sistem CAT BAPETEN • {{ now()->translatedFormat('d F Y, H:i') }} WIB
    </div>

    </div>

</body>

</html>
