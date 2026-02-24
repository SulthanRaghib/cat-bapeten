<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionSubUnit extends Model
{
    protected $fillable = [
        'question_unit_id',
        'name',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function questionUnit(): BelongsTo
    {
        return $this->belongsTo(QuestionUnit::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
