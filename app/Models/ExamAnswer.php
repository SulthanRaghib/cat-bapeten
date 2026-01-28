<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    protected $fillable = [
        'exam_session_id',
        'question_id',
        'answer',
        'score',
        'is_doubtful',
    ];

    protected $casts = [
        'score' => 'integer',
        'is_doubtful' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the exam session that owns this answer.
     */
    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    /**
     * Get the question for this answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if this answer is correct based on question's scoring_config.
     */
    public function isCorrect(): bool
    {
        $question = $this->question;

        if (!$question || !$this->answer) {
            return false;
        }

        $scoringConfig = $question->scoring_config ?? [];

        // Find the scoring for the selected answer
        foreach ($scoringConfig as $config) {
            if (isset($config['kode']) && $config['kode'] === $this->answer) {
                return ($config['skor'] ?? 0) > 0;
            }
        }

        return false;
    }

    /**
     * Calculate and set the score based on the answer.
     */
    public function calculateScore(): int
    {
        $question = $this->question;

        if (!$question || !$this->answer) {
            return 0;
        }

        $scoringConfig = $question->scoring_config ?? [];

        // Find the scoring for the selected answer
        foreach ($scoringConfig as $config) {
            if (isset($config['kode']) && $config['kode'] === $this->answer) {
                $this->score = (int) ($config['skor'] ?? 0);
                return $this->score;
            }
        }

        $this->score = 0;
        return 0;
    }
}
