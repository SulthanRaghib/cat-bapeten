<?php

declare(strict_types=1);

namespace App\DTOs\Question;

/**
 * Immutable DTO for updating an existing Question.
 * Structurally identical to CreateQuestionDTO but separated so each
 * use-case can evolve independently (SOLID — Interface Segregation).
 */
final readonly class UpdateQuestionDTO
{
    /**
     * @param  list<QuestionOptionData>  $options
     */
    public function __construct(
        public int $examTypeId,
        public string $questionText,
        public array  $options,
        public string $explanation      = '',
        public ?int   $questionUnitId   = null,
        public ?int   $questionSubUnitId = null,
        public string $unit             = '',
        public string $subUnit          = '',
        public string $category         = '',
        public string $competenceArea   = '',
        public string $competenceSubArea = '',
    ) {}

    /**
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
