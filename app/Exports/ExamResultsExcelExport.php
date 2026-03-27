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
    protected array $summaryData = [];  // For header summary
    protected array $columnGroups = []; // For merged group headers (Rincian Tahap Seleksi, Rincian Unit Penilaian)
    protected bool $hasGroupHeaders = false;

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

    public function setSummaryData(array $data): static
    {
        $this->summaryData = $data;
        return $this;
    }

    /**
     * Set column groups for merged headers.
     * Format: [
     *   ['label' => 'Rincian Tahap Seleksi', 'start_col' => 12, 'end_col' => 15],
     *   ['label' => 'Rincian Unit Penilaian', 'start_col' => 16, 'end_col' => 19],
     * ]
     */
    public function setColumnGroups(array $groups): static
    {
        $this->columnGroups = $groups;
        $this->hasGroupHeaders = !empty($groups);
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

            // Sort by exam type first (Teknis before Mansoskul), then by finish date
            $query->orderByRaw("(
                SELECT et.id 
                FROM exam_types et 
                JOIN exam_packages ep ON ep.exam_type_id = et.id 
                JOIN exam_participants part ON part.exam_package_id = ep.id
                WHERE part.id = exam_sessions.exam_participant_id 
                LIMIT 1
            ) ASC")
            ->orderBy('finished_at', 'desc');

            return $query;
        });
    }


    /**
     * Column letter index of NIP (2nd column = B).
     */
    private const NIP_COLUMN = 'B';

    public function registerEvents(): array
    {
        $includeStats    = $this->includeStatistics;
        $isMansoskul     = $this->isMansoskul;
        $unitCount       = $this->unitCount;
        $summaryData     = $this->summaryData;
        $columnGroups    = $this->columnGroups;
        $hasGroupHeaders = $this->hasGroupHeaders;

        // Merge any parent events (e.g. RTL BeforeSheet)
        return array_merge(parent::registerEvents(), [
            AfterSheet::class => function (AfterSheet $event) use ($includeStats, $isMansoskul, $unitCount, $summaryData, $columnGroups, $hasGroupHeaders): void {
                $sheet      = $event->sheet->getDelegate();
                $lastCol    = $sheet->getHighestColumn();
                $colCount   = Coordinate::columnIndexFromString($lastCol);
                $lastRow    = $sheet->getHighestRow();

                // ── Insert summary header rows (EXACTLY like PDF) ──────────────
                $summaryRowCount = 0;
                
                if (!empty($summaryData)) {
                    // Calculate how many rows needed (NO blank rows):
                    // 1. Title row
                    // 2. Export timestamp row
                    // 3. Filter info row (if any) - NO blank after
                    // 4. Summary metrics (2 rows: labels + values)
                    // 5. Group header row (if hasGroupHeaders) - for merged headers like "Rincian Tahap Seleksi"
                    // NO blank separators!
                    $filterRows = !empty($summaryData['filter_info']) ? 1 : 0;
                    $groupHeaderRows = $hasGroupHeaders ? 1 : 0;
                    $summaryRowCount = 2 + $filterRows + 2 + $groupHeaderRows; // title + timestamp + filter? + 2 summary rows + group?
                    
                    $sheet->insertNewRowBefore(1, $summaryRowCount);
                    
                    $currentRow = 1;
                    
                    // ── Row 1: Report Title (Indonesian, NO translation) ──
                    $sheet->setCellValue('A1', 'LAPORAN HASIL UJIAN');
                    $sheet->mergeCells("A1:{$lastCol}1");
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                            'color' => ['rgb' => '2C3E50'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(30);
                    $currentRow++;

                    // ── Row 2: Export timestamp ──
                    $sheet->setCellValue('A2', 'Dicetak pada: ' . $summaryData['export_timestamp']);
                    $sheet->mergeCells("A2:{$lastCol}2");
                    $sheet->getStyle('A2')->applyFromArray([
                        'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '7F8C8D']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getRowDimension(2)->setRowHeight(16);
                    $currentRow++;

                    // ── Filter info (if any) - NO blank row after ──
                    if (!empty($summaryData['filter_info'])) {
                        $filterText = 'Filter Aktif: ';
                        $filterParts = [];
                        foreach ($summaryData['filter_info'] as $key => $value) {
                            $filterParts[] = "{$key}: {$value}";
                        }
                        $filterText .= implode(' | ', $filterParts);
                        
                        $sheet->setCellValue("A{$currentRow}", $filterText);
                        $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
                        $sheet->getStyle("A{$currentRow}")->applyFromArray([
                            'font' => ['size' => 9, 'bold' => true],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8F9FA'],
                            ],
                        ]);
                        $sheet->getRowDimension($currentRow)->setRowHeight(18);
                        $currentRow++;
                    }

                    // ── Summary statistics (6 metrics in 2 rows) ──
                    $labelRow = $currentRow;
                    $valueRow = $currentRow + 1;
                    
                    $metrics = [
                        'A' => ['Total Peserta', $summaryData['total_peserta'] ?? 0, '333333'],
                        'B' => ['Jumlah Lulus', $summaryData['jumlah_lulus'] ?? 0, '1E8449'],
                        'C' => ['Jumlah Tidak Lulus', $summaryData['jumlah_gagal'] ?? 0, 'B03A2E'],
                        'D' => ['Rata-rata Nilai', $summaryData['rata_rata_nilai'] ?? 0, '2874A6'],
                        'E' => ['Nilai Tertinggi', $summaryData['nilai_tertinggi'] ?? 0, '27AE60'],
                        'F' => ['Nilai Terendah', $summaryData['nilai_terendah'] ?? 0, 'C0392B'],
                    ];
                    
                    foreach ($metrics as $col => $data) {
                        [$label, $value, $color] = $data;
                        
                        // Label row
                        $sheet->setCellValue("{$col}{$labelRow}", $label);
                        $sheet->getStyle("{$col}{$labelRow}")->applyFromArray([
                            'font' => ['size' => 8, 'bold' => true, 'color' => ['rgb' => '666666']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F4F6F7'],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'DEE2E6'],
                                ],
                            ],
                        ]);
                        
                        // Value row
                        $sheet->setCellValue("{$col}{$valueRow}", $value);
                        $sheet->getStyle("{$col}{$valueRow}")->applyFromArray([
                            'font' => ['size' => 14, 'bold' => true, 'color' => ['rgb' => $color]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'DEE2E6'],
                                ],
                            ],
                        ]);
                    }
                    
                    $sheet->getRowDimension($labelRow)->setRowHeight(20);
                    $sheet->getRowDimension($valueRow)->setRowHeight(28);
                    $currentRow += 2;

                    // ── Group header row (if hasGroupHeaders) ──
                    // This creates merged headers for column groups like "Rincian Tahap Seleksi"
                    // and merges non-group columns vertically with the column header row below
                    if ($hasGroupHeaders && !empty($columnGroups)) {
                        $groupHeaderRowNum = $currentRow;
                        $columnHeaderRowNum = $currentRow + 1; // The actual column headers will be here
                        
                        // Track which columns are part of a group
                        $groupedColIndices = [];
                        
                        // Common style for header rows
                        $headerStyle = [
                            'font' => [
                                'bold' => true,
                                'size' => 11,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '34495E'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '5D6D7E'],
                                ],
                            ],
                        ];
                        
                        foreach ($columnGroups as $group) {
                            $startColIdx = $group['start_col'];
                            $endColIdx = $group['end_col'];
                            $label = $group['label'];
                            
                            $startColLetter = Coordinate::stringFromColumnIndex($startColIdx);
                            $endColLetter = Coordinate::stringFromColumnIndex($endColIdx);
                            
                            // Mark these columns as grouped
                            for ($c = $startColIdx; $c <= $endColIdx; $c++) {
                                $groupedColIndices[$c] = true;
                            }
                            
                            // Create merged header for the group (horizontal merge on group header row)
                            $sheet->setCellValue("{$startColLetter}{$groupHeaderRowNum}", $label);
                            $sheet->mergeCells("{$startColLetter}{$groupHeaderRowNum}:{$endColLetter}{$groupHeaderRowNum}");
                            $sheet->getStyle("{$startColLetter}{$groupHeaderRowNum}:{$endColLetter}{$groupHeaderRowNum}")->applyFromArray($headerStyle);
                        }
                        
                        // For non-grouped columns, merge rows vertically (group header row + column header row)
                        // This ensures no empty cells in the group header row
                        // IMPORTANT: Copy value from column header row to group header row BEFORE merge
                        // because after merge, getValue() returns from top-left cell
                        for ($c = 1; $c <= $colCount; $c++) {
                            if (!isset($groupedColIndices[$c])) {
                                $colLetter = Coordinate::stringFromColumnIndex($c);
                                // Copy the header value from columnHeaderRowNum to groupHeaderRowNum
                                $headerValue = $sheet->getCell("{$colLetter}{$columnHeaderRowNum}")->getValue();
                                $sheet->setCellValue("{$colLetter}{$groupHeaderRowNum}", $headerValue);
                                // Merge vertically and apply same header style to both rows
                                $sheet->mergeCells("{$colLetter}{$groupHeaderRowNum}:{$colLetter}{$columnHeaderRowNum}");
                                // Apply style to the merged range
                                $sheet->getStyle("{$colLetter}{$groupHeaderRowNum}:{$colLetter}{$columnHeaderRowNum}")->applyFromArray($headerStyle);
                            }
                        }
                        
                        $sheet->getRowDimension($groupHeaderRowNum)->setRowHeight(24);
                        $sheet->getRowDimension($columnHeaderRowNum)->setRowHeight(24);
                        $currentRow++;
                    }

                    // Adjust last row count
                    $lastRow += $summaryRowCount;
                }

                // Calculate header row position
                // With group headers: column headers are at summaryRowCount + 1 (because group header is included in summaryRowCount)
                // Without group headers: column headers are at summaryRowCount + 1
                $headerRow = $summaryRowCount + 1;
                
                // For lookup purposes, when we have group headers and merged vertical cells,
                // the header values are in the group header row (headerRow - 1) because we copied them there
                // For non-group headers case, values are in headerRow
                $headerLookupRow = $hasGroupHeaders ? ($headerRow - 1) : $headerRow;
                
                $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
                $dataRange   = "A{$headerRow}:{$lastCol}{$lastRow}";

                // ── 1. Freeze pane ─────────────────────────────────────────────
                // Freeze below the header row (or group header + column header if applicable)
                $freezeRow = $headerRow + 1;
                $sheet->freezePane("B{$freezeRow}");

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
                        'startColor' => ['rgb' => '34495E'],   // professional slate gray
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '5D6D7E'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(32);

                // ── 4. Outer border on entire table ───────────────────────────
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color'       => ['rgb' => '34495E'],
                        ],
                    ],
                ]);

                // ── 5. Data rows: plain white background + thin border ─────────
                $firstDataRow = $headerRow + 1;
                if ($lastRow >= $firstDataRow) {
                    $dataRowRange = "A{$firstDataRow}:{$lastCol}{$lastRow}";
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
                    for ($row = $firstDataRow; $row <= $lastRow; $row++) {
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
                    for ($row = $firstDataRow; $row <= $lastRow; $row++) {
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
                    $nipRange = self::NIP_COLUMN . "{$firstDataRow}:" . self::NIP_COLUMN . $lastRow;
                    $sheet->getStyle($nipRange)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // ── 8. Column widths ────────────────────────────────────────────
                    // Width map by partial match on heading text
                    $widthMap = [
                        'Nama Lengkap'                    => 28,
                        'NIP'                             => 20,
                        'Paket Ujian'                     => 30,
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
                        'Status Kelulusan'                => 16,
                        'Rincian Tahap Seleksi'           => 30,
                        'Rincian Unit Penilaian'          => 55,
                    ];

                    for ($c = 1; $c <= $colCount; $c++) {
                        $letter  = Coordinate::stringFromColumnIndex($c);
                        $heading = (string) $sheet->getCell("{$letter}{$headerLookupRow}")->getValue();
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
                        // After units: UnitKompeten, Pelanggaran, Nilai, NAB, Status Kelulusan
                        $afterUnitsBase = 8 + 2 * $unitCount;   // 1-based col index of "Unit Kompeten"
                        $unitKompetenCol = Coordinate::stringFromColumnIndex($afterUnitsBase);
                        $pelanggaranCol  = Coordinate::stringFromColumnIndex($afterUnitsBase + 1);
                        $nilaiCol        = Coordinate::stringFromColumnIndex($afterUnitsBase + 2);
                        $nabCol          = Coordinate::stringFromColumnIndex($afterUnitsBase + 3);
                        $statusCol       = Coordinate::stringFromColumnIndex($afterUnitsBase + 4);

                        for ($u = 0; $u < $unitCount; $u++) {
                            $skorCol      = Coordinate::stringFromColumnIndex(8 + 2 * $u);
                            $indikatorCol = Coordinate::stringFromColumnIndex(9 + 2 * $u);

                            // Skor col: decimal format + center + bold dark blue
                            $sheet->getStyle("{$skorCol}{$firstDataRow}:{$skorCol}{$lastRow}")
                                ->getNumberFormat()->setFormatCode('0.00');
                            $sheet->getStyle("{$skorCol}{$firstDataRow}:{$skorCol}{$lastRow}")
                                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                                $sheet->getStyle("{$skorCol}{$row}")->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => 'D68910']],
                                ]);
                            }

                            // Indikator col: green if KOMPETEN, red if BELUM KOMPETEN
                            $sheet->getStyle("{$indikatorCol}{$firstDataRow}:{$indikatorCol}{$lastRow}")
                                ->getAlignment()->setWrapText(true);
                            for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                                $val   = (string) $sheet->getCell("{$indikatorCol}{$row}")->getValue();
                                // Contains "[KOMPETEN]" but NOT "[BELUM KOMPETEN]"
                                $lulus = str_contains($val, '[KOMPETEN]') && !str_contains($val, '[BELUM KOMPETEN]');
                                $sheet->getStyle("{$indikatorCol}{$row}")->applyFromArray([
                                    'font' => [
                                        'bold'  => true,
                                        'color' => ['rgb' => $lulus ? '1E8449' : 'B03A2E'],
                                    ],
                                ]);
                            }
                        }

                        // Unit Kompeten: center + green if all pass
                        $sheet->getStyle("{$unitKompetenCol}{$firstDataRow}:{$unitKompetenCol}{$lastRow}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                            $val   = (string) $sheet->getCell("{$unitKompetenCol}{$row}")->getValue();
                            // Format is "X/Y" — all pass when X === Y
                            [$passed, $total] = array_pad(explode('/', $val), 2, '0');
                            $allPass = (trim($passed) === trim($total)) && trim($total) !== '0';
                            $sheet->getStyle("{$unitKompetenCol}{$row}")->applyFromArray([
                                'font' => [
                                    'bold'  => true,
                                    'color' => ['rgb' => $allPass ? '1E8449' : 'B03A2E'],
                                ],
                            ]);
                        }
                    } else {
                        // ── TEKNIS layout ────────────────────────────────────────
                        // Column positions are dynamic because multi-stage packages
                        // insert CBT + per-stage columns between Pelanggaran and Nilai Akhir.
                        // Scan the header row to locate each column by heading text.
                        // Use headerLookupRow because merged cells have values in the top row

                        $headerMap = [];
                        for ($c = 1; $c <= $colCount; $c++) {
                            $letter = Coordinate::stringFromColumnIndex($c);
                            $headerMap[(string) $sheet->getCell("{$letter}{$headerLookupRow}")->getValue()] = $letter;
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
                        $statusCol      = $findCol('Status Kelulusan');

                        // Statistik columns: Benar / Salah / Tidak Dijawab
                        if ($includeStats) {
                            $benarCol   = $findCol('Benar');
                            $salahCol   = $findCol('Salah');
                            $tdkCol     = $findCol('Tidak Dijawab');
                            foreach (array_filter([$benarCol, $salahCol, $tdkCol]) as $statCol) {
                                $sheet->getStyle("{$statCol}{$firstDataRow}:{$statCol}{$lastRow}")
                                    ->getNumberFormat()->setFormatCode('0');
                                $sheet->getStyle("{$statCol}{$firstDataRow}:{$statCol}{$lastRow}")
                                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            }
                            if ($benarCol) {
                                for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                                    $sheet->getStyle("{$benarCol}{$row}")->applyFromArray(['font' => ['color' => ['rgb' => '1E8449'], 'bold' => true]]);
                                }
                            }
                            if ($salahCol) {
                                for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                                    $sheet->getStyle("{$salahCol}{$row}")->applyFromArray(['font' => ['color' => ['rgb' => 'B03A2E'], 'bold' => true]]);
                                }
                            }
                            if ($tdkCol) {
                                for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                                    $sheet->getStyle("{$tdkCol}{$row}")->applyFromArray(['font' => ['color' => ['rgb' => 'D68910'], 'bold' => true]]);
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
                                $sheet->getStyle("{$letter}{$firstDataRow}:{$letter}{$lastRow}")
                                    ->getNumberFormat()->setFormatCode('0.00');
                                $sheet->getStyle("{$letter}{$firstDataRow}:{$letter}{$lastRow}")
                                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                                for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                                    $sheet->getStyle("{$letter}{$row}")->applyFromArray([
                                        'font' => ['bold' => true, 'color' => ['rgb' => '2874A6']],
                                    ]);
                                }
                            }
                        }
                    }

                    // ── Shared trailing columns (Pelanggaran, Nilai, NAB, Status Kelulusan) ──
                    // Only process if columns were found
                    if ($pelanggaranCol && $nilaiCol && $nabCol && $statusCol) {
                        // Pelanggaran: center + red if > 0
                        $pelanggaranRange = "{$pelanggaranCol}{$firstDataRow}:{$pelanggaranCol}{$lastRow}";
                        $sheet->getStyle($pelanggaranRange)
                            ->getNumberFormat()->setFormatCode('0');
                        $sheet->getStyle($pelanggaranRange)
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                            $val = (int) $sheet->getCell("{$pelanggaranCol}{$row}")->getValue();
                            if ($val > 0) {
                                $sheet->getStyle("{$pelanggaranCol}{$row}")->applyFromArray([
                                    'font' => ['color' => ['rgb' => 'B03A2E'], 'bold' => true],
                                ]);
                            }
                        }

                        // Nilai: 2 decimal places + center
                        $nilaiRange = "{$nilaiCol}{$firstDataRow}:{$nilaiCol}{$lastRow}";
                        $sheet->getStyle($nilaiRange)
                            ->getNumberFormat()->setFormatCode('0.00');
                        $sheet->getStyle($nilaiRange)
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        // Color nilai red/green based on pass/fail (compare with NAB)
                        for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                            $nilai = (float) $sheet->getCell("{$nilaiCol}{$row}")->getValue();
                            $nab   = (float) $sheet->getCell("{$nabCol}{$row}")->getValue();
                            $pass  = $nilai >= $nab && $nab > 0;
                            $sheet->getStyle("{$nilaiCol}{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => $pass ? '1E8449' : 'B03A2E']],
                            ]);
                        }

                        // NAB: whole number + center
                        $nabRange = "{$nabCol}{$firstDataRow}:{$nabCol}{$lastRow}";
                        $sheet->getStyle($nabRange)
                            ->getNumberFormat()->setFormatCode('0');
                        $sheet->getStyle($nabRange)
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        // Status Kelulusan: bold + green/red
                        for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                            $cellVal = $sheet->getCell("{$statusCol}{$row}")->getValue();
                            $color   = $cellVal === 'LULUS' ? '1E8449' : 'B03A2E';
                            $sheet->getStyle("{$statusCol}{$row}")->applyFromArray([
                                'font'      => ['bold' => true, 'color' => ['rgb' => $color]],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);
                        }
                    }
                }
            },
        ]);
    }
}
