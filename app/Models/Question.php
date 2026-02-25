<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    protected $fillable = [
        'exam_type_id',
        'question_unit_id',
        'question_sub_unit_id',
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

    // ── UTF-8 sanitization ────────────────────────────────────────────────────
    // Word-pasted content embeds Windows-1252 bytes that break json_encode().
    // Two layers of defence:
    //   1. retrieved event  — sanitizes attributes immediately after DB hydration
    //   2. toArray() override — final safety net before any JSON serialization

    protected static function boot(): void
    {
        parent::boot();

        static::retrieved(function (self $model): void {
            // Sanitize plain-text / HTML string columns
            foreach (['question_text', 'explanation', 'competence_area', 'competence_sub_area', 'unit', 'sub_unit'] as $col) {
                $model->{$col} = self::sanitizeUtf8($model->{$col});
            }

            // Sanitize every string inside the JSON-cast arrays
            if (is_array($model->options)) {
                $model->options = self::sanitizeArrayStrings($model->options);
            }
            if (is_array($model->scoring_config)) {
                $model->scoring_config = self::sanitizeArrayStrings($model->scoring_config);
            }

            // Reset dirty state so the model still appears "clean"
            $model->syncOriginal();
        });
    }

    /**
     * Final safety net: sanitize every string value before JSON serialization.
     * Covers Livewire dehydration, Filament table data, API responses, etc.
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        array_walk_recursive($array, function (&$v): void {
            if (is_string($v) && !mb_check_encoding($v, 'UTF-8')) {
                $v = self::sanitizeUtf8($v);
            }
        });
        return $array;
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    /**
     * Recursively sanitize all string values in an array.
     */
    private static function sanitizeArrayStrings(array $data): array
    {
        array_walk_recursive($data, function (&$v): void {
            if (is_string($v)) {
                $v = self::sanitizeUtf8($v);
            }
        });
        return $data;
    }

    /**
     * Convert non-UTF-8 bytes to valid UTF-8 (Windows-1252 fallback).
     */
    private static function sanitizeUtf8(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }
        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function questionUnit(): BelongsTo
    {
        return $this->belongsTo(QuestionUnit::class);
    }

    public function questionSubUnit(): BelongsTo
    {
        return $this->belongsTo(QuestionSubUnit::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function examPackages(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ExamPackage::class, 'exam_package_question')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    /**
     * Generate a normalized scoring_config from the question options.
     * Returns array with structure: ['list'=>[{'kode','skor'}, ...], 'bobot'=>['A'=>5,...], 'correct' => 'D']
     */
    public function generateScoringConfigFromOptions(): array
    {
        $options = $this->options ?? [];
        $list = [];
        $map = [];
        $correct = null;

        $i = 0;
        foreach ($options as $key => $opt) {
            // Determine kode: prefer letter A,B,C... for indexed lists
            if (is_numeric($key)) {
                $kode = chr(65 + (int) $key);
            } else {
                $kode = (string) $key;
            }

            // Structural: explicit score
            $skor = null;
            if (is_array($opt) && array_key_exists('score', $opt)) {
                $skor = (int) $opt['score'];
            }

            // Technical: mark correct via 'is_correct' flag
            if (is_array($opt) && array_key_exists('is_correct', $opt) && $opt['is_correct']) {
                $skor = $skor ?? 5; // default technical correct weight
                $correct = $kode;
            }

            $skor = $skor ?? 0;

            $list[] = ['kode' => $kode, 'skor' => $skor];
            $map[$kode] = $skor;

            $i++;
        }

        $result = ['list' => $list, 'bobot' => $map];
        if ($correct) {
            $result['correct'] = $correct;
            $result['skor'] = $map[$correct] ?? 5;
        }

        return $result;
    }

    /**
     * Backfill scoring_config for all questions that currently have an empty config.
     * Returns number of questions updated.
     */
    public static function backfillScoringConfigs(): int
    {
        $updated = 0;
        $questions = self::all();
        foreach ($questions as $q) {
            $current = $q->scoring_config ?? [];
            $isEmpty = empty($current) || (is_array($current) && count($current) === 0);
            if ($isEmpty) {
                $q->scoring_config = $q->generateScoringConfigFromOptions();
                $q->saveQuietly();
                $updated++;
            }
        }

        return $updated;
    }
}
