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
    /**
     * Number of data columns (A – J = 10).
     * Update this constant if columns are added / removed.
     */
    private const COLUMN_COUNT = 10;

    /**
     * Column letter index of NIP (2nd column = B).
     */
    private const NIP_COLUMN = 'B';

    public function registerEvents(): array
    {
        // Merge any parent events (e.g. RTL BeforeSheet)
        return array_merge(parent::registerEvents(), [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet      = $event->sheet->getDelegate();
                $lastCol    = Coordinate::stringFromColumnIndex(self::COLUMN_COUNT);  // 'J'
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
                        ],
                    ]);
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(18);
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

                    // ── 7. Numeric columns: integer / decimal formats ──────────
                    // Nilai Akhir (column H = 8): show up to 2 decimal places
                    $nilaiRange = "H2:H{$lastRow}";
                    $sheet->getStyle($nilaiRange)
                        ->getNumberFormat()
                        ->setFormatCode('0.00');
                    $sheet->getStyle($nilaiRange)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // KKM (column I = 9): whole number
                    $kkmRange = "I2:I{$lastRow}";
                    $sheet->getStyle($kkmRange)
                        ->getNumberFormat()
                        ->setFormatCode('0');
                    $sheet->getStyle($kkmRange)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Keterangan (column J = 10): bold + conditional colour
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $cellVal = $sheet->getCell("J{$row}")->getValue();
                        $color   = $cellVal === 'LULUS' ? '1A6B3C' : 'C0392B';
                        $sheet->getStyle("J{$row}")->applyFromArray([
                            'font'      => ['bold' => true, 'color' => ['rgb' => $color]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                }
            },
        ]);
    }
}
