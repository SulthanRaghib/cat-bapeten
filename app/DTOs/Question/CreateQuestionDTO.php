<?php

declare(strict_types=1);

namespace App\DTOs\Question;

/**
 * Immutable DTO for creating a new Question + its answer options.
 *
 * The delivery layer (Filament CreateRecord page) builds this object from
 * the validated form data and hands it to QuestionService::create().
 */
final readonly class CreateQuestionDTO
{
    /**
     * @param  list<QuestionOptionData>  $options  Typed answer-option value objects.
     */
    public function __construct(
        /** Foreign key to exam_types table. */
        public int $examTypeId,

        /** HTML body of the question (may contain images / LaTeX). */
        public string $questionText,

        /** Typed answer options already converted to value objects. */
        public array $options,

        /** Optional explanation shown after exam completion. */
        public string $explanation = '',

        // ── Dynamic Unit fields (master-data driven) ───────────────────────

        public ?int $questionUnitId    = null,
        public ?int $questionSubUnitId = null,

        // ── Technical-question fields (legacy) ─────────────────────────────

        public string $unit    = '',
        public string $subUnit = '',

        /** 'easy' | 'medium' | 'hard' */
        public string $category = '',

        // ── Structural-question fields (legacy) ────────────────────────────

        public string $competenceArea    = '',
        public string $competenceSubArea = '',
    ) {}

    /**
     * Inflate from the raw Filament form state array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromFormData(array $data): self
    {
        $fields = QuestionFormDataMapper::extract($data);

        return new self(
            examTypeId: $fields['examTypeId'],
            questionText: $fields['questionText'],
            options: $fields['options'],
            explanation: $fields['explanation'],
            questionUnitId: $fields['questionUnitId'],
            questionSubUnitId: $fields['questionSubUnitId'],
            unit: $fields['unit'],
            subUnit: $fields['subUnit'],
            category: $fields['category'],
            competenceArea: $fields['competenceArea'],
            competenceSubArea: $fields['competenceSubArea'],
        );
    }
}
