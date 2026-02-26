<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Exports\ExamResultsExcelExport;
use App\Models\ExamPackage;
use App\Models\ExamSession;
use App\Services\ExamResultsPdfExportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Columns\Column;

/**
 * Header Action for exporting exam results with filters.
 *
 * Displays a modal with filter controls and format selector (Excel/PDF),
 * then delegates to the appropriate export strategy.
 */
class ExportExamResultsHeaderAction
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

    /** Hitung jawaban benar */
    private static function countCorrect(ExamSession $record): int
    {
        return $record->answers()->where('score', '>', 0)
            ->whereNotNull('answer')->where('answer', '!=', '')->count();
    }

    /** Hitung jawaban salah */
    private static function countWrong(ExamSession $record): int
    {
        return $record->answers()->where('score', '<=', 0)
            ->whereNotNull('answer')->where('answer', '!=', '')->count();
    }

    /** Hitung tidak dijawab */
    private static function countUnanswered(ExamSession $record): int
    {
        $totalQ = count($record->answers_meta ?? []);
        if ($totalQ === 0) {
            $totalQ = $record->examPackage?->questions()->count() ?? 0;
        }
        $answered = $record->answers()
            ->whereNotNull('answer')->where('answer', '!=', '')->count();
        return max(0, $totalQ - $answered);
    }

    public static function make(): Action
    {
        return Action::make('exportExamResults')
            ->label('Unduh Laporan')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->modalHeading('Unduh Laporan Hasil Ujian')
            ->modalDescription('Atur filter dan format export yang diinginkan.')
            ->modalSubmitActionLabel('Download')
            ->modalWidth('lg')
            ->schema([
                Section::make('Filter Data')
                    ->description('Kosongkan filter untuk mengekspor semua data.')
                    ->icon('heroicon-o-funnel')
                    ->collapsible()
                    ->schema([
                        Select::make('filter_exam_package_id')
                            ->label('Paket Ujian')
                            ->options(fn() => ExamPackage::pluck('title', 'id'))
                            ->placeholder('Semua Paket Ujian')
                            ->searchable()
                            ->native(false),

                        Select::make('filter_status_kelulusan')
                            ->label('Status Kelulusan')
                            ->options([
                                'lulus'       => 'Lulus',
                                'tidak_lulus' => 'Tidak Lulus',
                            ])
                            ->placeholder('Semua Status')
                            ->native(false),

                        DatePicker::make('filter_dari_tanggal')
                            ->label('Dari Tanggal'),

                        DatePicker::make('filter_sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->columns(2),

                Section::make('Opsi Export')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Select::make('export_format')
                            ->label('Format File')
                            ->options([
                                'excel' => 'Excel (.xlsx) — Data tabular lengkap',
                                'pdf'   => 'PDF (.pdf) — Cetak laporan resmi',
                            ])
                            ->default('excel')
                            ->required()
                            ->native(false),

                        Toggle::make('include_statistics')
                            ->label('Sertakan statistik jawaban (Benar, Salah, Tidak Dijawab)')
                            ->default(true)
                            ->helperText('Jika dinonaktifkan, hanya menampilkan nilai akhir dan status kelulusan.'),
                    ]),
            ])
            ->action(function (array $data, Action $action) {
                $query = self::buildFilteredQuery($data);
                $format = $data['export_format'];
                $includeStatistics = (bool) ($data['include_statistics'] ?? true);
                $filterMeta = self::buildFilterMeta($data);

                if ($format === 'pdf') {
                    return self::handlePdfExport($query, $includeStatistics, $filterMeta);
                }

                return self::handleExcelExport($action, $data, $includeStatistics);
            });
    }

    /**
     * Build a filtered query from the modal form data.
     */
    private static function buildFilteredQuery(array $data): Builder
    {
        /** @var Builder $query */
        $query = ExamSession::query()
            ->where('status', 'completed')
            ->with(['user', 'examPackage', 'examParticipant', 'answers']);

        if ($packageId = $data['filter_exam_package_id'] ?? null) {
            $query->whereHas('examParticipant', function (Builder $sub) use ($packageId) {
                $sub->where('exam_package_id', $packageId);
            });
        }

        if ($date = $data['filter_dari_tanggal'] ?? null) {
            $query->whereDate('started_at', '>=', $date);
        }

        if ($date = $data['filter_sampai_tanggal'] ?? null) {
            $query->whereDate('started_at', '<=', $date);
        }

        if ($status = $data['filter_status_kelulusan'] ?? null) {
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
    }

    /**
     * Build human-readable filter summary for PDF header.
     */
    private static function buildFilterMeta(array $data): array
    {
        $meta = [];

        if ($id = $data['filter_exam_package_id'] ?? null) {
            $meta['Paket Ujian'] = ExamPackage::find($id)?->title ?? '-';
        }
        if ($status = $data['filter_status_kelulusan'] ?? null) {
            $meta['Status'] = match ($status) {
                'lulus'       => 'Lulus',
                'tidak_lulus' => 'Tidak Lulus',
                default       => $status,
            };
        }
        if ($date = $data['filter_dari_tanggal'] ?? null) {
            $meta['Dari Tanggal'] = $date;
        }
        if ($date = $data['filter_sampai_tanggal'] ?? null) {
            $meta['Sampai Tanggal'] = $date;
        }

        return $meta;
    }

    private static function handleExcelExport(Action $action, array $data, bool $includeStatistics): mixed
    {
        $export = ExamResultsExcelExport::make();
        $export->setFilterData($data);
        $export->setIncludeStatistics($includeStatistics);

        // Build filename
        $parts = ['Laporan_Hasil_Ujian_BAPETEN'];
        if ($id = $data['filter_exam_package_id'] ?? null) {
            $name = ExamPackage::find($id)?->title ?? '';
            if ($name !== '') {
                $parts[] = \Illuminate\Support\Str::slug($name, '_');
            }
        }
        $parts[] = date('d-m-Y');
        $export->withFilename(implode('_', $parts));

        // Define columns
        $columns = [
            Column::make('nama')
                ->heading('Nama Lengkap')
                ->getStateUsing(fn(ExamSession $record): string => $record->user?->name ?? '-'),

            Column::make('nip')
                ->heading('NIP')
                ->format('@')
                ->getStateUsing(fn(ExamSession $record): string => (string) ($record->user?->nip ?? '-')),

            Column::make('examPackage.title')
                ->heading('Nama Ujian / Paket'),

            Column::make('tgl_ujian')
                ->heading('Tanggal Pelaksanaan')
                ->getStateUsing(fn(ExamSession $record): string => $record->started_at ? $record->started_at->format('d/m/Y') : '-'),

            Column::make('waktu_mulai')
                ->heading('Waktu Mulai')
                ->getStateUsing(fn(ExamSession $record): string => $record->started_at ? $record->started_at->format('H:i') . ' WIB' : '-'),

            Column::make('waktu_selesai')
                ->heading('Waktu Selesai')
                ->getStateUsing(fn(ExamSession $record): string => $record->finished_at ? $record->finished_at->format('H:i') . ' WIB' : '-'),

            Column::make('durasi_ujian')
                ->heading('Durasi Ujian')
                ->getStateUsing(fn(ExamSession $record): string => self::formatDuration($record)),
        ];

        // Statistik jawaban (opsional)
        if ($includeStatistics) {
            $columns[] = Column::make('jawaban_benar')
                ->heading('Benar')
                ->getStateUsing(fn(ExamSession $record): int => self::countCorrect($record));

            $columns[] = Column::make('jawaban_salah')
                ->heading('Salah')
                ->getStateUsing(fn(ExamSession $record): int => self::countWrong($record));

            $columns[] = Column::make('tidak_dijawab')
                ->heading('Tidak Dijawab')
                ->getStateUsing(fn(ExamSession $record): int => self::countUnanswered($record));
        }

        // Kolom nilai dan status
        $columns[] = Column::make('total_score')
            ->heading('Nilai Akhir');

        $columns[] = Column::make('kkm')
            ->heading('Nilai Kelulusan (KKM)')
            ->getStateUsing(fn(ExamSession $record): int|string => $record->examPackage->passing_grade ?? '-');

        $columns[] = Column::make('status_kelulusan')
            ->heading('Keterangan')
            ->getStateUsing(fn(ExamSession $record): string => self::isLulus($record) ? 'LULUS' : 'TIDAK LULUS');

        $export->withColumns($columns);

        return app()->call([$export, 'hydrate'], [
            'livewire' => $action->getLivewire(),
            'records'  => null,
            'formData' => [],
        ])->export();
    }

    private static function handlePdfExport(Builder $query, bool $includeStatistics, array $filterMeta): mixed
    {
        $sessions = $query->get();

        return app(ExamResultsPdfExportService::class)->download($sessions, $includeStatistics, $filterMeta);
    }
}
