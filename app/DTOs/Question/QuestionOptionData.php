<?php

declare(strict_types=1);

namespace App\DTOs\Question;

use Filament\Forms\Components\RichEditor\RichContentRenderer;

/**
 * Immutable value object representing a single answer option.
 * Used as a typed element inside CreateQuestionDTO / UpdateQuestionDTO.
 */
final readonly class QuestionOptionData
{
    public function __construct(
        /** HTML content of the answer (may contain images / LaTeX). */
        public string $answerText,

        /** TRUE = this option is the correct answer (technical questions). */
        public bool $isCorrect = false,

        /** Explicit point value for structural / scored questions. */
        public int $score = 0,

        /** Soft-toggle: option is active and visible to test-takers. */
        public bool $isActive = true,
    ) {}

    /**
     * Inflate from the raw Filament repeater array element.
     *
     * Filament v5's RichEditor stores state as a TipTap JSON document
     * (array) rather than an HTML string.  This is the system boundary
     * where we normalise that representation to plain HTML so that the
     * Service layer and all downstream consumers always see a string.
     *
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self(
            answerText: self::resolveHtml($raw['answer_text'] ?? ''),
            isCorrect: (bool) ($raw['is_correct'] ?? false),
            score: (int)  ($raw['score']      ?? 0),
            isActive: (bool) ($raw['is_active']  ?? true),
        );
    }

    /**
     * Normalise a value that may be a TipTap JSON array or an HTML string
     * into a plain HTML string.
     *
     * This is the single canonical place for the conversion — no other file
     * should call RichContentRenderer for option content.
     *
     * @param mixed $value
     */
    private static function resolveHtml(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value) && isset($value['type'])) {
            try {
                return RichContentRenderer::make($value)->toHtml();
            } catch (\Throwable) {
                return '';
            }
        }

        return '';
    }

    /**
     * Serialize back to a plain array for model persistence.
     *
     * For Technical questions (is_correct-based), score is derived from
     * isCorrect so we never store 0 for the correct answer even though the
     * score field is hidden in the form.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'answer_text' => $this->answerText,
            'is_correct'  => $this->isCorrect,
            // Ensure the correct option always carries score=1 for Teknis questions.
            // For Mansoskul (is_correct always false) this has no effect; the
            // explicit $this->score from the form is used as-is.
            'score'       => $this->isCorrect ? 1 : $this->score,
            'is_active'   => $this->isActive,
        ];
    }
}
