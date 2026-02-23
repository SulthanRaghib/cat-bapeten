<?php

declare(strict_types=1);

namespace App\DTOs\ExamSession;

/**
 * Immutable DTO for finishing / submitting an exam session.
 * Carries the authoritative finished-at timestamp so the service does
 * not depend on wall-clock time directly (easier to test).
 */
final readonly class FinishExamDTO
{
    public function __construct(
        public int    $examSessionId,
        public int    $examParticipantId,

        /** Pass Carbon::now() from the delivery layer. */
        public \DateTimeInterface $finishedAt,
    ) {}
}
