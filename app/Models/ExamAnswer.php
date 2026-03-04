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
     *
     * This method is robust: it supports multiple scoring_config shapes (list of {kode,skor},
     * associative bobot mapping, or 'correct' key) and falls back to the original question
     * options when scoring_config is missing.
     */
    public function calculateScore(): int
    {
        $question = $this->question;

        if (!$question || $this->answer === null || $this->answer === '') {
            $this->score = 0;
            return 0;
        }

        $sc = $question->scoring_config ?? [];

        // 1) If scoring_config has a 'list' (array of {kode, skor}) use it
        if (isset($sc['list']) && is_array($sc['list'])) {
            foreach ($sc['list'] as $config) {
                if (isset($config['kode']) && (string) $config['kode'] === (string) $this->answer) {
                    $skor = (int) ($config['skor'] ?? 0);
                    // Guard for legacy data: if skor=0 but this kode IS the correct answer, return 1
                    if ($skor === 0 && isset($sc['correct']) && (string) $sc['correct'] === (string) $this->answer) {
                        $skor = 1;
                    }
                    $this->score = $skor;
                    return $this->score;
                }
            }
        }

        // 2) If scoring_config has a direct mapping ('bobot') like ['A' => 5, 'B' => 0]
        if (isset($sc['bobot']) && is_array($sc['bobot'])) {
            // Try direct key
            if (array_key_exists($this->answer, $sc['bobot'])) {
                $this->score = (int) ($sc['bobot'][$this->answer] ?? 0);
                return $this->score;
            }
            // Try numeric index to letter mapping
            if (is_numeric($this->answer)) {
                $letter = chr(65 + (int) $this->answer);
                if (array_key_exists($letter, $sc['bobot'])) {
                    $this->score = (int) ($sc['bobot'][$letter] ?? 0);
                    return $this->score;
                }
            }
        }

        // 3) If scoring_config has a 'correct' key (technical), assign default weight for correct
        if (isset($sc['correct'])) {
            if ((string) $sc['correct'] === (string) $this->answer) {
                $this->score = (int) ($sc['skor'] ?? 1); // default 1 if not provided
                return $this->score;
            }
            // also support if numeric answer and correct is letter
            if (is_numeric($this->answer)) {
                $letter = chr(65 + (int) $this->answer);
                if ((string) $sc['correct'] === $letter) {
                    $this->score = (int) ($sc['skor'] ?? 1);
                    return $this->score;
                }
            }
        }

        // 4) Fallback: inspect question->options directly
        $options = $question->options ?? [];
        $selected = null;

        // Try direct key first
        if (isset($options[$this->answer])) {
            $selected = $options[$this->answer];
        } elseif (is_numeric($this->answer) && isset($options[(int) $this->answer])) {
            $selected = $options[(int) $this->answer];
        } else {
            // As a final attempt, match by letter mapping
            $letter = null;
            if (is_numeric($this->answer)) {
                $letter = chr(65 + (int) $this->answer);
            } else {
                $letter = strtoupper((string) $this->answer);
            }
            if (isset($options[$letter])) {
                $selected = $options[$letter];
            } else {
                // Try scanning options for positional match
                $i = 0;
                foreach ($options as $opt) {
                    if ((string) $i === (string) $this->answer) {
                        $selected = $opt;
                        break;
                    }
                    $i++;
                }
            }
        }

        if ($selected !== null) {
            // Structural: use explicit 'score' if present
            if (is_array($selected) && array_key_exists('score', $selected)) {
                $this->score = (int) ($selected['score'] ?? 0);
                return $this->score;
            }

            // Technical: use 'is_correct' flag if present
            if (is_array($selected) && array_key_exists('is_correct', $selected)) {
                $this->score = $selected['is_correct'] ? 1 : 0; // 1 point for correct answer
                return $this->score;
            }
        }

        // No scoring rule found -> default 0
        $this->score = 0;
        return 0;
    }
}
