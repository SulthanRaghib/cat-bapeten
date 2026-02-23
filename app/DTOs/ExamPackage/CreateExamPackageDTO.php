<?php

declare(strict_types=1);

namespace App\DTOs\ExamPackage;

/**
 * Immutable DTO for creating a new ExamPackage.
 */
final readonly class CreateExamPackageDTO
{
    public function __construct(
        public string $name,
        public string $description = '',
        public int    $duration    = 60,
        public bool   $isActive    = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromFormData(array $data): self
    {
        return new self(
            name: (string) ($data['name']        ?? ''),
            description: (string) ($data['description'] ?? ''),
            duration: (int)    ($data['duration']    ?? 60),
            isActive: (bool)   ($data['is_active']   ?? false),
        );
    }
}
