<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Ujian BAPETEN</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 3px solid #1E3A5F;
        }

        .header h1 {
            font-size: 16px;
            color: #1E3A5F;
            margin-bottom: 2px;
            letter-spacing: 1px;
        }

        .header .subtitle {
            font-size: 10px;
            color: #555;
        }

        .header .date {
            font-size: 8px;
            color: #888;
            margin-top: 4px;
        }

        .filter-meta {
            margin-bottom: 10px;
            padding: 6px 10px;
            background: #f0f4f8;
            border-left: 3px solid #1E3A5F;
            font-size: 8px;
        }

        .filter-meta span {
            display: inline-block;
            margin-right: 16px;
            color: #333;
        }

        .filter-meta strong {
            color: #1E3A5F;
        }

        /* Summary cards */
        .summary-row {
            margin-bottom: 12px;
            width: 100%;
        }

        .summary-row table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-row td {
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #ddd;
            font-size: 8px;
        }

        .summary-row .label {
            color: #666;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-row .value {
            font-size: 14px;
            font-weight: bold;
            color: #1E3A5F;
        }

        .summary-row .value.success {
            color: #1A6B3C;
        }

        .summary-row .value.danger {
            color: #C0392B;
        }

        /* Section heading */
        .section-heading {
            margin: 14px 0 6px;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
            border-radius: 3px;
        }

        .section-heading.teknis {
            background: #1E3A5F;
        }

        .section-heading.mansoskul {
            background: #6D28D9;
        }

        /* Main data table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.data-table thead th {
            background: #1E3A5F;
            color: #fff;
            font-size: 8px;
            font-weight: bold;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #4B8BBE;
            white-space: nowrap;
        }

        table.data-table.mansoskul thead th {
            background: #6D28D9;
            border-color: #a78bfa;
        }

        table.data-table tbody td {
            padding: 4px 4px;
            border: 1px solid #ddd;
            font-size: 8px;
            vertical-align: middle;
        }

        table.data-table tbody tr:nth-child(even) {
            background: #f7f9fb;
        }

        table.data-table.mansoskul tbody tr:nth-child(even) {
            background: #f5f3ff;
        }

        /* Unit sub-table inside mansoskul rows */
        .unit-subtable {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
            font-size: 7.5px;
        }

        .unit-subtable th {
            background: #ede9fe;
            color: #4c1d95;
            padding: 3px 4px;
            border: 1px solid #c4b5fd;
            font-weight: bold;
            text-align: center;
        }

        .unit-subtable td {
            padding: 3px 4px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        .unit-subtable tr.passing {
            background: #f0fdf4;
        }

        .unit-subtable tr.failing {
            background: #fff5f5;
        }

        /* Stage sub-table inside teknis rows (multi-stage scoring) */
        .stage-subtable {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
            font-size: 7.5px;
        }

        .stage-subtable th {
            background: #1E3A5F;
            color: #fff;
            padding: 3px 4px;
            border: 1px solid #4B8BBE;
            font-weight: bold;
            text-align: center;
        }

        .stage-subtable td {
            padding: 3px 4px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        .stage-subtable tr.cbt-row {
            background: #eff6ff;
        }

        .stage-subtable tr.stage-row {
            background: #f5f3ff;
        }

        .stage-subtable tr.total-row {
            background: #f0fdf4;
            font-weight: bold;
        }

        .stage-subtable tr.total-row.gagal {
            background: #fff5f5;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7px;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background: #d4edda;
            color: #1A6B3C;
        }

        .badge-danger {
            background: #f8d7da;
            color: #C0392B;
        }

        .badge-purple {
            background: #ede9fe;
            color: #5b21b6;
        }

        .stat-correct {
            color: #1A6B3C;
            font-weight: bold;
        }

        .stat-wrong {
            color: #C0392B;
            font-weight: bold;
        }

        .stat-unanswered {
            color: #E67E22;
            font-weight: bold;
        }

        .footer {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #ddd;
            font-size: 7px;
            color: #999;
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    {{-- ── HEADER ────────────────────────────────────────── --}}
    <div class="header">
        <h1>LAPORAN HASIL UJIAN</h1>
        <div class="subtitle">Badan Pengawas Tenaga Nuklir (BAPETEN)</div>
        <div class="date">Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</div>
    </div>

    {{-- ── FILTER META ───────────────────────────────────── --}}
    @if (!empty($filterMeta))
        <div class="filter-meta">
            <strong>Filter Aktif:</strong>
            @foreach ($filterMeta as $key => $value)
                <span><strong>{{ $key }}:</strong> {{ $value }}</span>
            @endforeach
        </div>
    @endif

    {{-- ── SUMMARY ROW ──────────────────────────────────── --}}
    <div class="summary-row">
        <table>
            <tr>
                <td>
                    <div class="label">Total Peserta</div>
                    <div class="value">{{ $summary['total_peserta'] }}</div>
                </td>
                <td>
                    <div class="label">Jumlah Lulus</div>
                    <div class="value success">{{ $summary['jumlah_lulus'] }}</div>
                </td>
                <td>
                    <div class="label">Jumlah Tidak Lulus</div>
                    <div class="value danger">{{ $summary['jumlah_gagal'] }}</div>
                </td>
                <td>
                    <div class="label">Rata-rata Nilai</div>
                    <div class="value">{{ $summary['rata_rata_nilai'] }}</div>
                </td>
                <td>
                    <div class="label">Nilai Tertinggi</div>
                    <div class="value success">{{ $summary['nilai_tertinggi'] }}</div>
                </td>
                <td>
                    <div class="label">Nilai Terendah</div>
                    <div class="value danger">{{ $summary['nilai_terendah'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ════════════════════════════════════════════════════
         BAGIAN 1 : UJIAN TEKNIS (Benar / Salah)
    ════════════════════════════════════════════════════ --}}
    @if ($teknis_results->isNotEmpty())
        <div class="section-heading teknis">
            UJIAN TEKNIS — Penilaian Benar / Salah{{ $has_staged_teknis ? ' + Tahap Seleksi Lanjutan' : '' }}
            ({{ $teknis_results->count() }} peserta)
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>NIP</th>
                    <th>Paket Ujian</th>
                    <th>Tanggal</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Durasi</th>
                    @if ($includeStatistics)
                        <th>Benar</th>
                        <th>Salah</th>
                        <th>Kosong</th>
                    @endif
                    <th>Pelanggaran</th>
                    <th>Nilai</th>
                    <th>NAB</th>
                    <th>Status</th>
                    @if ($has_staged_teknis)
                        <th style="min-width:200px;">Rincian Tahap Seleksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($teknis_results as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left">{{ $row['nama'] }}</td>
                        <td class="text-left">{{ $row['nip'] }}</td>
                        <td class="text-left">{{ $row['paket_ujian'] }}</td>
                        <td class="text-center">{{ $row['tanggal'] }}</td>
                        <td class="text-center">{{ $row['waktu_mulai'] }}</td>
                        <td class="text-center">{{ $row['waktu_selesai'] }}</td>
                        <td class="text-center">{{ $row['durasi'] }}</td>
                        @if ($includeStatistics)
                            <td class="text-center stat-correct">{{ $row['benar'] ?? 0 }}</td>
                            <td class="text-center stat-wrong">{{ $row['salah'] ?? 0 }}</td>
                            <td class="text-center stat-unanswered">{{ $row['tidak_dijawab'] ?? 0 }}</td>
                        @endif
                        <td class="text-center"
                            style="{{ ($row['pelanggaran'] ?? 0) > 0 ? 'color: #C0392B; font-weight: bold;' : '' }}">
                            {{ $row['pelanggaran'] ?? 0 }}
                        </td>
                        <td class="text-center" style="font-weight: bold;">{{ $row['nilai'] }}</td>
                        <td class="text-center">{{ $row['nab'] }}</td>
                        <td class="text-center">
                            <span class="badge {{ $row['is_lulus'] ? 'badge-success' : 'badge-danger' }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        @if ($has_staged_teknis)
                            <td>
                                @if (!empty($row['has_stages']) && !empty($row['stages_config']))
                                    @php
                                        $cbtScore = (float) ($row['cbt_score'] ?? 0);
                                        $cbtWeight = (float) ($row['cbt_weight'] ?? 100);
                                        $cbtContrib = round(($cbtScore * $cbtWeight) / 100, 2);
                                        $stageScores = $row['stage_scores'] ?? [];
                                    @endphp
                                    <table class="stage-subtable">
                                        <thead>
                                            <tr>
                                                <th>Komponen</th>
                                                <th>Nilai</th>
                                                <th>Bobot</th>
                                                <th>Nilai Terbobot</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- CBT row --}}
                                            <tr class="cbt-row">
                                                <td class="text-left">CBT</td>
                                                <td class="text-center" style="font-weight:bold; color:#1d4ed8;">
                                                    {{ number_format($cbtScore, 1, ',', '.') }}</td>
                                                <td class="text-center">{{ $cbtWeight }}%</td>
                                                <td class="text-center" style="color:#1d4ed8;">
                                                    {{ number_format($cbtContrib, 2, ',', '.') }}</td>
                                            </tr>
                                            {{-- Per-stage rows --}}
                                            @foreach ($row['stages_config'] as $si => $stage)
                                                @php
                                                    $stageLabel = $stage['label'] ?? 'Tahap ' . ($si + 1);
                                                    $stageWeight = (float) ($stage['weight'] ?? 0);
                                                    $stageScore = (float) ($stageScores['stage_' . $si] ?? 0);
                                                    $stageContrib = round(($stageScore * $stageWeight) / 100, 2);
                                                @endphp
                                                <tr class="stage-row">
                                                    <td class="text-left">{{ $stageLabel }}</td>
                                                    <td class="text-center" style="font-weight:bold; color:#6d28d9;">
                                                        {{ number_format($stageScore, 1, ',', '.') }}</td>
                                                    <td class="text-center">{{ $stageWeight }}%</td>
                                                    <td class="text-center" style="color:#6d28d9;">
                                                        {{ number_format($stageContrib, 2, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                            {{-- Total row --}}
                                            <tr class="total-row {{ $row['is_lulus'] ? '' : 'gagal' }}">
                                                <td class="text-left" colspan="3"
                                                    style="font-size:7px; text-transform:uppercase; letter-spacing:0.3px;">
                                                    Nilai Akhir Terbobot</td>
                                                <td class="text-center"
                                                    style="font-size:9px; color:{{ $row['is_lulus'] ? '#1A6B3C' : '#C0392B' }};">
                                                    {{ number_format((float) ($row['nilai'] ?? 0), 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @else
                                    <span style="color:#999; font-style:italic;">—</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $includeStatistics ? ($has_staged_teknis ? 16 : 15) : ($has_staged_teknis ? 13 : 12) }}"
                            class="text-center" style="padding: 12px; color: #999;">
                            Tidak ada data ujian teknis.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- ════════════════════════════════════════════════════
         BAGIAN 2 : UJIAN MANSOSKUL (Pembobotan per Unit)
    ════════════════════════════════════════════════════ --}}
    @if ($mansoskul_results->isNotEmpty())
        <div class="section-heading mansoskul">
            UJIAN MANSOSKUL — Penilaian Pembobotan Nilai per Unit
            ({{ $mansoskul_results->count() }} peserta)
        </div>

        <table class="data-table mansoskul">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>NIP</th>
                    <th>Paket Ujian</th>
                    <th>Tanggal</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Pelanggaran</th>
                    <th>Nilai Total</th>
                    <th>NAB</th>
                    <th>Unit Kompeten</th>
                    <th>Status</th>
                    <th style="min-width:220px;">Rincian Nilai per Unit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mansoskul_results as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left">{{ $row['nama'] }}</td>
                        <td class="text-left">{{ $row['nip'] }}</td>
                        <td class="text-left">{{ $row['paket_ujian'] }}</td>
                        <td class="text-center">{{ $row['tanggal'] }}</td>
                        <td class="text-center">{{ $row['waktu_mulai'] }}</td>
                        <td class="text-center">{{ $row['waktu_selesai'] }}</td>
                        <td class="text-center"
                            style="{{ ($row['pelanggaran'] ?? 0) > 0 ? 'color: #C0392B; font-weight: bold;' : '' }}">
                            {{ $row['pelanggaran'] ?? 0 }}
                        </td>
                        <td class="text-center" style="font-weight: bold;">
                            {{ number_format((float) ($row['nilai'] ?? 0), 2, ',', '.') }}
                        </td>
                        <td class="text-center">{{ $row['nab'] }}</td>
                        {{-- Unit kompeten progress --}}
                        <td class="text-center">
                            <span
                                style="font-weight:bold; color: {{ ($row['unit_lulus_count'] ?? 0) === ($row['unit_total_count'] ?? 0) ? '#1A6B3C' : '#C0392B' }}">
                                {{ $row['unit_lulus_count'] ?? 0 }}/{{ $row['unit_total_count'] ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $row['is_lulus'] ? 'badge-success' : 'badge-danger' }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        {{-- Per-unit sub table --}}
                        <td>
                            @if (!empty($row['unit_results']))
                                <table class="unit-subtable">
                                    <thead>
                                        <tr>
                                            <th>Unit</th>
                                            <th>Skor</th>
                                            <th>Indikator Dicapai</th>
                                            <th>Kompetensi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($row['unit_results'] as $unit)
                                            <tr class="{{ $unit['is_passing'] ? 'passing' : 'failing' }}">
                                                <td class="text-left">{{ $unit['unit_name'] }}</td>
                                                <td class="text-center"
                                                    style="font-weight:bold; color:{{ $unit['is_passing'] ? '#1A6B3C' : '#C0392B' }}">
                                                    {{ number_format((float) ($unit['total_score'] ?? 0), 2, ',', '.') }}
                                                </td>
                                                <td class="text-left" style="color: #6D28D9;">
                                                    {{ $unit['achieved_indicator'] ?: '—' }}
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        style="font-weight:bold; color:{{ $unit['is_passing'] ? '#1A6B3C' : '#C0392B' }}">
                                                        {{ $unit['is_passing'] ? 'KOMPETEN' : 'BELUM' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <span style="color:#999; font-style:italic;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center" style="padding: 12px; color: #999;">
                            Tidak ada data ujian mansoskul.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- Show generic table only if both result sets are empty (backward-compat fallback) --}}
    @if ($teknis_results->isEmpty() && $mansoskul_results->isEmpty())
        <p style="text-align:center; color:#999; padding:20px; font-style:italic;">
            Tidak ada data hasil ujian yang ditemukan.
        </p>
    @endif

    {{-- ── FOOTER ───────────────────────────────────────── --}}
    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh Sistem CAT BAPETEN &mdash;
        {{ now()->format('d F Y H:i:s') }} WIB
        &bull; Halaman {PAGE_NUM} dari {PAGE_COUNT}
    </div>

</body>

</html>
