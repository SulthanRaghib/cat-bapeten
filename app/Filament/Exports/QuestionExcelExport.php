<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

/**
 * Excel export for Bank Soal.
 *
 * - All HTML content is stripped to plain text.
 * - Embedded images are extracted as URL links (no image data).
 * - Supports filter passthrough and toggleable answer-key.
 */
class QuestionExcelExport extends ExcelExport
{
    protected array $filterData = [];
    protected bool $includeAnswerKey = true;

    public function setFilterData(array $data): static
    {
        $this->filterData = $data;

        return $this;
    }

    public function setIncludeAnswerKey(bool $value): static
    {
        $this->includeAnswerKey = $value;

        return $this;
    }

    public function setUp(): void
    {
        $this->withFilename('bank-soal-' . now()->format('Y-m-d'));

        $this->modifyQueryUsing(function ($query) {
            $query->with(['examType', 'questionUnit', 'questionSubUnit']);

            if ($v = $this->filterData['filter_exam_type_id'] ?? null) {
                $query->where('exam_type_id', $v);
            }
            if ($v = $this->filterData['filter_question_unit_id'] ?? null) {
                $query->where('question_unit_id', $v);
            }
            if ($v = $this->filterData['filter_question_sub_unit_id'] ?? null) {
                $query->where('question_sub_unit_id', $v);
            }
            if ($v = $this->filterData['filter_category'] ?? null) {
                $query->where('category', $v);
            }

            return $query;
        });

        $includeAnswerKey = $this->includeAnswerKey;

        $this->withColumns(array_filter([
            Column::make('id')
                ->heading('ID'),

            Column::make('examType.name')
                ->heading('Tipe Soal')
                ->getStateUsing(fn($record) => $record->examType?->name ?? '-'),

            Column::make('questionUnit.name')
                ->heading('Unit (Materi/Bab)')
                ->getStateUsing(fn($record) => $record->questionUnit?->name ?? '-'),

            Column::make('questionSubUnit.name')
                ->heading('Sub Unit (Sub-Bab)')
                ->getStateUsing(fn($record) => $record->questionSubUnit?->name ?? '-'),

            Column::make('category')
                ->heading('Kategori')
                ->formatStateUsing(fn($state) => match ($state) {
                    'easy'   => 'Mudah',
                    'medium' => 'Sedang',
                    'hard'   => 'Sulit',
                    default  => $state ?? '-',
                }),

            Column::make('question_text')
                ->heading('Pertanyaan')
                ->getStateUsing(fn($record) => self::cleanText($record->question_text ?? '')),

            Column::make('question_images')
                ->heading('Link Gambar Soal')
                ->getStateUsing(fn($record) => self::extractImageUrls($record->question_text ?? '')),

            Column::make('options_formatted')
                ->heading('Opsi Jawaban')
                ->getStateUsing(fn($record) => self::formatOptions($record, $includeAnswerKey)),

            $includeAnswerKey
                ? Column::make('answer_key')
                ->heading('Kunci Jawaban')
                ->getStateUsing(fn($record) => self::formatAnswerKey($record))
                : null,

            Column::make('created_at')
                ->heading('Dibuat')
                ->formatStateUsing(fn($state) => $state?->format('d M Y H:i') ?? '-'),
        ]));
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Strip HTML tags and sanitize UTF-8.
     */
    private static function cleanText(string $html): string
    {
        $text = strip_tags($html);

        return self::utf8($text);
    }

    /**
     * Extract all image URLs from HTML and return them as newline-separated list.
     */
    private static function extractImageUrls(string $html): string
    {
        if (empty($html) || stripos($html, '<img') === false) {
            return '-';
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

        if (empty($matches[1])) {
            return '-';
        }

        return implode("\n", $matches[1]);
    }

    /**
     * Strip invalid UTF-8 sequences so json_encode() never fails.
     */
    private static function utf8(string $value): string
    {
        $clean = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean) ?? $clean;
    }

    /**
     * Format options into a human-readable string.
     */
    private static function formatOptions(mixed $record, bool $includeAnswerKey): string
    {
        $options = $record->options ?? [];
        if (empty($options)) {
            return '-';
        }

        $parts = [];
        foreach ($options as $index => $opt) {
            if (! is_array($opt)) {
                continue;
            }

            $letter = chr(65 + (int) $index);
            $text   = self::cleanText($opt['answer_text'] ?? '');

            $meta = [];
            if ($includeAnswerKey) {
                if (! empty($opt['is_correct'])) {
                    $meta[] = 'Benar';
                }
                if (isset($opt['score']) && $opt['score'] !== '' && $opt['score'] !== null) {
                    $meta[] = 'Skor: ' . $opt['score'];
                }
            }

            $suffix = $meta !== [] ? ' (' . implode(' / ', $meta) . ')' : '';
            $parts[] = "{$letter}: {$text}{$suffix}";
        }

        return self::utf8(implode(' | ', $parts));
    }

    /**
     * Extract the correct answer letter(s).
     */
    private static function formatAnswerKey(mixed $record): string
    {
        $options = $record->options ?? [];
        if (empty($options)) {
            return '-';
        }

        $correct = [];
        foreach ($options as $index => $opt) {
            if (is_array($opt) && ! empty($opt['is_correct'])) {
                $correct[] = chr(65 + (int) $index);
            }
        }

        return $correct !== [] ? implode(', ', $correct) : '-';
    }
}
