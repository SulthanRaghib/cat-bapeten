<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ExamSession\FinishExamDTO;
use App\DTOs\ExamSession\SaveAnswerDTO;
use App\DTOs\ExamSession\StartExamDTO;
use App\Models\ExamAnswer;
use App\Models\ExamPackage;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\Question;
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

    // ──────────────────────────────────────────────────────────────────────
    // WEIGHTED / NAB SCORING
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Calculate the per-unit weighted result for a completed session.
     *
     * Uses the JSON snapshot stored in `exam_packages.unit_scoring_configs`
     * so the scoring rules are frozen at the moment of package configuration
     * and never hit the `question_unit_indicators` table at evaluation time.
     *
     * @return array<int, array{
     *     question_unit_id: int,
     *     unit_name: string,
     *     total_score: int,
     *     achieved_indicator: string|null,
     *     is_passing: bool,
     * }>
     */
    public function calculateWeightedResult(ExamSession $session): array
    {
        // 1. Resolve the ExamPackage via participant
        $participant = $session->examParticipant;
        /** @var ExamPackage $package */
        $package = ExamPackage::findOrFail($participant->exam_package_id);

        $configs = $package->unit_scoring_configs ?? [];

        if (empty($configs)) {
            return [];
        }

        // 2. Sum scores per question_unit_id — single query, no N+1
        $scoresByUnit = ExamAnswer::where('exam_session_id', $session->id)
            ->join('questions', 'questions.id', '=', 'exam_answers.question_id')
            ->selectRaw('questions.question_unit_id, SUM(exam_answers.score) as unit_total')
            ->groupBy('questions.question_unit_id')
            ->pluck('unit_total', 'question_unit_id')   // [unit_id => total]
            ->toArray();

        // 3. Walk through the JSON config and determine indicator + pass/fail
        $results = [];

        foreach ($configs as $unitConfig) {
            $unitId     = (int) ($unitConfig['question_unit_id'] ?? 0);
            $unitName   = $unitConfig['unit_name'] ?? '';
            $indicators = $unitConfig['indicators'] ?? [];
            $unitScore  = (int) ($scoresByUnit[$unitId] ?? 0);

            $achievedIndicator = null;
            $isPassing         = false;

            foreach ($indicators as $indicator) {
                $min = (int) ($indicator['min_score'] ?? 0);
                $max = (int) ($indicator['max_score'] ?? 0);

                if ($unitScore >= $min && $unitScore <= $max) {
                    $achievedIndicator = $indicator['name'] ?? null;
                    $isPassing         = (bool) ($indicator['is_passing'] ?? false);
                    break; // first match wins (ranges should be non-overlapping)
                }
            }

            $results[] = [
                'question_unit_id'   => $unitId,
                'unit_name'          => $unitName,
                'total_score'        => $unitScore,
                'achieved_indicator' => $achievedIndicator,
                'is_passing'         => $isPassing,
            ];
        }

        return $results;
    }
}
