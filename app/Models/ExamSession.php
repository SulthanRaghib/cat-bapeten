<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_participant_id',
        'status',
        'started_at',
        'finished_at',
        'total_score',
        'cbt_score',
        'interview_score',
        'stage_scores',
        'answers_meta',
    ];

    protected $casts = [
        'answers_meta'    => 'array',
        'stage_scores'    => 'array',
        'started_at'      => 'datetime',
        'finished_at'     => 'datetime',
        'total_score'     => 'float',
        'cbt_score'       => 'float',
        'interview_score' => 'float',
    ];

    /**
     * Boot the model and register event hooks.
     */
    protected static function booted(): void
    {
        static::creating(function (ExamSession $session) {
            // Set started_at timestamp only when creating
            $session->started_at = now();

            // Auto-shuffle questions and store in answers_meta
            $session->answers_meta = $session->generateShuffledQuestionOrder();
        });

        static::updating(function (ExamSession $session) {
            // Guard: never allow started_at to be changed once it is set.
            // This prevents MySQL's ON UPDATE CURRENT_TIMESTAMP (or accidental
            // code) from overwriting the real exam start time.
            if ($session->isDirty('started_at') && $session->getOriginal('started_at') !== null) {
                $session->started_at = $session->getOriginal('started_at');
            }
        });
    }

    /**
     * Generate a shuffled array of question IDs AND per-question option shuffle maps.
     *
     * Returns:
     * [
     *   'question_order' => [10, 5, 22, ...],            // shuffled question IDs
     *   'option_maps'    => ['10' => [2,0,3,1], ...],    // shuffledIndex → originalIndex per question
     * ]
     *
     * @return array{question_order: list<int>, option_maps: array<string, list<int>>}
     */
    public function generateShuffledQuestionOrder(): array
    {
        $examParticipant = ExamParticipant::find($this->exam_participant_id);

        if (!$examParticipant) {
            return ['question_order' => [], 'option_maps' => []];
        }

        $examPackage = ExamPackage::find($examParticipant->exam_package_id);

        if (!$examPackage) {
            return ['question_order' => [], 'option_maps' => []];
        }

        // Fetch all questions belonging to the package
        $questions = $examPackage->questions()->get(['questions.id', 'questions.options']);

        $questionIds = $questions->pluck('id')->unique()->values()->toArray();
        shuffle($questionIds);

        // Build per-question option shuffle map
        $optionMaps = [];
        foreach ($questions as $q) {
            $opts = $q->options;
            if (is_string($opts)) {
                $opts = json_decode($opts, true) ?: [];
            }
            if (!is_array($opts) || count($opts) < 2) {
                // No shuffle needed for 0-1 options
                continue;
            }

            $indices = range(0, count($opts) - 1);
            shuffle($indices);
            // $indices[shuffledPosition] = originalIndex
            $optionMaps[(string) $q->id] = $indices;
        }

        return [
            'question_order' => $questionIds,
            'option_maps'    => $optionMaps,
        ];
    }

    /**
     * Resolve question IDs from answers_meta, supporting both legacy (flat array)
     * and new structured format.
     *
     * @return list<int>
     */
    public function resolveQuestionIds(): array
    {
        $meta = $this->answers_meta ?? [];

        // New format: { question_order: [...], option_maps: {...} }
        if (isset($meta['question_order']) && is_array($meta['question_order'])) {
            return $meta['question_order'];
        }

        // Legacy format: flat array of IDs [10, 5, 22, ...]
        if (is_array($meta) && !empty($meta) && isset($meta[0]) && is_int($meta[0])) {
            return $meta;
        }

        return [];
    }

    /**
     * Get the option shuffle maps from answers_meta.
     *
     * @return array<string, list<int>>  questionId => [shuffledPos => originalIndex, ...]
     */
    public function resolveOptionMaps(): array
    {
        $meta = $this->answers_meta ?? [];

        if (isset($meta['option_maps']) && is_array($meta['option_maps'])) {
            return $meta['option_maps'];
        }

        return [];
    }

    /**
     * Get the ordered questions based on answers_meta.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Question>
     */
    public function getOrderedQuestions()
    {
        $order = $this->resolveQuestionIds();

        if (empty($order)) {
            return collect();
        }

        // Fetch questions and sort by the shuffled order
        $questions = Question::whereIn('id', $order)->get();

        return $questions->sortBy(function ($question) use ($order) {
            return array_search($question->id, $order);
        })->values();
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the exam participant that owns this session.
     */
    public function examParticipant(): BelongsTo
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    {
        return $this->belongsTo(ExamParticipant::class);
    }

    /**
     * Get the user via the exam participant.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            ExamParticipant::class,
            'id', // Foreign key on exam_participants table...
            'id', // Foreign key on users table...
            'exam_participant_id', // Local key on exam_sessions table...
            'user_id' // Local key on exam_participants table...
        );
    }

    /**
     * Get the exam package via the exam participant.
     */
    public function examPackage(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            ExamPackage::class,
            ExamParticipant::class,
            'id', // Foreign key on exam_participants table...
            'id', // Foreign key on exam_packages table...
            'exam_participant_id', // Local key on exam_sessions table...
            'exam_package_id' // Local key on exam_participants table...
        );
    }

    /**
     * Get all answers for this exam session.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'exam_session_id');
    }

    /**
     * Get all activity logs for this exam session.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ExamActivityLog::class, 'exam_session_id');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if the exam session is still ongoing.
     */
    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    /**
     * Check if the exam session is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Mark the exam as completed and calculate total score.
     */
    public function complete(): void
    {
        $this->status = 'completed';
        $this->finished_at = now();
        $this->total_score = $this->answers()->sum('score');
        $this->save();
    }

    /**
     * Terminate the exam session (e.g., due to violation).
     */
    public function terminate(): void
    {
        $this->status = 'terminated';
        $this->finished_at = now();
        $this->total_score = $this->answers()->sum('score');
        $this->save();
    }
}
