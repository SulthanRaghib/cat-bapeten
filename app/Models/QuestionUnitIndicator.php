<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionUnitIndicator extends Model
{
    protected $fillable = [
        'question_unit_id',
        'name',
        'min_score',
        'max_score',
        'is_passing',
        'sort_order',
    ];

    protected $casts = [
        'min_score'  => 'integer',
        'max_score'  => 'integer',
        'is_passing' => 'boolean',
        'sort_order' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function questionUnit(): BelongsTo
    {
        return $this->belongsTo(QuestionUnit::class);
    }
}
