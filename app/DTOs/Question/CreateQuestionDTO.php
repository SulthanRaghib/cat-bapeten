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
        // Convert each raw repeater element into a typed value object.
        $options = array_map(
            static fn(array $row): QuestionOptionData => QuestionOptionData::fromArray($row),
            (array) ($data['options'] ?? []),
        );

        return new self(
            examTypeId: (int) ($data['exam_type_id']      ?? 0),
            questionText: (string) ($data['question_text']       ?? ''),
            options: $options,
            explanation: (string) ($data['explanation']         ?? ''),
            questionUnitId: isset($data['question_unit_id']) ? (int) $data['question_unit_id'] : null,
            questionSubUnitId: isset($data['question_sub_unit_id']) ? (int) $data['question_sub_unit_id'] : null,
            unit: (string) ($data['unit']                ?? ''),
            subUnit: (string) ($data['sub_unit']            ?? ''),
            category: (string) ($data['category']            ?? ''),
            competenceArea: (string) ($data['competence_area']     ?? ''),
            competenceSubArea: (string) ($data['competence_sub_area'] ?? ''),
        );
    }
}
