<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

/**
 * Custom Excel export for Laporan Hasil Ujian BAPETEN.
 *
 * Extends the base ExcelExport to add:
 * - Freeze pane at B2 (row-1 heading + column-A "Nama Lengkap" always visible)
 * - AutoFilter dropdown on every heading column
 * - Styled header row (bold / white text / blue fill)
 * - Alternating row (zebra) striping
 * - Text format (@) on NIP column so it never appears as scientific notation
 */
class ExamResultsExcelExport extends ExcelExport
{
    protected array $filterData = [];
    protected bool $includeStatistics = true;
    protected bool $isMansoskul = false;
    protected int  $unitCount   = 0;

    public function setFilterData(array $data): static
    {
        $this->filterData = $data;
        return $this;
    }

    public function setIncludeStatistics(bool $value): static
    {
        $this->includeStatistics = $value;
        return $this;
    }

    public function setIsMansoskul(bool $value): static
    {
        $this->isMansoskul = $value;
        return $this;
    }

    public function setUnitCount(int $count): static
    {
        $this->unitCount = $count;
        return $this;
    }

    /**
     * Apply filter data to the underlying Eloquent query.
     *
     * Called automatically by pxlrbt/filament-excel before fetching rows.
     */
    public function setUp(): void
    {
        $this->modifyQueryUsing(function ($query) {
            // Always eager-load relationships needed by columns
            $query->with(['user', 'examPackage.examType', 'examParticipant', 'answers', 'activityLogs']);

            // Only completed sessions
            $query->where('status', 'completed');

            // Filter: Paket Ujian
            if ($packageId = $this->filterData['filter_exam_package_id'] ?? null) {
                $query->whereHas('examParticipant', function ($sub) use ($packageId) {
                    $sub->where('exam_package_id', $packageId);
                });
            }

            // Filter: Dari Tanggal
            if ($date = $this->filterData['filter_dari_tanggal'] ?? null) {
                $query->whereDate('started_at', '>=', $date);
            }

            // Filter: Sampai Tanggal
            if ($date = $this->filterData['filter_sampai_tanggal'] ?? null) {
                $query->whereDate('started_at', '<=', $date);
            }

            // Filter: Status Kelulusan
            if ($status = $this->filterData['filter_status_kelulusan'] ?? null) {
                $operator = $status === 'lulus' ? '>=' : '<';
                $query->whereRaw("total_score {$operator} (
                    SELECT ep.passing_grade
                    FROM exam_packages ep
                    JOIN exam_participants part ON part.exam_package_id = ep.id
                    WHERE part.id = exam_sessions.exam_participant_id
                    LIMIT 1
                )");
            }

            $query->orderBy('finished_at', 'desc');

            return $query;
        });
    }


    /**
     * Column letter index of NIP (2nd column = B).
     */
    private const NIP_COLUMN = 'B';

    public function registerEvents(): array
    {
        $includeStats  = $this->includeStatistics;
        $isMansoskul   = $this->isMansoskul;
        $unitCount     = $this->unitCount;

        // Merge any parent events (e.g. RTL BeforeSheet)
        return array_merge(parent::registerEvents(), [
            AfterSheet::class => function (AfterSheet $event) use ($includeStats, $isMansoskul, $unitCount): void {
                $sheet      = $event->sheet->getDelegate();
                $lastCol    = $sheet->getHighestColumn();
                $colCount   = Coordinate::columnIndexFromString($lastCol);
                $lastRow    = $sheet->getHighestRow();
                $headerRange = "A1:{$lastCol}1";
                $dataRange   = "A1:{$lastCol}{$lastRow}";

                // ── 1. Freeze pane ─────────────────────────────────────────────
                // B2 = freeze rows above row 2 (heading) AND columns left of B (Nama).
                $sheet->freezePane('B2');

                // ── 2. AutoFilter on all heading columns ───────────────────────
                $sheet->setAutoFilter($headerRange);

                // ── 3. Header row style ────────────────────────────────────────
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'size'  => 11,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E3A5F'],   // navy blue
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '4B8BBE'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(32);

                // ── 4. Outer border on entire table ───────────────────────────
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color'       => ['rgb' => '1E3A5F'],
                        ],
                    ],
                ]);

                // ── 5. Data rows: plain white background + thin border ─────────
                if ($lastRow >= 2) {
                    $dataRowRange = "A2:{$lastCol}{$lastRow}";
                    $sheet->getStyle($dataRowRange)->applyFromArray([
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFFFFF'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_HAIR,
                                'color'       => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                    for ($row = 2; $row <= $lastRow; $row++) {
                        // Auto-set row height based on line count in any cell
                        $maxLines = 1;
                        for ($c = 1; $c <= $colCount; $c++) {
                            $letter  = Coordinate::stringFromColumnIndex($c);
                            $val     = (string) $sheet->getCell("{$letter}{$row}")->getValue();
                            $lines   = max(1, substr_count($val, "\n") + 1);
                            if ($lines > $maxLines) {
                                $maxLines = $lines;
                            }
                        }
                        $sheet->getRowDimension($row)->setRowHeight($maxLines > 1 ? $maxLines * 15 : 18);
                    }

                    // ── 6. NIP column: force explicit STRING type per cell ────────
                    // setCellValueExplicit overrides PhpSpreadsheet's auto numeric
                    // detection — the only reliable way to prevent scientific notation.
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $cell    = self::NIP_COLUMN . $row;
                        $current = $sheet->getCell($cell)->getValue();
                        if ($current !== null && $current !== '-' && $current !== '') {
                            $sheet->setCellValueExplicit(
                                $cell,
                                (string) $current,
                                DataType::TYPE_STRING
                            );
                        }
                    }
                    $nipRange = self::NIP_COLUMN . "2:" . self::NIP_COLUMN . $lastRow;
                    $sheet->getStyle($nipRange)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // ── 8. Column widths ────────────────────────────────────────────
                    // Width map by partial match on heading text
                    $widthMap = [
                        'Nama Lengkap'                    => 28,
                        'NIP'                             => 20,
                        'Nama Ujian'                      => 30,
                        'Tipe Ujian'                      => 16,
                        'Tanggal'                         => 14,
                        'Waktu Mulai'                     => 13,
                        'Waktu Selesai'                   => 13,
                        'Durasi'                          => 14,
                        'Benar'                           => 8,
                        'Salah'                           => 8,
                        'Tidak Dijawab'                   => 13,
                        'Unit Kompeten'                   => 13,
                        'Pelanggaran'                     => 13,
                        'Skor'                            => 12,
                        'Indikator'                       => 28,
                        'Skor CBT'                        => 12,
                        'Nilai '                          => 18,
                        'Nilai Akhir'                     => 16,
                        'Nilai Ambang Batas'              => 18,
                        'NAB'                             => 10,
                        'Keterangan'                      => 14,
                        'Rincian Tahap Seleksi'           => 30,
                        'Rincian Unit Penilaian'          => 55,
                    ];

                    for ($c = 1; $c <= $colCount; $c++) {
                        $letter  = Coordinate::stringFromColumnIndex($c);
                        $heading = (string) $sheet->getCell("{$letter}1")->getValue();
                        $width   = null;
                        foreach ($widthMap as $pattern => $w) {
                            if (str_contains($heading, $pattern)) {
                                $width = $w;
                                break;
                            }
                        }
                        if ($width !== null) {
                            $sheet->getColumnDimension($letter)->setWidth($width);
                        } else {
                            $sheet->getColumnDimension($letter)->setAutoSize(true);
                        }
                    }
                    if ($isMansoskul) {
                        // ── MANSOSKUL layout ──────────────────────────────────────
                        // A-G: base cols (1-7)
                        // H, J, L, ...: Skor unit[0], unit[1], ...  (col 8+2i)
                        // I, K, M, ...: Indikator unit[0], unit[1], ...  (col 9+2i)
                        // After units: UnitKompeten, Pelanggaran, Nilai, NAB, Keterangan
                        $afterUnitsBase = 8 + 2 * $unitCount;   // 1-based col index of "Unit Kompeten"
                        $unitKompetenCol = Coordinate::stringFromColumnIndex($afterUnitsBase);
                        $pelanggaranCol  = Coordinate::stringFromColumnIndex($afterUnitsBase + 1);
                        $nilaiCol        = Coordinate::stringFromColumnIndex($afterUnitsBase + 2);
                        $nabCol          = Coordinate::stringFromColumnIndex($afterUnitsBase + 3);
                        $keteranganCol   = Coordinate::stringFromColumnIndex($afterUnitsBase + 4);

                        for ($u = 0; $u < $unitCount; $u++) {
                            $skorCol      = Coordinate::stringFromColumnIndex(8 + 2 * $u);
                            $indikatorCol = Coordinate::stringFromColumnIndex(9 + 2 * $u);

                            // Skor col: decimal format + center + bold dark blue
                            $sheet->getStyle("{$skorCol}2:{$skorCol}{$lastRow}")
                                ->getNumberFormat()->setFormatCode('0.00');
                            $sheet->getStyle("{$skorCol}2:{$skorCol}{$lastRow}")
                                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            for ($row = 2; $row <= $lastRow; $row++) {
                                $sheet->getStyle("{$skorCol}{$row}")->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => 'E67E22']],
                                ]);
                            }

                            // Indikator col: green if KOMPETEN, red if BELUM KOMPETEN
                            $sheet->getStyle("{$indikatorCol}2:{$indikatorCol}{$lastRow}")
                                ->getAlignment()->setWrapText(true);
                            for ($row = 2; $row <= $lastRow; $row++) {
                                $val   = (string) $sheet->getCell("{$indikatorCol}{$row}")->getValue();
                                // Contains "[KOMPETEN]" but NOT "[BELUM KOMPETEN]"
                                $lulus = str_contains($val, '[KOMPETEN]') && !str_contains($val, '[BELUM KOMPETEN]');
                                $sheet->getStyle("{$indikatorCol}{$row}")->applyFromArray([
                                    'font' => [
                                        'bold'  => true,
                                        'color' => ['rgb' => $lulus ? '1A6B3C' : 'C0392B'],
                                    ],
                                ]);
                            }
                        }

                        // Unit Kompeten: center + green if all pass
                        $sheet->getStyle("{$unitKompetenCol}2:{$unitKompetenCol}{$lastRow}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        for ($row = 2; $row <= $lastRow; $row++) {
                            $val   = (string) $sheet->getCell("{$unitKompetenCol}{$row}")->getValue();
                            // Format is "X/Y" — all pass when X === Y
                            [$passed, $total] = array_pad(explode('/', $val), 2, '0');
                            $allPass = (trim($passed) === trim($total)) && trim($total) !== '0';
                            $sheet->getStyle("{$unitKompetenCol}{$row}")->applyFromArray([
                                'font' => [
                                    'bold'  => true,
                                    'color' => ['rgb' => $allPass ? '1A6B3C' : 'C0392B'],
                                ],
                            ]);
                        }
                    } else {
                        // ── TEKNIS layout ────────────────────────────────────────
                        // Column positions are dynamic because multi-stage packages
                        // insert CBT + per-stage columns between Pelanggaran and Nilai Akhir.
                        // Scan the header row to locate each column by heading text.

                        $headerMap = [];
                        for ($c = 1; $c <= $colCount; $c++) {
                            $letter = Coordinate::stringFromColumnIndex($c);
                            $headerMap[(string) $sheet->getCell("{$letter}1")->getValue()] = $letter;
                        }
                        // Partial-match helper (matches heading text that STARTS WITH $search)
                        $findCol = function (string $search) use ($headerMap): ?string {
                            if (isset($headerMap[$search])) {
                                return $headerMap[$search];
                            }
                            foreach ($headerMap as $heading => $letter) {
                                if (str_starts_with($heading, $search)) {
                                    return $letter;
                                }
                            }
                            return null;
                        };

                        $pelanggaranCol = $findCol('Pelanggaran');
                        $nilaiCol       = $findCol('Nilai Akhir');
                        $nabCol         = $findCol('Nilai Ambang Batas');
                        $keteranganCol  = $findCol('Keterangan');

                        // Statistik columns: Benar / Salah / Tidak Dijawab
                        if ($includeStats) {
                            $benarCol   = $findCol('Benar');
                            $salahCol   = $findCol('Salah');
                            $tdkCol     = $findCol('Tidak Dijawab');
                            foreach (array_filter([$benarCol, $salahCol, $tdkCol]) as $statCol) {
                                $sheet->getStyle("{$statCol}2:{$statCol}{$lastRow}")
                                    ->getNumberFormat()->setFormatCode('0');
                                $sheet->getStyle("{$statCol}2:{$statCol}{$lastRow}")
                                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            }
                            if ($benarCol) {
                                for ($row = 2; $row <= $lastRow; $row++) {
                                    $sheet->getStyle("{$benarCol}{$row}")->applyFromArray(['font' => ['color' => ['rgb' => '1A6B3C'], 'bold' => true]]);
                                }
                            }
                            if ($salahCol) {
                                for ($row = 2; $row <= $lastRow; $row++) {
                                    $sheet->getStyle("{$salahCol}{$row}")->applyFromArray(['font' => ['color' => ['rgb' => 'C0392B'], 'bold' => true]]);
                                }
                            }
                            if ($tdkCol) {
                                for ($row = 2; $row <= $lastRow; $row++) {
                                    $sheet->getStyle("{$tdkCol}{$row}")->applyFromArray(['font' => ['color' => ['rgb' => 'E67E22'], 'bold' => true]]);
                                }
                            }
                        }

                        // Score columns between Pelanggaran and Nilai Akhir
                        // (Skor CBT, per-stage scores) — decimal format + center
                        if ($pelanggaranCol && $nilaiCol) {
                            $pelIdx = Coordinate::columnIndexFromString($pelanggaranCol);
                            $nilIdx = Coordinate::columnIndexFromString($nilaiCol);
                            for ($c = $pelIdx + 1; $c < $nilIdx; $c++) {
                                $letter = Coordinate::stringFromColumnIndex($c);
                                $sheet->getStyle("{$letter}2:{$letter}{$lastRow}")
                                    ->getNumberFormat()->setFormatCode('0.00');
                                $sheet->getStyle("{$letter}2:{$letter}{$lastRow}")
                                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                                for ($row = 2; $row <= $lastRow; $row++) {
                                    $sheet->getStyle("{$letter}{$row}")->applyFromArray([
                                        'font' => ['bold' => true, 'color' => ['rgb' => '1d4ed8']],
                                    ]);
                                }
                            }
                        }
                    }

                    // ── Shared trailing columns (Pelanggaran, Nilai, NAB, Keterangan) ──
                    // Pelanggaran: center + red if > 0
                    $pelanggaranRange = "{$pelanggaranCol}2:{$pelanggaranCol}{$lastRow}";
                    $sheet->getStyle($pelanggaranRange)
                        ->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle($pelanggaranRange)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $val = (int) $sheet->getCell("{$pelanggaranCol}{$row}")->getValue();
                        if ($val > 0) {
                            $sheet->getStyle("{$pelanggaranCol}{$row}")->applyFromArray([
                                'font' => ['color' => ['rgb' => 'C0392B'], 'bold' => true],
                            ]);
                        }
                    }

                    // Nilai: 2 decimal places + center
                    $nilaiRange = "{$nilaiCol}2:{$nilaiCol}{$lastRow}";
                    $sheet->getStyle($nilaiRange)
                        ->getNumberFormat()->setFormatCode('0.00');
                    $sheet->getStyle($nilaiRange)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // Color nilai red/green based on pass/fail (compare with NAB)
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $nilai = (float) $sheet->getCell("{$nilaiCol}{$row}")->getValue();
                        $nab   = (float) $sheet->getCell("{$nabCol}{$row}")->getValue();
                        $pass  = $nilai >= $nab && $nab > 0;
                        $sheet->getStyle("{$nilaiCol}{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $pass ? '1A6B3C' : 'C0392B']],
                        ]);
                    }

                    // NAB: whole number + center
                    $nabRange = "{$nabCol}2:{$nabCol}{$lastRow}";
                    $sheet->getStyle($nabRange)
                        ->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle($nabRange)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Keterangan: bold + green/red
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $cellVal = $sheet->getCell("{$keteranganCol}{$row}")->getValue();
                        $color   = $cellVal === 'LULUS' ? '1A6B3C' : 'C0392B';
                        $sheet->getStyle("{$keteranganCol}{$row}")->applyFromArray([
                            'font'      => ['bold' => true, 'color' => ['rgb' => $color]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                }
            },
        ]);
    }
}
