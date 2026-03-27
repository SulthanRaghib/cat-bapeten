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
        $session = $this->examSessions()->latest()->first();

        // Jika sudah pernah ujian, prioritaskan status sesi terbaru
        if ($session) {
            return match ($session->status) {
                'completed'          => __('Completed'),
                'ongoing'            => __('In Progress'),
                'awaiting_interview' => __('Awaiting Selection'),
                'terminated'         => __('Terminated'),
                default              => ucfirst($session->status),
            };
        }

        // Belum punya sesi sama sekali
        if (!$this->is_active) {
            // Nonaktif tanpa riwayat ujian
            return __('Inactive');
        }

        if (!$session) {
            return __('Not Started');
        }
        return __('Not Started');
    }

    /**
     * Get the raw status key (untranslated) for matching purposes
     */
    public function getRawStatusKeyAttribute(): string
    {
        $session = $this->examSessions()->latest()->first();

        if ($session) {
            return match ($session->status) {
                'completed'          => 'Completed',
                'ongoing'            => 'In Progress',
                'awaiting_interview' => 'Awaiting Selection',
                'terminated'         => 'Terminated',
                default              => ucfirst($session->status),
            };
        }

        if (!$this->is_active) {
            return 'Inactive';
        }

        return 'Not Started';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->raw_status_key) {
            'Inactive'           => 'danger',
            'Not Started'        => 'gray',
            'In Progress'        => 'warning',
            'Awaiting Selection' => 'info',
            'Completed'          => 'success',
            'Terminated'         => 'danger',
            default              => 'gray',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->raw_status_key) {
            'Inactive'           => 'heroicon-m-x-circle',
            'Not Started'        => 'heroicon-m-clock',
            'In Progress'        => 'heroicon-m-play-circle',
            'Awaiting Selection' => 'heroicon-m-clipboard-document-check',
            'Completed'          => 'heroicon-m-check-badge',
            'Terminated'         => 'heroicon-m-no-symbol',
            default              => 'heroicon-m-question-mark-circle',
        };
    }
}
