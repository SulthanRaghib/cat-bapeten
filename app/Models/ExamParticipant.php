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

    // Gunakan kolom id sebagai primary key pada pivot ini
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

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
        // Secara eksplisit gunakan foreign key "exam_participant_id" dan local key "id"
        // untuk menghindari Laravel menebak foreign key yang salah (mis. exam_package_id)
        // ketika model ini dipakai sebagai Pivot.
        return $this->hasMany(ExamSession::class, 'exam_participant_id', 'id');
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

    // ==================== ATTRIBUTES ====================

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Nonaktif';
        }

        $session = $this->examSessions()->latest()->first();

        if (!$session) {
            return 'Belum Mengerjakan';
        }

        if ($session->status === 'completed') {
            return 'Selesai';
        }

        if ($session->status === 'ongoing') {
            return 'Sedang Mengerjakan';
        }

        return ucfirst($session->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_label) {
            'Nonaktif' => 'danger',
            'Belum Mengerjakan' => 'gray',
            'Sedang Mengerjakan' => 'warning',
            'Selesai' => 'success',
            default => 'info',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status_label) {
            'Nonaktif' => 'heroicon-m-x-circle',
            'Belum Mengerjakan' => 'heroicon-m-clock',
            'Sedang Mengerjakan' => 'heroicon-m-play-circle',
            'Selesai' => 'heroicon-m-check-badge',
            default => 'heroicon-m-question-mark-circle',
        };
    }
}
