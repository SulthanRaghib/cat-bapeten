<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class ExamParticipant extends Pivot
{
    protected $table = 'exam_participants';
    /** Explicitly set table name just in case */

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ExamParticipant $participant) {
            if (empty($participant->token)) {
                $participant->token = strtoupper(Str::random(6));
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user (participant).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exam package.
     */
    public function examPackage(): BelongsTo
    {
        return $this->belongsTo(ExamPackage::class);
    }

    /**
     * Get all exam sessions for this participant.
     */
    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    /**
     * Get the latest/active exam session.
     */
    public function activeSession(): ?ExamSession
    {
        return $this->examSessions()
            ->where('status', 'ongoing')
            ->latest()
            ->first();
    }
}
