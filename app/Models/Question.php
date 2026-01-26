<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'exam_package_id',
        'question_text',
        'options',
        'scoring_config',
    ];

    protected $casts = [
        'options' => 'array',
        'scoring_config' => 'array',
    ];

    protected $attributes = [
        'options' => '[]',
        'scoring_config' => '[]',
    ];

    public function examPackage(): BelongsTo
    {
        return $this->belongsTo(ExamPackage::class);
    }
}
