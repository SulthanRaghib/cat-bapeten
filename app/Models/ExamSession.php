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
        'answers_meta',
    ];

    protected $casts = [
        'answers_meta' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'total_score' => 'integer',
    ];

    /**
     * Boot the model and register event hooks.
     */
    protected static function booted(): void
    {
        static::creating(function (ExamSession $session) {
            // Set started_at timestamp
            $session->started_at = now();

            // Auto-shuffle questions and store in answers_meta
            $session->answers_meta = $session->generateShuffledQuestionOrder();
        });
    }

    /**
     * Generate a shuffled array of question IDs for this exam session.
     *
     * @return array<int>
     */
    public function generateShuffledQuestionOrder(): array
    {
        // Get the ExamParticipant to find the ExamPackage
        $examParticipant = ExamParticipant::find($this->exam_participant_id);

        if (!$examParticipant) {
            return [];
        }

        // Fetch all question IDs belonging to the related ExamPackage
        $questionIds = Question::where('exam_package_id', $examParticipant->exam_package_id)
            ->pluck('id')
            ->toArray();

        // Shuffle the array randomly
        shuffle($questionIds);

        return $questionIds;
    }

    /**
     * Get the ordered questions based on answers_meta.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Question>
     */
    public function getOrderedQuestions()
    {
        $order = $this->answers_meta ?? [];

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
    {
        return $this->belongsTo(ExamParticipant::class);
    }

    /**
     * Get all answers for this exam session.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
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
