<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Filament\Exports\QuestionExcelExport;
use App\Services\QuestionPdfExportService;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Collection;

/**
 * Bulk Action for exporting selected questions.
 *
 * Displays a modal with format selector & answer key toggle,
 * then delegates to Excel or PDF export.
 */
class ExportQuestionsBulkAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('exportSelected')
            ->label('Export Terpilih')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->modalHeading('Export Soal Terpilih')
            ->modalDescription('Konfigurasi format export untuk soal yang dipilih.')
            ->modalSubmitActionLabel('Download')
            ->modalWidth('md')
            ->deselectRecordsAfterCompletion()
            ->schema([
                Section::make('Opsi Export')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Select::make('export_format')
                            ->label('Format File')
                            ->options([
                                'excel' => 'Excel (.xlsx) — Data tabular',
                                'pdf'   => 'PDF (.pdf) — Cetak dokumen',
                            ])
                            ->default('excel')
                            ->required()
                            ->native(false),

                        Toggle::make('include_answer_key')
                            ->label('Sertakan kunci jawaban & skor')
                            ->default(true)
                            ->helperText('Jika dinonaktifkan, kunci jawaban tidak ditampilkan pada hasil export.'),
                    ]),
            ])
            ->action(function (Collection $records, array $data, BulkAction $action) {
                $format = $data['export_format'];
                $includeAnswerKey = (bool) ($data['include_answer_key'] ?? true);

                if ($format === 'pdf') {
                    return self::handlePdfExport($records, $includeAnswerKey);
                }

                return self::handleExcelExport($records, $action, $includeAnswerKey);
            });
    }

    private static function handleExcelExport(Collection $records, BulkAction $action, bool $includeAnswerKey): mixed
    {
        $export = QuestionExcelExport::make('bank-soal');
        $export->setIncludeAnswerKey($includeAnswerKey);

        return app()->call([$export, 'hydrate'], [
            'livewire' => $action->getLivewire(),
            'records'  => $records,
            'formData' => [],
        ])->export();
    }

    private static function handlePdfExport(Collection $records, bool $includeAnswerKey): mixed
    {
        $filterMeta = ['Seleksi' => "{$records->count()} soal dipilih"];

        return app(QuestionPdfExportService::class)->download($records, $includeAnswerKey, $filterMeta);
    }
}
