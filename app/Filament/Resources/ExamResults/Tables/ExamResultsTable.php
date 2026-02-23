<?php

namespace App\Filament\Resources\ExamResults\Tables;

use App\Models\ExamSession;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\ExamResultsExcelExport;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class ExamResultsTable
{
    /** Hitung durasi ujian (detik) menjadi string human-readable */
    private static function formatDuration(ExamSession $record): string
    {
        if (! $record->started_at || ! $record->finished_at) {
            return '-';
        }
        $total = (int) $record->started_at->diffInSeconds($record->finished_at);
        $h     = intdiv($total, 3600);
        $m     = intdiv($total % 3600, 60);
        $s     = $total % 60;

        if ($h > 0) return "{$h} jam {$m} menit {$s} detik";
        if ($m > 0) return "{$m} menit {$s} detik";
        return "{$s} detik";
    }

    /** Apakah peserta lulus? */
    private static function isLulus(ExamSession $record): bool
    {
        return ($record->total_score ?? 0) >= ($record->examPackage->passing_grade ?? 0);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultSort('finished_at', 'desc')
            ->columns([

                // ── 1. Peserta ───────────────────────────────────────────
                TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->description(fn(ExamSession $record): string => 'NIP: ' . ($record->user->nip ?? '-'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                // ── 2. Paket Ujian ───────────────────────────────────────
                TextColumn::make('examPackage.title')
                    ->label('Paket Ujian')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                // ── 3. Tanggal & Waktu Ujian ─────────────────────────────
                TextColumn::make('started_at')
                    ->label('Tanggal Ujian')
                    ->description(
                        fn(ExamSession $record): string => ($record->started_at ? $record->started_at->format('H:i') : '-')
                            . ' – '
                            . ($record->finished_at ? $record->finished_at->format('H:i') : '-')
                            . ' WIB'
                    )
                    ->date('d M Y')
                    ->sortable(),

                // ── 4. Durasi ────────────────────────────────────────────
                TextColumn::make('durasi')
                    ->label('Durasi')
                    ->icon('heroicon-m-clock')
                    ->state(fn(ExamSession $record): string => self::formatDuration($record))
                    ->color('gray'),

                // ── 5. Nilai Akhir ───────────────────────────────────────
                TextColumn::make('total_score')
                    ->label('Nilai')
                    ->badge()
                    ->color(
                        fn(ExamSession $record): string =>
                        self::isLulus($record) ? 'success' : 'danger'
                    )
                    ->icon(
                        fn(ExamSession $record): string =>
                        self::isLulus($record) ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'
                    )
                    ->description(
                        fn(ExamSession $record): string =>
                        'KKM: ' . ($record->examPackage->passing_grade ?? '-')
                    )
                    ->sortable()
                    ->alignCenter(),

                // ── 6. Status Kelulusan ──────────────────────────────────
                TextColumn::make('kelulusan')
                    ->label('Status')
                    ->badge()
                    ->state(
                        fn(ExamSession $record): string =>
                        self::isLulus($record) ? 'LULUS' : 'TIDAK LULUS'
                    )
                    ->color(
                        fn(string $state): string =>
                        $state === 'LULUS' ? 'success' : 'danger'
                    )
                    ->icon(
                        fn(string $state): string =>
                        $state === 'LULUS' ? 'heroicon-m-trophy' : 'heroicon-m-x-circle'
                    )
                    ->alignCenter(),

            ])
            ->filters([

                SelectFilter::make('exam_package_id')
                    ->label('Paket Ujian')
                    ->relationship('examPackage', 'title'),

                Filter::make('rentang_waktu')
                    ->label('Rentang Tanggal Ujian')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $q, $date) => $q->whereDate('started_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $q, $date) => $q->whereDate('started_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Dari: ' . $data['dari_tanggal'];
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Sampai: ' . $data['sampai_tanggal'];
                        }
                        return $indicators;
                    }),

                Filter::make('status_kelulusan')
                    ->label('Status Kelulusan')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->label('Filter Status')
                            ->placeholder('Semua Status')
                            ->options([
                                'lulus'       => 'Lulus',
                                'tidak_lulus' => 'Tidak Lulus',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['status'] ?? null, function (Builder $q, $status) {
                            $operator = $status === 'lulus' ? '>=' : '<';
                            $q->whereRaw("total_score {$operator} (
                                SELECT ep.passing_grade
                                FROM exam_packages ep
                                JOIN exam_participants part ON part.exam_package_id = ep.id
                                WHERE part.id = exam_sessions.exam_participant_id
                                LIMIT 1
                            )");
                        });
                    })
                    ->indicateUsing(fn(array $data): ?string => match ($data['status'] ?? null) {
                        'lulus'       => 'Status: Lulus',
                        'tidak_lulus' => 'Status: Tidak Lulus',
                        default       => null,
                    }),

            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Unduh Laporan Excel')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->exports([
                        ExamResultsExcelExport::make()
                            ->withFilename(
                                'Laporan_Hasil_Ujian_BAPETEN_' . date('d-m-Y')
                            )
                            ->withColumns([

                                // 1. Nama Lengkap
                                Column::make('nama')
                                    ->heading('Nama Lengkap')
                                    ->getStateUsing(
                                        fn(ExamSession $record): string =>
                                        $record->user?->name ?? '-'
                                    ),

                                // 2. NIP — format teks (@) mencegah notasi ilmiah;
                                //    pemformatan nomor bulat & rata kanan diatur di AfterSheet.
                                Column::make('nip')
                                    ->heading('NIP')
                                    ->format('@')   // text cell → angka penuh tanpa desimal
                                    ->getStateUsing(
                                        fn(ExamSession $record): string =>
                                        (string) ($record->user?->nip ?? '-')
                                    ),

                                // 3. Paket Ujian
                                Column::make('examPackage.title')
                                    ->heading('Nama Ujian / Paket'),

                                // 4. Tanggal Pelaksanaan
                                Column::make('tgl_ujian')
                                    ->heading('Tanggal Pelaksanaan')
                                    ->getStateUsing(
                                        fn(ExamSession $record): string =>
                                        $record->started_at
                                            ? $record->started_at->format('d/m/Y')
                                            : '-'
                                    ),

                                // 5. Waktu Mulai
                                Column::make('waktu_mulai')
                                    ->heading('Waktu Mulai')
                                    ->getStateUsing(
                                        fn(ExamSession $record): string =>
                                        $record->started_at
                                            ? $record->started_at->format('H:i') . ' WIB'
                                            : '-'
                                    ),

                                // 6. Waktu Selesai
                                Column::make('waktu_selesai')
                                    ->heading('Waktu Selesai')
                                    ->getStateUsing(
                                        fn(ExamSession $record): string =>
                                        $record->finished_at
                                            ? $record->finished_at->format('H:i') . ' WIB'
                                            : '-'
                                    ),

                                // 7. Durasi
                                Column::make('durasi_ujian')
                                    ->heading('Durasi Ujian')
                                    ->getStateUsing(
                                        fn(ExamSession $record): string =>
                                        self::formatDuration($record)
                                    ),

                                // 8. Nilai Akhir
                                Column::make('total_score')
                                    ->heading('Nilai Akhir'),

                                // 9. Nilai Kelulusan (KKM)
                                Column::make('kkm')
                                    ->heading('Nilai Kelulusan (KKM)')
                                    ->getStateUsing(
                                        fn(ExamSession $record): int|string =>
                                        $record->examPackage->passing_grade ?? '-'
                                    ),

                                // 10. Status Kelulusan
                                Column::make('status_kelulusan')
                                    ->heading('Keterangan')
                                    ->getStateUsing(
                                        fn(ExamSession $record): string =>
                                        self::isLulus($record) ? 'LULUS' : 'TIDAK LULUS'
                                    ),

                            ]),
                    ]),

            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat Detail'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Hasil Terpilih')
                        ->modalHeading('Hapus Hasil Ujian Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus hasil ujian terpilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])->label('Tindakan Massal'),
            ]);
    }
}
