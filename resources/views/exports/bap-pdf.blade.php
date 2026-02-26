<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        @page {
            margin: 2cm 2.5cm 2cm 3cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            color: #000;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* ── Kop surat ── */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border: 0;
            margin-bottom: 4pt;
        }

        .kop-table td {
            border: 0 !important;
            padding: 0;
            vertical-align: middle;
        }

        .kop-logo-cell {
            width: 70pt;
            text-align: center;
            padding-right: 8pt;
        }

        .kop-logo-cell img {
            width: 65pt;
            height: auto;
            border: 0;
        }

        .kop-text-cell {
            text-align: center;
        }

        .kop-instansi {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13pt;
            font-weight: bold;
            line-height: 1.4;
        }

        .kop-biro {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.4;
        }

        .kop-alamat {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            line-height: 1.4;
            margin-top: 2pt;
        }

        .kop-homepage {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            line-height: 1.4;
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 2pt solid #000;
            margin: 5pt 0 12pt 0;
        }

        /* ── Judul ── */
        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.5;
            margin-bottom: 14pt;
        }

        /* ── Opening paragraph ── */
        .opening-para {
            font-size: 12pt;
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 10pt;
        }

        /* ── Detail table ── */
        .detail-table {
            width: 85%;
            margin: 0 auto 16pt auto;
            border-collapse: collapse;
            border: 0;
        }

        .detail-table td {
            font-size: 12pt;
            line-height: 1.5;
            padding: 2pt 0;
            vertical-align: top;
            border: 0 !important;
        }

        .col-label {
            width: 170pt;
        }

        .col-colon {
            width: 12pt;
            text-align: left;
        }

        .col-value {}

        /* ── Penutup ── */
        .closing {
            font-size: 12pt;
            line-height: 1.6;
            margin-bottom: 3pt;
        }

        .closing-last {
            font-size: 12pt;
            line-height: 1.6;
            margin-bottom: 20pt;
        }

        /* ── TTD ── */
        .ttd-place {
            font-size: 12pt;
            text-align: center;
            margin-bottom: 12pt;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            border: 0;
        }

        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 12pt;
            line-height: 1.5;
            padding: 0 8pt;
            border: 0 !important;
        }

        .ttd-title {
            margin-bottom: 2pt;
        }

        .ttd-space {
            height: 60pt;
        }

        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 1pt;
        }

        .ttd-nip {}
    </style>
</head>

<body>

    {{-- ── KOP SURAT ── --}}
    <div style="text-align:center; margin-bottom: 4pt;">
        @if (!empty($garuda_b64))
            <img src="{{ $garuda_b64 }}" alt="Garuda BAPETEN"
                style="width:65pt; height:auto; border:0; display:block; margin:0 auto 4pt auto;">
        @endif
        <div class="kop-instansi">BADAN PENGAWAS TENAGA NUKLIR</div>
        <div class="kop-biro">BIRO ORGANISASI DAN UMUM</div>
        <div class="kop-alamat">
            Alamat : Jl. Gajah Mada No. 8 Jakarta Pusat 10120. Telp. (+62-21) 6385 8269-70, 630 2164, 630 2485
            Fax. (+62-21) 6385 8275 Po Box. 4005 Jkt 10040
        </div>
        <div class="kop-homepage">
            Homepage : www.bapeten.go.id, E-mail:
            <span style="text-decoration:underline">info@bapeten.go.id</span>
        </div>
    </div>

    <hr class="divider">

    {{-- ── JUDUL ── --}}
    <div class="doc-title">
        BERITA ACARA<br>
        PELAKSANAAN UJI KOMPETENSI
    </div>

    {{-- ── PARAGRAF PEMBUKA ── --}}
    <p class="opening-para">
        Pada hari ini, <strong>{{ $hari }}</strong>, tanggal
        <strong>{{ $tanggal_angka }} {{ $bulan }} {{ $tahun }}</strong>
        telah dilaksanakan kegiatan Uji Kompetensi untuk pegawai di lingkungan
        Badan Pengawas Tenaga Nuklir dengan rincian sebagai berikut:
    </p>

    {{-- ── DETAIL ── --}}
    <table class="detail-table">
        <tr>
            <td class="col-label">Judul Kegiatan</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $judul_kegiatan }}</td>
        </tr>
        <tr>
            <td class="col-label">Lokasi</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $lokasi }}</td>
        </tr>
        <tr>
            <td class="col-label">Sesi</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $sesi }}</td>
        </tr>
        <tr>
            <td class="col-label">Jumlah Peserta Hadir</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $jumlah_peserta }} orang</td>
        </tr>
    </table>

    {{-- ── PENUTUP ── --}}
    <p class="closing">Dengan rincian peserta terlampir</p>
    <p class="closing-last">Demikian Berita Acara ini dibuat dengan sebenarnya.</p>

    {{-- ── TANGGAL & TTD ── --}}
    <p class="ttd-place">Jakarta, {{ $tanggal_ttd }}</p>

    <table class="ttd-table">
        <tr>
            <td>
                <div class="ttd-title">Panitia Pelaksana Uji Kompetensi</div>
            </td>
            <td>
                <div class="ttd-title">Kepala Biro Organisasi dan Umum</div>
            </td>
        </tr>
        <tr>
            <td class="ttd-space"></td>
            <td class="ttd-space"></td>
        </tr>
        <tr>
            <td>
                <div class="ttd-name">{{ $nama_panitia }}</div>
                <div class="ttd-nip">NIP. {{ $nip_panitia }}</div>
            </td>
            <td>
                <div class="ttd-name">{{ $nama_kepala_bou }}</div>
                <div class="ttd-nip">NIP. {{ $nip_kepala_bou }}</div>
            </td>
        </tr>
    </table>

</body>

</html>
