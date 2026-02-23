<?php

declare(strict_types=1);

namespace App\DTOs\ExamPackage;

/**
 * Immutable DTO used when attaching a User to an ExamPackage as a participant.
 * Token generation is deliberately left to the Service (business rule),
 * not to the delivery layer.
 */
final readonly class AssignParticipantDTO
{
    public function __construct(
        public int  $examPackageId,
        public int  $userId,
        public bool $isActive = true,
    ) {}
}
