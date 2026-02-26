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

    /**
     * Apply filter data to the underlying Eloquent query.
     *
     * Called automatically by pxlrbt/filament-excel before fetching rows.
     */
    public function setUp(): void
    {
        $this->modifyQueryUsing(function ($query) {
            // Always eager-load relationships needed by columns
            $query->with(['user', 'examPackage', 'examParticipant', 'answers', 'activityLogs']);

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
     * Number of data columns.
     * With statistics: A–N = 14 (added Pelanggaran).
     * Without statistics: A–K = 11.
     */
    private function getColumnCount(): int
    {
        return $this->includeStatistics ? 14 : 11;
    }

    /**
     * Column letter index of NIP (2nd column = B).
     */
    private const NIP_COLUMN = 'B';

    public function registerEvents(): array
    {
        $includeStats = $this->includeStatistics;
        $filterData = $this->filterData;

        // Merge any parent events (e.g. RTL BeforeSheet)
        return array_merge(parent::registerEvents(), [
            AfterSheet::class => function (AfterSheet $event) use ($includeStats): void {
                $sheet      = $event->sheet->getDelegate();
                $colCount   = $this->getColumnCount();
                $lastCol    = Coordinate::stringFromColumnIndex($colCount);
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
                    // Column indices shift based on include_statistics toggle
                    // With stats: H=Benar,I=Salah,J=TdkDijawab, K=Pelanggaran, L=Nilai, M=KKM, N=Keterangan
                    // Without stats: H=Pelanggaran, I=Nilai, J=KKM, K=Keterangan
                    $pelanggaranCol = $includeStats ? 'K' : 'H';
                    $nilaiCol       = $includeStats ? 'L' : 'I';
                    $kkmCol         = $includeStats ? 'M' : 'J';
                    $keteranganCol  = $includeStats ? 'N' : 'K';

                    // Statistik columns (only when included): center align
                    if ($includeStats) {
                        foreach (['H', 'I', 'J'] as $statCol) {
                            $statRange = "{$statCol}2:{$statCol}{$lastRow}";
                            $sheet->getStyle($statRange)
                                ->getNumberFormat()
                                ->setFormatCode('0');
                            $sheet->getStyle($statRange)
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }

                        // Color the stat columns
                        for ($row = 2; $row <= $lastRow; $row++) {
                            // Benar (H) → green
                            $sheet->getStyle("H{$row}")->applyFromArray([
                                'font' => ['color' => ['rgb' => '1A6B3C'], 'bold' => true],
                            ]);
                            // Salah (I) → red
                            $sheet->getStyle("I{$row}")->applyFromArray([
                                'font' => ['color' => ['rgb' => 'C0392B'], 'bold' => true],
                            ]);
                            // Tidak Dijawab (J) → orange
                            $sheet->getStyle("J{$row}")->applyFromArray([
                                'font' => ['color' => ['rgb' => 'E67E22'], 'bold' => true],
                            ]);
                        }
                    }

                    // Pelanggaran column: center + conditional red
                    $pelanggaranRange = "{$pelanggaranCol}2:{$pelanggaranCol}{$lastRow}";
                    $sheet->getStyle($pelanggaranRange)
                        ->getNumberFormat()
                        ->setFormatCode('0');
                    $sheet->getStyle($pelanggaranRange)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $val = (int) $sheet->getCell("{$pelanggaranCol}{$row}")->getValue();
                        if ($val > 0) {
                            $sheet->getStyle("{$pelanggaranCol}{$row}")->applyFromArray([
                                'font' => ['color' => ['rgb' => 'C0392B'], 'bold' => true],
                            ]);
                        }
                    }

                    // Nilai Akhir: show up to 2 decimal places
                    $nilaiRange = "{$nilaiCol}2:{$nilaiCol}{$lastRow}";
                    $sheet->getStyle($nilaiRange)
                        ->getNumberFormat()
                        ->setFormatCode('0.00');
                    $sheet->getStyle($nilaiRange)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // KKM: whole number
                    $kkmRange = "{$kkmCol}2:{$kkmCol}{$lastRow}";
                    $sheet->getStyle($kkmRange)
                        ->getNumberFormat()
                        ->setFormatCode('0');
                    $sheet->getStyle($kkmRange)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Keterangan: bold + conditional colour
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
