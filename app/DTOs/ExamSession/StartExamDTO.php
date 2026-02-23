<?php

declare(strict_types=1);

namespace App\DTOs\ExamSession;

/**
 * Immutable DTO for starting a new exam session.
 * The Livewire component (delivery layer) inflates this and passes it
 * to ExamSessionService::start().
 */
final readonly class StartExamDTO
{
    public function __construct(
        public int $examParticipantId,
    ) {}
}
