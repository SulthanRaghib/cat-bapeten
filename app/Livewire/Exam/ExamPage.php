<?php

namespace App\Livewire\Exam;

use App\Models\ExamAnswer;
use App\Models\ExamPackage;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExamPage extends Component
{
    public $examSessionId;
    public $currentQuestionIndex = 0;
    public $questionIds = [];
    public $currentAnswer = '';
    public $currentDoubtful = false;
    public $totalQuestions = 0;

    public $examTitle = 'Ujian CAT BAPETEN';
    public $candidateName;
    public $candidateIdentifier;

    // Timer properties
    public $durationMinutes = 0;
    public $startedAt;
    public $endTime;

    protected $listeners = ['refreshMathJax' => '$refresh'];

    public function mount()
    {
        $user = Auth::user();

        // 1. Find active participant record
        // Priority: Use session-stored participant ID (from login with token)
        $participantId = session('exam_participant_id');

        if ($participantId) {
            $participant = ExamParticipant::where('id', $participantId)
                ->where('user_id', $user->id) // Security check
                ->where('is_active', true)
                ->first();
        } else {
            // Fallback: Get latest active participant (for admin testing)
            $participant = ExamParticipant::where('user_id', $user->id)
                ->where('is_active', true)
                ->latest()
                ->first();
        }

        if (!$participant) {
            abort(403, 'Akses ujian tidak ditemukan.');
        }

        // 2. Find or Create Exam Session
        $session = ExamSession::where('exam_participant_id', $participant->id)
            ->latest()
            ->first();

        if (!$session || $session->status !== 'ongoing') {
            // Only create if no ongoing session
            // Note: If previous was completed, this logic prevents retake unless allowed.
            // Assuming for now we create a new one if none exists or last one is done?
            // "Check if the user has an active ExamSession. If NOT, create a new one"
            if (!$session || $session->status === 'terminated') { // If completed, maybe check policy? Assuming simple logic here.
                $session = ExamSession::create([
                    'exam_participant_id' => $participant->id,
                    'status' => 'ongoing',
                ]);
            }
        }

        $this->examSessionId = $session->id;
        $this->questionIds = $session->answers_meta ?? [];
        $this->totalQuestions = count($this->questionIds);
        $this->startedAt = $session->started_at;

        // Get duration from ExamPackage
        $package = ExamPackage::find($participant->exam_package_id);
        $this->durationMinutes = $package->duration_minutes ?? 60;
        $this->examTitle = $package->title ?? $this->examTitle;

        $this->candidateName = $user->name;
        $this->candidateIdentifier = $user->nip;

        // Calculate end time (started_at + duration)
        $this->endTime = $session->started_at->copy()->addMinutes($this->durationMinutes)->toIso8601String();

        // Restore question index from session (persist across refresh)
        $this->currentQuestionIndex = session("exam_question_index_{$this->examSessionId}", 0);

        // Load existing answer for current question
        $this->loadCurrentAnswer();
    }

    public function getCurrentQuestionProperty()
    {
        if (empty($this->questionIds) || !isset($this->questionIds[$this->currentQuestionIndex])) {
            return null;
        }

        return Question::find($this->questionIds[$this->currentQuestionIndex]);
    }

    public function loadCurrentAnswer()
    {
        $questionId = $this->questionIds[$this->currentQuestionIndex] ?? null;
        if (!$questionId) return;

        $answer = ExamAnswer::where('exam_session_id', $this->examSessionId)
            ->where('question_id', $questionId)
            ->first();

        $this->currentAnswer = $answer ? $answer->answer : '';
        $this->currentDoubtful = $answer ? (bool) $answer->is_doubtful : false;
    }

    public function updatedCurrentAnswer($value)
    {
        $this->saveAnswer($value);
    }

    public function saveAnswer($option)
    {
        if (!$this->currentQuestion) return;

        $this->currentAnswer = $option;

        $answer = ExamAnswer::updateOrCreate(
            [
                'exam_session_id' => $this->examSessionId,
                'question_id' => $this->currentQuestion->id,
            ],
            [
                'answer' => $option,
                'score' => 0,
            ]
        );

        // Calculate score immediately
        $answer->calculateScore();
        $answer->save();

        $this->currentDoubtful = (bool) $answer->is_doubtful;

        // Dispatch event to re-render MathJax (answer saved, UI might update)
        $this->dispatch('answer-saved');
    }

    public function toggleDoubtful()
    {
        if (!$this->currentQuestion) {
            return;
        }

        $answer = ExamAnswer::firstOrNew([
            'exam_session_id' => $this->examSessionId,
            'question_id' => $this->currentQuestion->id,
        ]);

        if (!$answer->exists) {
            $answer->answer = $this->currentAnswer ?: null;
            $answer->score = 0;
        }

        $answer->is_doubtful = !($answer->is_doubtful ?? false);

        if ($answer->answer) {
            $answer->calculateScore();
        }

        $answer->save();

        $this->currentDoubtful = (bool) $answer->is_doubtful;

        $this->dispatch('question-flagged', $this->currentDoubtful);
    }

    public function nextQuestion()
    {
        if ($this->currentQuestionIndex < $this->totalQuestions - 1) {
            $this->currentQuestionIndex++;
            // Save to session for persistence
            session(["exam_question_index_{$this->examSessionId}" => $this->currentQuestionIndex]);
            $this->loadCurrentAnswer();
            $this->dispatch('question-changed');
        }
    }

    public function prevQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
            // Save to session for persistence
            session(["exam_question_index_{$this->examSessionId}" => $this->currentQuestionIndex]);
            $this->loadCurrentAnswer();
            $this->dispatch('question-changed');
        }
    }

    public function goToQuestion($index)
    {
        if ($index >= 0 && $index < $this->totalQuestions) {
            $this->currentQuestionIndex = $index;
            session(["exam_question_index_{$this->examSessionId}" => $this->currentQuestionIndex]);
            $this->loadCurrentAnswer();
            $this->dispatch('question-changed');
        }
    }

    public function render()
    {
        $answersMap = collect();

        if ($this->examSessionId) {
            $answersMap = ExamAnswer::where('exam_session_id', $this->examSessionId)
                ->get()
                ->keyBy('question_id');
        }

        $questionStatuses = collect($this->questionIds)->values()->map(function ($questionId, $index) use ($answersMap) {
            $answer = $answersMap[$questionId] ?? null;
            return [
                'index' => $index,
                'question_id' => $questionId,
                'number' => $index + 1,
                'answered' => $answer && $answer->answer !== null && $answer->answer !== '',
                'current' => $index === $this->currentQuestionIndex,
                'answer' => $answer ? $answer->answer : null,
                'doubtful' => $answer ? (bool) $answer->is_doubtful : false,
            ];
        })->all();

        $answeredCount = $answersMap
            ->filter(function ($answer) {
                return $answer && $answer->answer !== null && $answer->answer !== '';
            })
            ->count();
        $doubtfulCount = $answersMap
            ->filter(function ($answer) {
                return $answer && (bool) $answer->is_doubtful;
            })
            ->count();
        $unansweredCount = max($this->totalQuestions - $answeredCount, 0);

        return view('livewire.exam.exam-page', [
            'questionStatuses' => $questionStatuses,
            'answeredCount' => $answeredCount,
            'unansweredCount' => $unansweredCount,
            'doubtfulCount' => $doubtfulCount,
        ])->layout('layouts.exam', [
            'examTitle' => $this->examTitle,
            'candidateName' => $this->candidateName,
            'candidateIdentifier' => $this->candidateIdentifier,
            'endTime' => $this->endTime,
            'answeredCount' => $answeredCount,
            'totalQuestions' => $this->totalQuestions,
        ]);
    }
}
