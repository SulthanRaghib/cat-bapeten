<?php

namespace App\Livewire\Exam;

use App\Models\ExamAnswer;
use App\Models\ExamPackage;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.exam')]
class ExamPage extends Component
{
    public $examSessionId;
    public $currentQuestionIndex = 0;
    public $questionIds = [];
    public $currentAnswer = '';
    public $totalQuestions = 0;

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

        // Calculate end time (started_at + duration)
        $this->endTime = $session->started_at->copy()->addMinutes($this->durationMinutes)->toIso8601String();

        // Load existing answer for first question
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
    }

    public function updatedCurrentAnswer($value)
    {
        $this->saveAnswer($value);
    }

    public function saveAnswer($option)
    {
        if (!$this->currentQuestion) return;

        $answer = ExamAnswer::updateOrCreate(
            [
                'exam_session_id' => $this->examSessionId,
                'question_id' => $this->currentQuestion->id,
            ],
            [
                'answer' => $option,
                'score' => 0, // Should calculate score immediately or later?
                // Context: "Update or Create ExamAnswer".
                // Calculating score logic is in ExamAnswer model but needs to be called.
            ]
        );

        // Calculate score immediately
        $answer->calculateScore();
        $answer->save();
    }

    public function nextQuestion()
    {
        if ($this->currentQuestionIndex < $this->totalQuestions - 1) {
            $this->currentQuestionIndex++;
            $this->loadCurrentAnswer();
            $this->dispatch('question-changed'); // Trigger MathJax re-render
        }
    }

    public function prevQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
            $this->loadCurrentAnswer();
            $this->dispatch('question-changed'); // Trigger MathJax re-render
        }
    }

    public function render()
    {
        return view('livewire.exam.exam-page');
    }
}
