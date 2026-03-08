<?php

declare(strict_types=1);

namespace App\DTOs\Question;

/**
 * Extracts the repeated form-to-DTO mapping logic that was previously
 * copy-pasted inside CreateQuestionDTO::fromFormData() and
 * UpdateQuestionDTO::fromFormData().
 *
 * Both DTOs are intentionally separate (ISP — each use-case may evolve
 * independently) but they currently share the same field set, so the
 * field extraction lives here to honour DRY.
 *
 * This class contains pure, side-effect-free static helpers — no
 * constructor required.
 *
 * @internal  Used only by CreateQuestionDTO and UpdateQuestionDTO.
 */
final class QuestionFormDataMapper
{
    /**
     * Convert Filament repeater rows into typed QuestionOptionData objects.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return list<QuestionOptionData>
     */
    public static function mapOptions(array $rows): array
    {
        return array_values(array_map(
            static fn(array $row): QuestionOptionData => QuestionOptionData::fromArray($row),
            $rows,
        ));
    }

    /**
     * Extract scalar question fields from raw form data, applying safe
     * type casts and default values.
     *
     * @param  array<string, mixed>  $data
     * @return array{
     *     examTypeId: int,
     *     questionText: string,
     *     options: list<QuestionOptionData>,
     *     explanation: string,
     *     questionUnitId: int|null,
     *     questionSubUnitId: int|null,
     *     unit: string,
     *     subUnit: string,
     *     category: string,
     *     competenceArea: string,
     *     competenceSubArea: string,
     * }
     */
    public static function extract(array $data): array
    {
        return [
            'examTypeId'       => (int)    ($data['exam_type_id']       ?? 0),
            'questionText'     => (string) ($data['question_text']       ?? ''),
            'options'          => self::mapOptions((array) ($data['options'] ?? [])),
            'explanation'      => (string) ($data['explanation']         ?? ''),
            'questionUnitId'   => isset($data['question_unit_id'])    ? (int) $data['question_unit_id']    : null,
            'questionSubUnitId' => isset($data['question_sub_unit_id']) ? (int) $data['question_sub_unit_id'] : null,
            'unit'             => (string) ($data['unit']               ?? ''),
            'subUnit'          => (string) ($data['sub_unit']           ?? ''),
            'category'         => (string) ($data['category']           ?? ''),
            'competenceArea'   => (string) ($data['competence_area']    ?? ''),
            'competenceSubArea' => (string) ($data['competence_sub_area'] ?? ''),
        ];
    }
}
