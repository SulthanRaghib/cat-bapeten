<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ExamPackage\AssignParticipantDTO;
use App\DTOs\ExamPackage\CreateExamPackageDTO;
use App\Models\ExamPackage;
use App\Models\ExamParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ExamPackageService — owns all ExamPackage lifecycle logic.
 *
 * Responsibilities:
 *  - Create / update exam packages.
 *  - Assign / detach participants with automatic token generation.
 *  - Enforce business rule: a participant may only have ONE active
 *    record per package at any time.
 */
final class ExamPackageService
{
    // ──────────────────────────────────────────────────────────────────────
    // PACKAGE OPERATIONS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Create a new ExamPackage.
     *
     * @throws \Throwable
     */
    public function create(CreateExamPackageDTO $dto): ExamPackage
    {
        return DB::transaction(static function () use ($dto): ExamPackage {
            return ExamPackage::create([
                'title'            => $dto->title,
                'type'             => $dto->type,
                'passing_grade'    => $dto->passingGrade,
                'duration_minutes' => $dto->durationMinutes,
                'is_active'        => $dto->isActive,
            ]);
        });
    }

    /**
     * Update an existing ExamPackage's metadata.
     *
     * @throws \Throwable
     */
    public function update(ExamPackage $package, CreateExamPackageDTO $dto): ExamPackage
    {
        return DB::transaction(static function () use ($package, $dto): ExamPackage {
            $package->update([
                'title'            => $dto->title,
                'type'             => $dto->type,
                'passing_grade'    => $dto->passingGrade,
                'duration_minutes' => $dto->durationMinutes,
                'is_active'        => $dto->isActive,
            ]);

            return $package->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // PARTICIPANT OPERATIONS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Attach a user to a package as a participant.
     *
     * Business rule: only one active participant record per (package, user)
     * pair is allowed.  If one already exists it is returned unchanged; the
     * delivery layer should surface a validation error before calling this.
     *
     * @throws \Throwable
     */
    public function assignParticipant(AssignParticipantDTO $dto): ExamParticipant
    {
        return DB::transaction(function () use ($dto): ExamParticipant {
            // Idempotency guard — prevent duplicate attachments.
            $existing = ExamParticipant::where('exam_package_id', $dto->examPackageId)
                ->where('user_id', $dto->userId)
                ->first();

            if ($existing) {
                return $existing;
            }

            // Token generation is a BUSINESS RULE — it lives here, not in the
            // controller or the model observer.
            $token = strtoupper(Str::random(6));

            // Use sync-style insertion through the BelongsToMany so pivot
            // events (ExamParticipant::creating) still fire.
            $package = ExamPackage::findOrFail($dto->examPackageId);
            $package->participants()->attach($dto->userId, [
                'token'     => $token,
                'is_active' => $dto->isActive,
            ]);

            return ExamParticipant::where('exam_package_id', $dto->examPackageId)
                ->where('user_id', $dto->userId)
                ->latest()
                ->firstOrFail();
        });
    }

    /**
     * Detach a participant from a package.
     * All associated ExamSession / ExamAnswer records should be handled
     * by a separate cascade or a dedicated service method.
     *
     * @throws \Throwable
     */
    public function detachParticipant(int $examParticipantId): void
    {
        DB::transaction(static function () use ($examParticipantId): void {
            ExamParticipant::findOrFail($examParticipantId)->delete();
        });
    }

    /**
     * Regenerate the access token for a given participant.
     *
     * @throws \Throwable
     */
    public function regenerateToken(int $examParticipantId): ExamParticipant
    {
        return DB::transaction(static function () use ($examParticipantId): ExamParticipant {
            $participant = ExamParticipant::findOrFail($examParticipantId);
            $participant->token = strtoupper(Str::random(6));
            $participant->save();

            return $participant->fresh();
        });
    }
}
