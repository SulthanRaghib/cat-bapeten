<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DTOs\ExamSession\FinishExamDTO;
use App\Models\ExamSession;
use App\Services\ExamSessionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ForceCloseExpiredExams — Server-Side Sweeper for Orphaned Sessions.
 *
 * Runs every minute via the scheduler.  Finds `ongoing` exam sessions
 * whose allowed time (duration + 1-minute grace) has elapsed and
 * force-closes them through the canonical ExamSessionService::finish()
 * pipeline so scoring & participant deactivation happen correctly.
 *
 * Why this exists:
 *   When a participant closes their browser mid-exam the frontend JS
 *   timer dies and the server never receives an auto-submit request.
 *   Without this sweeper, those sessions stay `ongoing` forever.
 */
class ForceCloseExpiredExams extends Command
{
    protected $signature   = 'app:force-close-expired-exams';
    protected $description = 'Force-close ongoing exam sessions that have exceeded their allowed duration.';

    public function handle(ExamSessionService $service): int
    {
        $this->info('[Sweeper] Scanning for expired ongoing exam sessions…');

        // ── Fetch all ongoing sessions with eager-loaded relationships ────
        // examPackage is a HasOneThrough via examParticipant, so we must
        // eager-load the intermediate model as well to prevent N+1.
        $ongoingSessions = ExamSession::where('status', 'ongoing')
            ->with(['examParticipant', 'examPackage'])
            ->get();

        if ($ongoingSessions->isEmpty()) {
            $this->info('[Sweeper] No ongoing sessions found. All clear.');
            return self::SUCCESS;
        }

        $this->info("[Sweeper] Found {$ongoingSessions->count()} ongoing session(s). Checking expiry…");

        $closedCount  = 0;
        $failedCount  = 0;

        foreach ($ongoingSessions as $session) {
            /** @var ExamSession $session */
            $examPackage = $session->examPackage;

            // Safety: skip if the relationship couldn't be resolved.
            if (! $examPackage || ! $session->started_at) {
                Log::warning('[Sweeper] Skipping session — missing examPackage or started_at.', [
                    'session_id' => $session->id,
                ]);
                continue;
            }

            // ── Calculate expiry: started_at + duration + 1 min grace ────
            $expiresAt = $session->started_at
                ->copy()
                ->addMinutes((int) $examPackage->duration_minutes)
                ->addMinute(); // 1-minute grace to avoid racing frontend auto-submit

            if (now()->lte($expiresAt)) {
                // Session still within allowed time — skip.
                continue;
            }

            // ── Force-close this session inside an isolated transaction ───
            try {
                DB::transaction(function () use ($session, $service, $expiresAt): void {
                    $service->finish(new FinishExamDTO(
                        examSessionId:     $session->id,
                        examParticipantId: $session->exam_participant_id,
                        finishedAt:        $expiresAt, // Use the calculated expiry, not now()
                    ));
                });

                $closedCount++;
                $this->line("  ✓ Closed session #{$session->id} (expired at {$expiresAt})");

                Log::info('[Sweeper] Force-closed expired session.', [
                    'session_id'    => $session->id,
                    'participant_id' => $session->exam_participant_id,
                    'started_at'    => $session->started_at->toDateTimeString(),
                    'expired_at'    => $expiresAt->toDateTimeString(),
                ]);
            } catch (\Throwable $e) {
                $failedCount++;
                $this->error("  ✗ Failed to close session #{$session->id}: {$e->getMessage()}");

                Log::error('[Sweeper] Failed to force-close session.', [
                    'session_id' => $session->id,
                    'error'      => $e->getMessage(),
                    'trace'      => $e->getTraceAsString(),
                ]);

                // Continue to the next session — never let one failure crash the batch.
                continue;
            }
        }

        $this->info("[Sweeper] Done. Closed: {$closedCount}, Failed: {$failedCount}.");

        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
