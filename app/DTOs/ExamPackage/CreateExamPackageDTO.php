<?php

declare(strict_types=1);

namespace App\DTOs\ExamPackage;

/**
 * Immutable DTO for creating or updating an ExamPackage.
 */
final readonly class CreateExamPackageDTO
{
    public function __construct(
        public string  $title,
        public int     $examTypeId,
        public int     $passingGrade,
        public int     $durationMinutes,
        public bool    $isActive = true,
        public ?string $startTime = null,
        public ?string $endTime = null,
        public ?array  $technicalScoringConfig = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromFormData(array $data): self
    {
        // Flatten dot-notation form data for technical_scoring_config
        $tsc = $data['technical_scoring_config'] ?? null;
        if (is_array($tsc) && empty(array_filter($tsc, fn($v) => $v !== null && $v !== '' && $v !== false))) {
            $tsc = null;
        }

        return new self(
            title: (string) ($data['title']              ?? ''),
            examTypeId: (int)    ($data['exam_type_id']       ?? 0),
            passingGrade: (int)    ($data['passing_grade']       ?? 0),
            durationMinutes: (int)    ($data['duration_minutes']    ?? 60),
            isActive: (bool)   ($data['is_active']           ?? true),
            startTime: isset($data['start_time']) && $data['start_time'] !== '' ? (string) $data['start_time'] : null,
            endTime: isset($data['end_time'])   && $data['end_time']   !== '' ? (string) $data['end_time']   : null,
            technicalScoringConfig: is_array($tsc) ? $tsc : null,
        );
    }
}
