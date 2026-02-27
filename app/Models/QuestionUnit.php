<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionUnit extends Model
{
    protected $fillable = [
        'exam_type_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function subUnits(): HasMany
    {
        return $this->hasMany(QuestionSubUnit::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Default/template indicator levels for this unit.
     * Used as the source of truth when syncing into ExamPackage JSON snapshots.
     */
    public function indicators(): HasMany
    {
        return $this->hasMany(QuestionUnitIndicator::class)->orderBy('sort_order');
    }
}
