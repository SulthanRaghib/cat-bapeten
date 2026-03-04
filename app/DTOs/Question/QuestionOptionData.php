<?php

declare(strict_types=1);

namespace App\DTOs\Question;

/**
 * Immutable value object representing a single answer option.
 * Used as a typed element inside CreateQuestionDTO / UpdateQuestionDTO.
 */
final readonly class QuestionOptionData
{
    public function __construct(
        /** Raw HTML content of the answer (may contain images / LaTeX). */
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
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self(
            answerText: (string) ($raw['answer_text'] ?? ''),
            isCorrect: (bool)   ($raw['is_correct']  ?? false),
            score: (int)    ($raw['score']        ?? 0),
            isActive: (bool)   ($raw['is_active']    ?? true),
        );
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
