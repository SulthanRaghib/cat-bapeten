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

        /* Filter meta */
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

        .summary-row .value.success { color: #1A6B3C; }
        .summary-row .value.danger { color: #C0392B; }
        .summary-row .value.warning { color: #E67E22; }

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

        table.data-table tbody td {
            padding: 4px 4px;
            border: 1px solid #ddd;
            font-size: 8px;
            vertical-align: middle;
        }

        table.data-table tbody tr:nth-child(even) {
            background: #f7f9fb;
        }

        table.data-table tbody tr:hover {
            background: #eef2f7;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

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

        .stat-correct { color: #1A6B3C; font-weight: bold; }
        .stat-wrong { color: #C0392B; font-weight: bold; }
        .stat-unanswered { color: #E67E22; font-weight: bold; }

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
    @if(! empty($filterMeta))
        <div class="filter-meta">
            <strong>Filter Aktif:</strong>
            @foreach($filterMeta as $key => $value)
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

    {{-- ── DATA TABLE ───────────────────────────────────── --}}
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
                @if($includeStatistics)
                    <th>Benar</th>
                    <th>Salah</th>
                    <th>Kosong</th>
                @endif
                <th>Pelanggaran</th>
                <th>Nilai</th>
                <th>KKM</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $row['nama'] }}</td>
                    <td class="text-left">{{ $row['nip'] }}</td>
                    <td class="text-left">{{ $row['paket_ujian'] }}</td>
                    <td class="text-center">{{ $row['tanggal'] }}</td>
                    <td class="text-center">{{ $row['waktu_mulai'] }}</td>
                    <td class="text-center">{{ $row['waktu_selesai'] }}</td>
                    <td class="text-center">{{ $row['durasi'] }}</td>
                    @if($includeStatistics)
                        <td class="text-center stat-correct">{{ $row['benar'] }}</td>
                        <td class="text-center stat-wrong">{{ $row['salah'] }}</td>
                        <td class="text-center stat-unanswered">{{ $row['tidak_dijawab'] }}</td>
                    @endif
                    <td class="text-center" style="{{ $row['pelanggaran'] > 0 ? 'color: #C0392B; font-weight: bold;' : '' }}">{{ $row['pelanggaran'] }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $row['nilai'] }}</td>
                    <td class="text-center">{{ $row['kkm'] }}</td>
                    <td class="text-center">
                        <span class="badge {{ $row['is_lulus'] ? 'badge-success' : 'badge-danger' }}">
                            {{ $row['status'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $includeStatistics ? 15 : 12 }}" class="text-center" style="padding: 20px; color: #999;">
                        Tidak ada data hasil ujian yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── FOOTER ───────────────────────────────────────── --}}
    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh Sistem CAT BAPETEN &mdash;
        {{ now()->format('d F Y H:i:s') }} WIB
        &bull; Halaman {PAGE_NUM} dari {PAGE_COUNT}
    </div>

</body>
</html>
