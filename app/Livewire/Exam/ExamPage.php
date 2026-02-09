<?php

namespace App\Livewire\Exam;

use App\Models\ExamAnswer;
use App\Models\ExamPackage;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\Question;
use Carbon\Carbon;
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

    // Workflow Steps
    public $step = 'verification'; // verification, rules, exam
    public $cameraValid = false;
    public $rulesAgreed = false;

    // UI State
    public $showConfirmFinish = false;

    public $examTitle = 'Ujian CAT BAPETEN';
    public $candidateName;
    public $candidateIdentifier;

    // Timer properties
    public $durationMinutes = 0;
    public $startedAt;
    public $endTime;

    // Results State
    public $showResults = false;
    public $resultStats = [];

    protected $listeners = ['refreshMathJax' => '$refresh', 'timeExpired' => 'handleTimeExpiry'];

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
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            \Filament\Notifications\Notification::make()
                ->title('Akses Ditolak')
                ->body('Akses ujian tidak ditemukan atau akun Anda tidak aktif.')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.auth.login');
        }

        // Get duration from ExamPackage early to validate time
        $package = ExamPackage::find($participant->exam_package_id);
        if (!$package) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            \Filament\Notifications\Notification::make()
                ->title('Error Sistem')
                ->body('Paket ujian tidak ditemukan. Hubungi administrator.')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.auth.login');
        }
        if (! $package->is_active) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            \Filament\Notifications\Notification::make()
                ->title('Paket Ujian Ditutup')
                ->body('Paket ujian sedang dinonaktifkan oleh panitia. Silakan hubungi panitia untuk informasi lebih lanjut.')
                ->warning()
                ->send();

            return redirect()->route('filament.admin.auth.login');
        }
        $this->durationMinutes = $package->duration_minutes ?? 60;
        $this->examTitle = $package->title ?? $this->examTitle;

        // 2. Find Exam Session
        $session = ExamSession::where('exam_participant_id', $participant->id)
            ->latest()
            ->first();

        // LOGIC REVISION: Check Status & Time Expiry

        // Case A: User finished manually previously
        if ($session && $session->status === 'completed') {
            $this->examSessionId = $session->id;
            $this->questionIds = $session->answers_meta ?? [];
            $this->loadResults();
            $this->showResults = true;
            return;
        }

        // Case B: Session is ongoing
        if ($session && $session->status === 'ongoing') {
            $startedAt = $session->started_at;
            $expirationTime = $startedAt->copy()->addMinutes($this->durationMinutes);

            // Check if now is past expiration time
            if (now()->greaterThan($expirationTime)) {
                $this->examSessionId = $session->id;
                $this->endTime = $expirationTime->toIso8601String();
                $this->questionIds = $session->answers_meta ?? [];
                $this->handleTimeExpiry();
                return;
            }

            // Session is valid, resume exam immediately
            $this->step = 'exam';
            $this->initializeExamState($session, $user);
            return;
        }

        // Case C: New Session (or previous terminated)
        // Do NOT create session yet. Go to verification step.
        $this->step = 'verification';
        $this->candidateName = $user->name;
        $this->candidateIdentifier = $user->nip;
    }

    public function verifyCameraSuccess()
    {
        $this->cameraValid = true;
        $this->step = 'rules';
    }

    public function startExam()
    {
        if (!$this->rulesAgreed) {
            \Filament\Notifications\Notification::make()
                ->title('Perhatian')
                ->body('Anda harus menyetujui peraturan ujian.')
                ->warning()
                ->send();
            return;
        }

        $user = Auth::user();

        // Re-fetch participant for safety
        $participantId = session('exam_participant_id');
        if ($participantId) {
            $participant = ExamParticipant::find($participantId);
        } else {
            $participant = ExamParticipant::where('user_id', $user->id)
                ->where('is_active', true)
                ->latest()
                ->first();
        }

        if (!$participant) return;

        $participant->loadMissing('examPackage');

        if (! $participant->examPackage || ! $participant->examPackage->is_active) {
            \Filament\Notifications\Notification::make()
                ->title('Paket Ujian Ditutup')
                ->body('Paket ujian ini saat ini dinonaktifkan. Silakan hubungi panitia.')
                ->warning()
                ->send();

            $this->step = 'verification';

            return;
        }

        $session = ExamSession::create([
            'exam_participant_id' => $participant->id,
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        $this->step = 'exam';
        $this->initializeExamState($session, $user);

        // Dispatch event to start timer on frontend
        // Using named parameter for clarity
        $this->dispatch('exam-started', endTime: $this->endTime);
    }

    protected function initializeExamState($session, $user)
    {
        $this->examSessionId = $session->id;
        $this->questionIds = $session->answers_meta ?? [];
        $this->totalQuestions = count($this->questionIds);
        $this->startedAt = $session->started_at;

        $this->candidateName = $user->name;
        $this->candidateIdentifier = $user->nip;

        // Calculate end time (started_at + duration)
        $this->endTime = $session->started_at->copy()->addMinutes($this->durationMinutes)->toIso8601String();

        // Restore question index from session (persist across refresh)
        $this->currentQuestionIndex = session("exam_question_index_{$this->examSessionId}", 0);

        // Load existing answer for current question
        if (!empty($this->questionIds)) {
            $this->loadCurrentAnswer();
        }
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
        if ($this->showResults || ! $this->examSessionId) {
            return;
        }

        if (! $this->ensureSessionIsActive()) {
            return;
        }

        // Validation: Verify if time is strictly up
        if ($this->hasTimeExpired()) {
            // Trigger finish logic immediately
            $this->handleTimeExpiry();
            // Return early to prevent saving
            return;
        }

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
        if (! $this->ensureSessionIsActive()) {
            return;
        }

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
        if (! $this->ensureSessionIsActive()) {
            return;
        }

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
        if (! $this->ensureSessionIsActive()) {
            return;
        }

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
        if (! $this->ensureSessionIsActive()) {
            return;
        }

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

    protected function hasTimeExpired(): bool
    {
        if (!$this->endTime) return false;

        // Strict check: current time > end time
        // We add a tiny buffer (e.g., 5 seconds) for network latency.
        return now()->greaterThan(Carbon::parse($this->endTime)->addSeconds(5));
    }

    public function handleTimeExpiry()
    {
        // Mark session as completed
        if ($this->examSessionId) {
            $session = ExamSession::find($this->examSessionId);
            if ($session && $session->status === 'ongoing') {
                $finishedAt = $this->endTime
                    ? Carbon::parse($this->endTime)
                    : now();

                if ($finishedAt->isFuture()) {
                    $finishedAt = now();
                }

                $totalScore = (int) $session->answers()->sum('score');

                $session->forceFill([
                    'status' => 'completed',
                    'finished_at' => $finishedAt,
                    'total_score' => $totalScore,
                ])->save();

                // Setelah ujian selesai (otomatis karena waktu habis), nonaktifkan akses token peserta
                if ($session->examParticipant) {
                    $session->examParticipant->update(['is_active' => false]);
                }
            }
        }

        $this->loadResults();
        $this->showResults = true;
    }

    public function confirmFinish()
    {
        $this->showConfirmFinish = true;
    }

    public function cancelFinish()
    {
        $this->showConfirmFinish = false;
    }

    public function submitFinish()
    {
        if (! $this->ensureSessionIsActive()) {
            return;
        }

        // Mark session as completed
        if ($this->examSessionId) {
            $session = ExamSession::find($this->examSessionId);
            if ($session && $session->status === 'ongoing') {
                $totalScore = (int) $session->answers()->sum('score');

                $session->forceFill([
                    'status' => 'completed',
                    'finished_at' => now(), // Manual finish uses current time
                    'total_score' => $totalScore,
                ])->save();

                // Setelah peserta menekan tombol selesai, blokir akses ujian berikutnya
                if ($session->examParticipant) {
                    $session->examParticipant->update(['is_active' => false]);
                }
            }
        }

        $this->showConfirmFinish = false;
        $this->loadResults();
        $this->showResults = true;
    }

    protected function loadResults()
    {
        if (!$this->examSessionId) return;

        $answers = ExamAnswer::where('exam_session_id', $this->examSessionId)->get();

        $totalQuestions = count($this->questionIds);
        $answeredCount = $answers->count();
        // Assuming 'score' is already calculated per answer (0 or 1/weight)
        // If simply 1 for correct, 0 for wrong:
        $totalScore = $answers->sum('score');

        // Count correct answers (assuming score > 0 is correct)
        $correctCount = $answers->where('score', '>', 0)->count();
        $wrongCount = $answeredCount - $correctCount;

        $this->resultStats = [
            'total_questions' => $totalQuestions,
            'answered' => $answeredCount,
            'unanswered' => max($totalQuestions - $answeredCount, 0),
            'correct' => $correctCount,
            'wrong' => $wrongCount, // Includes wrong answered questions
            'total_score' => $totalScore,
        ];

        if ($session = ExamSession::find($this->examSessionId)) {
            if ((int) $session->total_score !== (int) $totalScore) {
                $session->forceFill(['total_score' => (int) $totalScore])->save();
            }
        }
    }

    public function monitorSessionStatus(): void
    {
        if ($this->showResults || !$this->examSessionId) {
            return;
        }

        $session = ExamSession::find($this->examSessionId);

        if (!$session || $session->status !== 'ongoing') {
            $this->finalizeExternallyCompletedSession($session);
            return;
        }

        $session->loadMissing('examParticipant.examPackage');

        if (! $session->examParticipant?->examPackage?->is_active) {
            $this->finalizeExternallyCompletedSession($session, 'Paket ujian sudah ditutup oleh panitia.');
        }
    }

    protected function ensureSessionIsActive(): bool
    {
        if ($this->showResults || !$this->examSessionId) {
            return false;
        }

        $session = ExamSession::find($this->examSessionId);

        if ($session && $session->status === 'ongoing') {
            $session->loadMissing('examParticipant.examPackage');

            if (! $session->examParticipant?->examPackage?->is_active) {
                $this->finalizeExternallyCompletedSession($session, 'Paket ujian sudah ditutup oleh panitia.');

                return false;
            }

            return true;
        }

        $this->finalizeExternallyCompletedSession($session);

        return false;
    }

    protected function finalizeExternallyCompletedSession(?ExamSession $session, string $message = 'Sesi ujian Anda telah diakhiri oleh pengawas.'): void
    {
        if ($this->showResults) {
            return;
        }

        if ($session) {
            $this->endTime = optional($session->finished_at)->toIso8601String() ?? now()->toIso8601String();

            if ($session->total_score === null) {
                $session->forceFill([
                    'total_score' => (int) $session->answers()->sum('score'),
                ])->save();
            }

            if ($session->examParticipant && $session->examParticipant->is_active) {
                $session->examParticipant->update(['is_active' => false]);
            }
        } else {
            $this->endTime = now()->toIso8601String();
        }

        $this->showConfirmFinish = false;
        $this->loadResults();
        $this->showResults = true;

        $this->dispatch('exam-stopped', endTime: $this->endTime);

        \Filament\Notifications\Notification::make()
            ->title('Ujian dihentikan')
            ->body($message)
            ->warning()
            ->send();
    }

    public function finishAndLogout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        \Filament\Notifications\Notification::make()
            ->title('Ujian Selesai')
            ->body('Terima kasih telah mengikuti ujian.')
            ->success()
            ->send();

        return redirect()->route('filament.admin.auth.login');
    }
}
