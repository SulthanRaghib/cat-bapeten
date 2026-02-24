<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'evaluation_method',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function questionUnits(): HasMany
    {
        return $this->hasMany(QuestionUnit::class);
    }

    public function examPackages(): HasMany
    {
        return $this->hasMany(ExamPackage::class);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /** Check if this type uses weighted scoring (structural). */
    public function isWeighted(): bool
    {
        return $this->evaluation_method === 'weighted';
    }

    /** Check if this type uses correct/wrong scoring (technical). */
    public function isCorrectWrong(): bool
    {
        return $this->evaluation_method === 'correct_wrong';
    }
}
