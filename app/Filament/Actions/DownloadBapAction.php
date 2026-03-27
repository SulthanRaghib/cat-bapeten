<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\ExamPackage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class DownloadBapAction
{
    public static function make(): Action
    {
        return Action::make('downloadBap')
            ->label(__('Download BAP'))
            ->icon('heroicon-o-document-arrow-down')
            ->color('primary')
            ->modalHeading(__('Create Event Report (BAP)'))
            ->modalDescription(__('Fill in the data below to generate the Competency Test Event Report document.'))
            ->modalIcon('heroicon-o-document-text')
            ->modalWidth('2xl')
            ->modalSubmitActionLabel(__('Download Document'))
            ->modalCancelActionLabel(__('Cancel'))
            ->fillForm(function (): array {
                return [
                    'tanggal' => now()->toDateString(),
                    'lokasi'  => '',
                    'sesi'    => '',
                    'format'  => 'pdf',
                ];
            })
            ->schema([

                // ── Bagian 1: Informasi Kegiatan ──
                Section::make(__('Event Information'))
                    ->icon('heroicon-o-calendar')
                    ->columns(2)
                    ->schema([

                        DatePicker::make('tanggal')
                            ->label(__('Event Date'))
                            ->required()
                            ->displayFormat('d M Y')
                            ->native(false)
                            ->columnSpan(1),

                        Select::make('exam_package_id')
                            ->label(__('Exam Package'))
                            ->options(fn() => ExamPackage::where('is_active', true)
                                ->pluck('title', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set) {
                                if (! $state) {
                                    $set('judul_kegiatan', null);
                                    $set('jumlah_peserta', null);

                                    return;
                                }
                                $pkg = ExamPackage::find($state);
                                if ($pkg) {
                                    $set('judul_kegiatan', __('Competency Test') . ' ' . $pkg->title);
                                    $set('jumlah_peserta', $pkg->participants()->count());
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('judul_kegiatan')
                            ->label(__('Event Title'))
                            ->placeholder(__('Competency Test for Functional Position ...'))
                            ->required()
                            ->helperText(__('Auto-filled from Exam Package, can be manually edited.'))
                            ->columnSpanFull(),

                        TextInput::make('lokasi')
                            ->label(__('Exam Location'))
                            ->placeholder(__('Exam Room Lt. 3, BAPETEN Head Office'))
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('sesi')
                            ->label(__('Exam Session'))
                            ->placeholder(__('Session I / Session II / 09.00—11.00 WIB'))
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('jumlah_peserta')
                            ->label(__('Number of Participants Present'))
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText(__('Auto-filled from registered participants, can be edited.'))
                            ->suffix(__('person'))
                            ->columnSpan(1),
                    ]),

                // ── Bagian 2: Penandatangan ──
                Section::make(__('Signatory Data'))
                    ->icon('heroicon-o-pencil-square')
                    ->columns(2)
                    ->schema([

                        TextInput::make('nama_panitia')
                            ->label(__('Committee Name'))
                            ->placeholder(__('Full name with title'))
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('nip_panitia')
                            ->label(__('Committee NIP'))
                            ->placeholder('19XXXXXXXXXXXXXXXXX')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('nama_kepala_bou')
                            ->label(__('BOU Head Name'))
                            ->placeholder(__('Full name with title'))
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('nip_kepala_bou')
                            ->label(__('BOU Head NIP'))
                            ->placeholder('19XXXXXXXXXXXXXXXXX')
                            ->required()
                            ->columnSpan(1),
                    ]),

                // ── Bagian 3: Format Unduhan ──
                Section::make(__('Download Format'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->schema([
                        Radio::make('format')
                            ->label(__('Select Format'))
                            ->options([
                                'pdf'  => __('PDF — ready to print, locked layout'),
                                'word' => __('Word (DOCX) — can be edited further'),
                            ])
                            ->default('pdf')
                            ->required()
                            ->inline(false),
                    ]),
            ])
            ->action(function (array $data) {
                return self::generate($data);
            });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Core generator
    // ─────────────────────────────────────────────────────────────────────────
    private static function generate(array $data): mixed
    {
        $package = ExamPackage::find($data['exam_package_id'] ?? null);
        $tanggal = Carbon::parse($data['tanggal']);

        $hariMap = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $bulanMap = [
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

        $viewData = [
            'hari'            => $hariMap[$tanggal->format('l')] ?? $tanggal->format('l'),
            'tanggal_angka'   => $tanggal->day,
            'bulan'           => $bulanMap[$tanggal->month],
            'tahun'           => $tanggal->year,
            'tanggal_ttd'     => $tanggal->day . ' ' . $bulanMap[$tanggal->month] . ' ' . $tanggal->year,
            'judul_kegiatan'  => $data['judul_kegiatan'] ?? ($package?->title ?? '-'),
            'lokasi'          => $data['lokasi'] ?? '-',
            'sesi'            => $data['sesi'] ?? '-',
            'jumlah_peserta'  => $data['jumlah_peserta'] ?? 0,
            'nama_panitia'    => $data['nama_panitia'] ?? '-',
            'nip_panitia'     => $data['nip_panitia'] ?? '-',
            'nama_kepala_bou' => $data['nama_kepala_bou'] ?? '-',
            'nip_kepala_bou'  => $data['nip_kepala_bou'] ?? '-',
            'garuda_path'     => public_path('assets/img/garuda.png'),
        ];

        $filename = 'BAP_Uji_Kompetensi_BAPETEN_' . $tanggal->format('Ymd_His');

        if (($data['format'] ?? 'pdf') === 'word') {
            return self::generateWord($viewData, $filename);
        }

        return self::generatePdf($viewData, $filename);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PDF via DomPDF
    // ─────────────────────────────────────────────────────────────────────────
    private static function generatePdf(array $data, string $filename): mixed
    {
        // Embed image as base64 — DomPDF cannot reliably read Windows absolute paths
        $garuda_b64 = '';
        if (file_exists($data['garuda_path'])) {
            $garuda_b64 = 'data:image/png;base64,' . base64_encode(file_get_contents($data['garuda_path']));
        }
        $data['garuda_b64'] = $garuda_b64;

        $pdf = Pdf::loadView('exports.bap-pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'sans-serif',
                'dpi'                  => 150,
            ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Word via PHPWord
    // ─────────────────────────────────────────────────────────────────────────
    private static function generateWord(array $data, string $filename): mixed
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop'    => 1134, // ~2 cm
            'marginBottom' => 1134,
            'marginLeft'   => 1800, // ~3.2 cm
            'marginRight'  => 1440, // ~2.5 cm
        ]);

        // ── Shared styles ──
        $f     = ['size' => 11, 'name' => 'Arial'];
        $fBold = ['size' => 11, 'name' => 'Arial', 'bold' => true];
        $fSm   = ['size' => 8,  'name' => 'Arial'];

        // Paragraph: spacing 0/0, lineHeight 1.0  (for kop & divider area)
        $p0   = ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1.0];
        // Paragraph: spacing 0/0, lineHeight 1.5  (for body text)
        $p15L = ['alignment' => 'left',   'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1.5];
        $p15C = ['alignment' => 'center', 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1.5];
        $p15J = ['alignment' => 'both',   'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1.5];
        $p0C  = ['alignment' => 'center', 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1.0];

        // Helper: cell with all borders off
        $noBorder = [
            'borderTopSize'    => 0,
            'borderTopColor'    => 'FFFFFF',
            'borderBottomSize' => 0,
            'borderBottomColor' => 'FFFFFF',
            'borderLeftSize'   => 0,
            'borderLeftColor'   => 'FFFFFF',
            'borderRightSize'  => 0,
            'borderRightColor'  => 'FFFFFF',
        ];

        // ── Kop surat: garuda centered at top, institution text below ──
        if (file_exists($data['garuda_path'])) {
            $section->addImage($data['garuda_path'], [
                'width'       => 65,
                'height'      => 75,
                'alignment'   => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'borderSize'  => 0,
                'borderColor' => 'FFFFFF',
                'spaceAfter'  => 0,
            ]);
        }
        $section->addText('BADAN PENGAWAS TENAGA NUKLIR', $fBold, $p0C);
        $section->addText('BIRO ORGANISASI DAN UMUM', $fBold, $p0C);
        $section->addText(
            'Alamat : Jl. Gajah Mada No. 8 Jakarta Pusat 10120. Telp. (+62-21) 6385 8269-70, 630 2164, 630 2485 Fax. (+62-21) 6385 8275 Po Box. 4005 Jkt 10040',
            $fSm,
            $p0C
        );
        $section->addText(
            'Homepage : www.bapeten.go.id, E-mail: info@bapeten.go.id',
            $fSm,
            $p0C
        );

        // ── Divider: paragraph with bottom border 1.5 pt, spacing 0/0, line 1.0 ──
        $section->addText('', null, array_merge($p0, [
            'borderBottomSize'  => 12,       // 12 eighths-of-a-point = 1.5 pt
            'borderBottomColor' => '000000',
        ]));

        // ── Empty gap ──
        $section->addText('', null, $p0);

        // ── Title ──
        $section->addText('BERITA ACARA', $fBold, $p0C);
        $section->addText('PELAKSANAAN UJI KOMPETENSI', $fBold, $p0C);

        // ── Gap before opening paragraph ──
        $section->addText('', null, $p0);
        $section->addText('', null, $p0);

        // ── Opening paragraph with bold hari & tanggal ──
        $textRun = $section->addTextRun($p15J);
        $textRun->addText("Pada hari ini, ", $f);
        $textRun->addText($data['hari'], $fBold);
        $textRun->addText(", tanggal ", $f);
        $textRun->addText("{$data['tanggal_angka']} {$data['bulan']} {$data['tahun']}", $fBold);
        $textRun->addText(
            " telah dilaksanakan kegiatan Uji Kompetensi untuk pegawai di lingkungan " .
                "Badan Pengawas Tenaga Nuklir dengan rincian sebagai berikut:",
            $f
        );

        // ── Key-value table: no border, centered, text left ──
        $table = $section->addTable([
            'borderSize'  => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin'  => 0,
            'alignment'   => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        ]);
        foreach (
            [
                ['Judul Kegiatan',       $data['judul_kegiatan']],
                ['Lokasi',               $data['lokasi']],
                ['Sesi',                 $data['sesi']],
                ['Jumlah Peserta Hadir', $data['jumlah_peserta'] . ' orang'],
            ] as [$label, $value]
        ) {
            $table->addRow();
            $table->addCell(2200, $noBorder)->addText($label, $f, $p15L);
            $table->addCell(300,  $noBorder)->addText(':',    $f, $p15L);
            $table->addCell(5500, $noBorder)->addText($value, $f, $p15L);
        }

        // ── Closing text ──
        $section->addText('', null, $p0);
        $section->addText('Dengan rincian peserta terlampir', $f, $p15L);
        $section->addText('Demikian Berita Acara ini dibuat dengan sebenarnya.', $f, $p15L);

        // ── Place/date ──
        $section->addText('', null, $p0);
        $section->addText('', null, $p0);
        $section->addText("Jakarta, {$data['tanggal_ttd']}", $f, $p15C);

        // ── Signature table: no border ──
        $section->addText('', null, $p0);
        $sigTable = $section->addTable([
            'borderSize'  => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin'  => 0,
        ]);
        $sigTable->addRow();

        $left = $sigTable->addCell(4500, $noBorder);
        $left->addText('Panitia Pelaksana Uji Kompetensi', $f, $p15C);
        for ($i = 0; $i < 4; $i++) {
            $left->addText('', null, $p15C);
        }
        $left->addText($data['nama_panitia'], $fBold, $p15C);
        $left->addText('NIP. ' . $data['nip_panitia'], $f, $p15C);

        $right = $sigTable->addCell(4500, $noBorder);
        $right->addText('Kepala Biro Organisasi dan Umum', $f, $p15C);
        for ($i = 0; $i < 4; $i++) {
            $right->addText('', null, $p15C);
        }
        $right->addText($data['nama_kepala_bou'], $fBold, $p15C);
        $right->addText('NIP. ' . $data['nip_kepala_bou'], $f, $p15C);

        // ── Stream ──
        $writer  = IOFactory::createWriter($phpWord, 'Word2007');
        $tmpPath = sys_get_temp_dir() . '/' . Str::random(16) . '.docx';
        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename . '.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
