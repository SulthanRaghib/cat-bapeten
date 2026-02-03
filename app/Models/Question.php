<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    protected $fillable = [
        'type',
        'question_text',
        'options',
        'scoring_config',
        'explanation',
        'unit',
        'sub_unit',
        'category',
        'competence_area',
        'competence_sub_area',
    ];

    protected $casts = [
        'options' => 'array',
        'scoring_config' => 'array',
    ];

    protected $attributes = [
        'options' => '[]',
        'scoring_config' => '[]',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function examPackages(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ExamPackage::class, 'exam_package_question')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
