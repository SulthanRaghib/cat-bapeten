<?php

declare(strict_types=1);

namespace App\Models;

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
}
