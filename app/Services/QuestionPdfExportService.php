<?php

declare(strict_types=1);

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Generates a printable PDF from Question models.
 *
 * No images are embedded — embedded <img> tags are converted to
 * clickable/copyable URL text links for fast, lightweight output.
 */
class QuestionPdfExportService
{
    /**
     * Build and stream a PDF download.
     */
    public function download(
        Collection $questions,
        bool $includeAnswerKey = true,
        array $filterMeta = [],
    ): StreamedResponse {
        $questions->loadMissing(['examType', 'questionUnit', 'questionSubUnit']);

        // Clone models so Livewire state is never mutated.
        // Strip HTML to plain text and extract image URLs as links.
        $processed = $questions->map(function ($question) {
            $clone = clone $question;
            $clone->setRelations($question->getRelations());

            // Extract image links before stripping HTML
            $clone->setAttribute('question_image_links', $this->extractImageUrls($clone->question_text ?? ''));
            $clone->question_text = $this->sanitize($clone->question_text ?? '');

            $options = $clone->options ?? [];
            foreach ($options as $i => $opt) {
                if (is_array($opt) && isset($opt['answer_text'])) {
                    $options[$i]['image_links'] = $this->extractImageUrls($opt['answer_text']);
                    $options[$i]['answer_text'] = $this->sanitize($opt['answer_text']);
                }
            }
            $clone->options = $options;

            return $clone;
        });

        $stats = $this->buildStats($processed);

        $pdf = Pdf::loadView('exports.questions-pdf', [
            'questions'        => $processed,
            'includeAnswerKey' => $includeAnswerKey,
            'filterMeta'       => $filterMeta,
            'stats'            => $stats,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'sans-serif')
            ->setOption('dpi', 96);

        $filename = 'bank-soal' . $this->buildFilenameSlug($filterMeta) . '_' . now()->format('Ymd-His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    /**
     * Build a slug suffix from filter meta for use in the filename.
     * e.g. filterMeta = ['Tipe Soal' => 'Teknis', 'Unit' => 'Unit A'] → '_teknis_unit-a'
     */
    private function buildFilenameSlug(array $filterMeta): string
    {
        if (empty($filterMeta)) {
            return '';
        }
        $parts = [];
        foreach ($filterMeta as $value) {
            $slug = Str::slug((string) $value, '-');
            if ($slug !== '') {
                $parts[] = $slug;
            }
        }
        return $parts !== [] ? '_' . implode('_', $parts) : '';
    }

    /**
     * Sanitize HTML: fix UTF-8, strip tags, normalize whitespace.
     */
    private function sanitize(string $html): string
    {
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Collapse multiple whitespace/newlines into single space
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Extract all image URLs from HTML content.
     *
     * @return string[]
     */
    private function extractImageUrls(string $html): array
    {
        if (empty($html) || stripos($html, '<img') === false) {
            return [];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Build summary statistics.
     */
    private function buildStats(Collection $questions): array
    {
        return [
            'total'       => $questions->count(),
            'by_type'     => $questions->groupBy(fn($q) => $q->examType?->name ?? 'Tidak Ada Tipe')->map->count()->toArray(),
            'by_difficulty' => $questions->groupBy(fn($q) => match ($q->category) {
                'easy'   => 'Mudah',
                'medium' => 'Sedang',
                'hard'   => 'Sulit',
                default  => 'Tidak Ditentukan',
            })->map->count()->toArray(),
            'by_unit'     => $questions->groupBy(fn($q) => $q->questionUnit?->name ?? 'Tidak Ada Unit')->map->count()->toArray(),
        ];
    }
}
