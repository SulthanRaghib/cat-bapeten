<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamActivityLog extends Model
{
    protected $fillable = [
        'exam_session_id',
        'action',
        'message',
        'severity',
    ];

    public function examSession(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }
}
