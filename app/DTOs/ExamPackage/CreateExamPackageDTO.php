<?php

declare(strict_types=1);

namespace App\DTOs\ExamPackage;

/**
 * Immutable DTO for creating a new ExamPackage.
 */
final readonly class CreateExamPackageDTO
{
    public function __construct(
        public string $title,
        public string $type,
        public int    $passingGrade,
        public int    $durationMinutes,
        public bool   $isActive = true,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromFormData(array $data): self
    {
        return new self(
            title: (string) ($data['title']            ?? ''),
            type: (string) ($data['type']             ?? 'technical'),
            passingGrade: (int)    ($data['passing_grade']    ?? 0),
            durationMinutes: (int)    ($data['duration_minutes'] ?? 60),
            isActive: (bool)   ($data['is_active']        ?? true),
        );
    }
}
