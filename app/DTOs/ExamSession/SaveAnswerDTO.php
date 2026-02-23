<?php

declare(strict_types=1);

namespace App\DTOs\ExamSession;

/**
 * Immutable DTO for saving / updating a single answer in an ongoing session.
 */
final readonly class SaveAnswerDTO
{
    public function __construct(
        public int    $examSessionId,
        public int    $questionId,

        /** The option code the test-taker chose, e.g. 'A', 'B', 'C' ... */
        public string $answer,

        /** Whether the test-taker flagged this question for later review. */
        public bool   $isDoubtful = false,
    ) {}
}
