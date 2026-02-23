<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ExamSession\FinishExamDTO;
use App\DTOs\ExamSession\SaveAnswerDTO;
use App\DTOs\ExamSession\StartExamDTO;
use App\Models\ExamAnswer;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ExamSessionService — owns the full lifecycle of an exam session.
 *
 * Responsibilities:
 *  - Start:   create session, shuffle questions (delegated to model event).
 *  - Answer:  upsert answer row, calculate & persist score instantly.
 *  - Finish:  mark session completed, tally total score, deactivate participant.
 *
 * Every mutating method is wrapped in a DB transaction so partial writes
 * cannot occur even under network failures or PHP fatals.
 */
final class ExamSessionService
{
    // ──────────────────────────────────────────────────────────────────────
    // START
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Start a new exam session for a participant.
     *
     * Business rules enforced here:
     *  - A participant may not have more than one 'ongoing' session.
     *  - Question shuffle order is generated inside ExamSession::booted().
     *
     * @throws \RuntimeException  If the participant already has an active session.
     * @throws \Throwable
     */
    public function start(StartExamDTO $dto): ExamSession
    {
        return DB::transaction(function () use ($dto): ExamSession {
            $participant = ExamParticipant::findOrFail($dto->examParticipantId);

            // Guard: one active session at a time.
            if ($participant->activeSession() !== null) {
                throw new \RuntimeException(
                    "Participant #{$dto->examParticipantId} already has an ongoing session.",
                );
            }

            // The model's creating event sets started_at and shuffles questions.
            return ExamSession::create([
                'exam_participant_id' => $dto->examParticipantId,
                'status'              => 'ongoing',
            ]);
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // SAVE ANSWER
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Persist (or update) a single answer and calculate its score atomically.
     *
     * Using updateOrCreate so rapid repeated saves (e.g. autosave on every
     * option click) are idempotent.
     *
     * @throws \Throwable
     */
    public function saveAnswer(SaveAnswerDTO $dto): ExamAnswer
    {
        return DB::transaction(function () use ($dto): ExamAnswer {
            /** @var ExamAnswer $answer */
            $answer = ExamAnswer::updateOrCreate(
                [
                    'exam_session_id' => $dto->examSessionId,
                    'question_id'     => $dto->questionId,
                ],
                [
                    'answer'      => $dto->answer,
                    'is_doubtful' => $dto->isDoubtful,
                    // score = 0 as placeholder; calculateScore() overwrites it below.
                    'score'       => 0,
                ],
            );

            // Delegate score calculation to the model — it already knows the
            // question's scoring_config.  We only persist the result here.
            $answer->score = $answer->calculateScore();
            $answer->save();

            return $answer;
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // FINISH
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Finish the exam: tally scores, mark session completed,
     * and deactivate the participant so they cannot re-enter.
     *
     * @throws \RuntimeException  If the session is not in 'ongoing' status.
     * @throws \Throwable
     */
    public function finish(FinishExamDTO $dto): ExamSession
    {
        return DB::transaction(function () use ($dto): ExamSession {
            $session = ExamSession::findOrFail($dto->examSessionId);

            if ($session->status !== 'ongoing') {
                throw new \RuntimeException(
                    "Session #{$dto->examSessionId} is not ongoing (status: {$session->status}).",
                );
            }

            // Tally the total score from all persisted answers.
            $totalScore = (int) ExamAnswer::where('exam_session_id', $session->id)
                ->sum('score');

            // Persist the final state in a single update — no risk of race.
            $session->update([
                'status'      => 'completed',
                'finished_at' => $dto->finishedAt,
                'total_score' => $totalScore,
            ]);

            // Business rule: once an exam is submitted the participant's
            // is_active flag is turned off so they cannot start again.
            ExamParticipant::where('id', $dto->examParticipantId)
                ->update(['is_active' => false]);

            Log::info("Exam finished", [
                'session_id'    => $session->id,
                'participant'   => $dto->examParticipantId,
                'total_score'   => $totalScore,
                'finished_at'   => $dto->finishedAt,
            ]);

            return $session->fresh();
        });
    }
}
