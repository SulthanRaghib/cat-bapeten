<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamPackage extends Model
{
    protected $fillable = [
        'title',
        'type',
        'passing_grade',
        'duration_minutes',
        'is_active',
        'start_time',
        'end_time',
        'unit_scoring_configs',
        'technical_scoring_config',
        'exam_type_id',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'unit_scoring_configs'    => 'array',
        'technical_scoring_config' => 'array',
        'start_time'              => 'datetime',
        'end_time'                => 'datetime',
    ];

    /**
     * Get the computed status of the exam package.
     */
    public function getComputedStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'cancelled'; // Or 'inactive'? User said "cancelled -> cancelled". Using inactive as cancelled for now or add 'status' column?
            // Wait, user said "Jika status = cancelled -> cancelled". ExamPackage doesn't have 'status' column.
            // Maybe they mean if I add one? Or rely on is_active=false?
            // "Jika status = cancelled". Assuming 'status' is a column I need to add? No, I added start/end_time.
            // I will assume is_active=false => cancelled.
        }

        $now = now();

        if ($this->start_time && $now < $this->start_time) {
            return 'scheduled';
        }

        if ($this->start_time && $this->end_time && $now >= $this->start_time && $now <= $this->end_time) {
            return 'ongoing';
        }

        if ($this->end_time && $now > $this->end_time) {
            return 'finished';
        }

        return 'scheduled'; // Default fallthrough? Or 'ongoing' if no times? User said "Jika sekarang < start_time".
        // If times are null, what? Assuming scheduled if no times set yet? or ignore.
    }


    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function participants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'exam_participants')
            ->using(ExamParticipant::class)
            // Sertakan kolom id agar pivot (ExamParticipant) tahu primary key-nya
            ->withPivot(['id', 'token', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get the exam participants (pivot records) directly as models.
     */
    public function examParticipants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamParticipant::class);
    }

    /**
     * Get all exam sessions for this package through participants.
     */
    public function examSessions(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            ExamSession::class,
            ExamParticipant::class,
            'exam_package_id', // Foreign key on ExamParticipant table...
            'exam_participant_id', // Foreign key on ExamSession table...
            'id', // Local key on ExamPackage table...
            'id'  // Local key on ExamParticipant table...
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function questions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_package_question')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
