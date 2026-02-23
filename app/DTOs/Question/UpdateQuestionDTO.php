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
        public string $type,
        public string $questionText,
        public array  $options,
        public string $explanation      = '',
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
        $options = array_map(
            static fn(array $row): QuestionOptionData => QuestionOptionData::fromArray($row),
            (array) ($data['options'] ?? []),
        );

        return new self(
            type: (string) ($data['type']                ?? ''),
            questionText: (string) ($data['question_text']       ?? ''),
            options: $options,
            explanation: (string) ($data['explanation']         ?? ''),
            unit: (string) ($data['unit']                ?? ''),
            subUnit: (string) ($data['sub_unit']            ?? ''),
            category: (string) ($data['category']            ?? ''),
            competenceArea: (string) ($data['competence_area']     ?? ''),
            competenceSubArea: (string) ($data['competence_sub_area'] ?? ''),
        );
    }
}
