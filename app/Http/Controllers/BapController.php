<?php

namespace App\Http\Controllers;

use App\Models\ExamPackage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;

class BapController extends Controller
{
    /**
     * Generate and stream BAP file.
     * Data is stored in cache with a one-time token.
     */
    public function download(Request $request)
    {
        $token = $request->query('token');
        $data  = cache('bap:' . $token);

        if (! $data) {
            abort(403, 'Link unduhan telah kedaluwarsa atau tidak valid. Silakan buat kembali dari halaman Hasil Ujian.');
        }

        cache()->forget('bap:' . $token);

        // Resolve related data
        $package = ExamPackage::find($data['exam_package_id'] ?? null);

        // Enrich $data with derived fields
        $tanggal = Carbon::parse($data['tanggal']);
        $hariIndonesia = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ][$tanggal->format('l')] ?? $tanggal->format('l');

        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $view_data = [
            'hari'           => $hariIndonesia,
            'tanggal_angka'  => $tanggal->day,
            'bulan'          => $bulanIndonesia[$tanggal->month],
            'tahun'          => $tanggal->year,
            'tanggal_ttd'    => $tanggal->day . ' ' . $bulanIndonesia[$tanggal->month] . ' ' . $tanggal->year,
            'judul_kegiatan' => $data['judul_kegiatan'] ?? ($package?->title ?? '-'),
            'lokasi'         => $data['lokasi'] ?? '-',
            'sesi'           => $data['sesi'] ?? '-',
            'jumlah_peserta' => $data['jumlah_peserta'] ?? 0,
            'nama_panitia'   => $data['nama_panitia'] ?? '-',
            'nip_panitia'    => $data['nip_panitia'] ?? '-',
            'nama_kepala_bou' => $data['nama_kepala_bou'] ?? '-',
            'nip_kepala_bou' => $data['nip_kepala_bou'] ?? '-',
            'garuda_path'    => public_path('assets/img/garuda.png'),
        ];

        $filename = 'BAP-Uji-Kompetensi-' . $tanggal->format('Ymd');

        $format = $data['format'] ?? 'pdf';

        if ($format === 'word') {
            return $this->generateWord($view_data, $filename);
        }

        return $this->generatePdf($view_data, $filename);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PDF via DomPDF
    // ─────────────────────────────────────────────────────────────────────────
    private function generatePdf(array $data, string $filename)
    {
        $pdf = Pdf::loadView('exports.bap-pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'serif',
                'dpi'                  => 150,
            ]);

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Word via PHPWord
    // ─────────────────────────────────────────────────────────────────────────
    private function generateWord(array $data, string $filename)
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);

        // ── Page settings ──
        $section = $phpWord->addSection([
            'marginTop'    => 900,
            'marginBottom' => 900,
            'marginLeft'   => 1800,
            'marginRight'  => 1440,
            'pageNumberingStart' => 1,
        ]);

        // ── Header: Garuda logo ──
        $header = $section->addHeader();
        $headerTable = $header->addTable(['borderSize' => 0, 'width' => 9000, 'unit' => 'twip']);
        $headerTable->addRow();
        $cell = $headerTable->addCell(9000, ['borderSize' => 0]);
        if (file_exists($data['garuda_path'])) {
            $cell->addImage($data['garuda_path'], [
                'width'  => 60,
                'height' => 60,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
        }

        // ── Letterhead ──
        $section->addText(
            'BADAN PENGAWAS TENAGA NUKLIR',
            ['bold' => true, 'size' => 13, 'name' => 'Arial'],
            ['alignment' => 'center']
        );
        $section->addText(
            'BIRO ORGANISASI DAN UMUM',
            ['bold' => true, 'size' => 13, 'name' => 'Arial'],
            ['alignment' => 'center']
        );
        $section->addText(
            'Alamat : Jl. Gajah Mada No. 8 Jakarta Pusat 10120. Telp. (+62-21) 6385 8269-70, 630 2164, 630 2485 Fax. (+62-21) 6385 8275 Po Box. 4005 Jkt 10040',
            ['size' => 9, 'name' => 'Arial'],
            ['alignment' => 'center']
        );
        $section->addText(
            'Homepage : www.bapeten.go.id, E-mail: info@bapeten.go.id',
            ['size' => 9, 'name' => 'Arial'],
            ['alignment' => 'center']
        );

        // ── Horizontal rule ──
        $section->addTextBreak(0);
        $section->addLine(['width' => 9000, 'height' => 0, 'positioning' => 'relative', 'lineStyle' => ['color' => '000000', 'line' => 1]]);
        $section->addTextBreak(1);

        // ── Title ──
        $section->addText(
            'BERITA ACARA',
            ['bold' => true, 'size' => 13, 'name' => 'Arial'],
            ['alignment' => 'center']
        );
        $section->addText(
            'PELAKSANAAN UJI KOMPETENSI',
            ['bold' => true, 'size' => 13, 'name' => 'Arial'],
            ['alignment' => 'center']
        );
        $section->addTextBreak(1);

        // ── Opening paragraph ──
        $section->addText(
            "Pada hari ini, {$data['hari']}, tanggal {$data['tanggal_angka']} {$data['bulan']} {$data['tahun']} telah dilaksanakan kegiatan Uji Kompetensi untuk pegawai di lingkungan Badan Pengawas Tenaga Nuklir dengan rincian sebagai berikut:",
            ['size' => 12, 'name' => 'Arial'],
            ['alignment' => 'both', 'spaceAfter' => 160]
        );
        $section->addTextBreak(0);

        // ── Key-value table ──
        $table = $section->addTable(['borderSize' => 0, 'cellMarginLeft' => 0]);
        $rows = [
            ['Judul Kegiatan', $data['judul_kegiatan']],
            ['Lokasi',         $data['lokasi']],
            ['Sesi',           $data['sesi']],
            ['Jumlah Peserta Hadir', $data['jumlah_peserta'] . ' orang'],
        ];

        foreach ($rows as [$label, $value]) {
            $table->addRow();
            $table->addCell(2000, ['borderSize' => 0])
                ->addText($label, ['size' => 12, 'name' => 'Arial']);
            $table->addCell(360, ['borderSize' => 0])
                ->addText(':', ['size' => 12, 'name' => 'Arial']);
            $table->addCell(6640, ['borderSize' => 0])
                ->addText($value, ['size' => 12, 'name' => 'Arial']);
        }

        $section->addTextBreak(1);

        // ── Closing ──
        $section->addText(
            'Dengan rincian peserta terlampir',
            ['size' => 12, 'name' => 'Arial']
        );
        $section->addText(
            'Demikian Berita Acara ini dibuat dengan sebenarnya.',
            ['size' => 12, 'name' => 'Arial'],
            ['spaceAfter' => 280]
        );
        $section->addTextBreak(1);

        // ── Place and date ──
        $section->addText(
            "Jakarta, {$data['tanggal_ttd']}",
            ['size' => 12, 'name' => 'Arial'],
            ['alignment' => 'center']
        );
        $section->addTextBreak(1);

        // ── Signature columns (two-column table) ──
        $sigTable = $section->addTable(['borderSize' => 0, 'width' => 9000, 'unit' => 'twip']);
        $sigTable->addRow();

        // Left: Panitia
        $leftCell = $sigTable->addCell(4500, ['borderSize' => 0]);
        $leftCell->addText('Panitia Pelaksana Uji Kompetensi', ['size' => 12, 'name' => 'Arial'], ['alignment' => 'center']);
        $leftCell->addTextBreak(3);
        $leftCell->addText($data['nama_panitia'], ['size' => 12, 'bold' => true, 'name' => 'Arial'], ['alignment' => 'center']);
        $leftCell->addText('NIP. ' . $data['nip_panitia'], ['size' => 12, 'name' => 'Arial'], ['alignment' => 'center']);

        // Right: Kepala BOU
        $rightCell = $sigTable->addCell(4500, ['borderSize' => 0]);
        $rightCell->addText('Kepala Biro Organisasi dan Umum', ['size' => 12, 'name' => 'Arial'], ['alignment' => 'center']);
        $rightCell->addTextBreak(3);
        $rightCell->addText($data['nama_kepala_bou'], ['size' => 12, 'bold' => true, 'name' => 'Arial'], ['alignment' => 'center']);
        $rightCell->addText('NIP. ' . $data['nip_kepala_bou'], ['size' => 12, 'name' => 'Arial'], ['alignment' => 'center']);

        // ── Stream the file ──
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $tmpPath = sys_get_temp_dir() . '/' . Str::random(16) . '.docx';
        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename . '.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
