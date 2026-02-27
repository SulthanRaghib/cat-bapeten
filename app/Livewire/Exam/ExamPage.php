<?php

declare(strict_types=1);

namespace App\Livewire\Exam;

use App\DTOs\ExamSession\FinishExamDTO;
use App\DTOs\ExamSession\SaveAnswerDTO;
use App\DTOs\ExamSession\StartExamDTO;
use App\Models\ExamActivityLog;
use App\Models\ExamAnswer;
use App\Models\ExamPackage;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\Question;
use App\Services\ExamSessionService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class ExamPage extends Component
{
    // == Sesi & Identitas ====================================================
    public ?int    $examSessionId      = null;
    /** Di-cache agar startExam() tidak perlu membaca ulang PHP session. */
    public ?int    $examParticipantId  = null;

    // == Navigasi Soal ============================================================
    public int     $currentQuestionIndex = 0;
    public array   $questionIds          = [];
    public int     $totalQuestions       = 0;

    // == State Soal yang Sedang Ditampilkan ================================================
    public string  $currentAnswer   = '';
    public bool    $currentDoubtful = false;

    // == Data Bulk untuk JavaScript =================================================
    /** Semua soal yang diserialisasi untuk JS - dimuat sekali saat sesi dimulai/dilanjutkan. */
    public array   $questionsJson  = [];
    /** Status jawaban awal per soal, dipakai JS untuk inisialisasi tampilan. */
    public array   $initialAnswers = [];

    // == Alur Kerja (Tahap Ujian) ==============================================================
    /** Tahap aktif: verification | rules | exam | result */
    public string  $step        = 'rules';
    public bool    $cameraValid = true;
    public bool    $rulesAgreed = false;

    // == Pembantu UI ============================================================
    public bool    $showConfirmFinish = false;

    // == Informasi Meta Peserta ==================================================================
    public string  $examTitle            = 'Ujian CAT BAPETEN';
    public ?string $candidateName        = null;
    public ?string $candidateIdentifier  = null;

    // == Timer (string ISO 8601 - di-serialize Livewire sebagai JSON ke frontend) ==
    public int     $durationMinutes = 0;
    public ?string $startedAt       = null;
    public ?string $endTime         = null;

    // == Hasil Ujian ===============================================================
    public bool    $showResults = false;
    public array   $resultStats = [];

    // == Pemantauan Keamanan ===================================================
    public int     $violationCount     = 0;
    public bool    $showViolationModal = false;
    public string  $violationMessage   = '';

    // =========================================================================
    // SIKLUS HIDUP KOMPONEN
    // =========================================================================

    public function mount(): void
    {
        $user        = Auth::user();
        $participant = $this->resolveParticipant($user);

        if (! $participant) {
            $this->performLogout();
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Akses ujian tidak ditemukan atau akun Anda tidak aktif.')
                ->danger()
                ->send();
            redirect()->route('filament.admin.auth.login');
            return;
        }

        $package = ExamPackage::find($participant->exam_package_id);

        if (! $package) {
            $this->performLogout();
            Notification::make()
                ->title('Error Sistem')
                ->body('Paket ujian tidak ditemukan. Hubungi administrator.')
                ->danger()
                ->send();
            redirect()->route('filament.admin.auth.login');
            return;
        }

        if (! $package->is_active) {
            $this->performLogout();
            Notification::make()
                ->title('Paket Ujian Ditutup')
                ->body('Paket ujian sedang dinonaktifkan oleh panitia. Silakan hubungi panitia untuk informasi lebih lanjut.')
                ->warning()
                ->send();
            redirect()->route('filament.admin.auth.login');
            return;
        }

        $this->durationMinutes     = $package->duration_minutes ?? 60;
        $this->examTitle           = $package->title ?? $this->examTitle;
        $this->examParticipantId   = $participant->id;
        $this->candidateName       = $user->name;
        $this->candidateIdentifier = $user->nip;

        $this->resolveSessionStep($participant);
    }

    // =========================================================================
    // ALUR KERJA (TAHAP UJIAN)
    // =========================================================================

    public function startExam(): void
    {
        if (! $this->rulesAgreed) {
            Notification::make()
                ->title('Perhatian')
                ->body('Anda harus menyetujui peraturan ujian.')
                ->warning()
                ->send();
            return;
        }

        $participant = ExamParticipant::find($this->examParticipantId);

        if (! $participant) {
            return;
        }

        $participant->loadMissing('examPackage');

        if (! $participant->examPackage?->is_active) {
            Notification::make()
                ->title('Paket Ujian Ditutup')
                ->body('Paket ujian ini saat ini dinonaktifkan. Silakan hubungi panitia.')
                ->warning()
                ->send();
            $this->step = 'rules';
            return;
        }

        $session = app(ExamSessionService::class)->start(
            new StartExamDTO($participant->id),
        );

        // Pengaman: jika event model gagal mengisi answers_meta, generate ulang di sini.
        if (empty($session->answers_meta)) {
            $session->answers_meta = $session->generateShuffledQuestionOrder();
            $session->save();
            $session->refresh();
        }

        $this->step = 'exam';
        $this->initializeExamState($session);

        // Pengaman tambahan: initializeExamState bisa gagal diam-diam jika answers_meta belum terisi.
        if (empty($this->questionsJson)) {
            $this->initializeClientData();
        }

        $this->dispatch('exam-started', $this->endTime);
    }

    // =========================================================================
    // AKSI SAAT UJIAN BERLANGSUNG
    // =========================================================================

    public function updatedCurrentAnswer(string $value): void
    {
        $this->saveAnswer($value);
    }

    #[Renderless]
    public function saveAnswer(string $option): void
    {
        $questionId = $this->questionIds[$this->currentQuestionIndex] ?? null;

        if ($questionId) {
            $this->saveAnswerForQuestion($questionId, $option);
        }
    }

    /** Dipanggil langsung dari JS/Alpine - melewati siklus render Livewire agar lebih cepat. */
    #[Renderless]
    public function saveAnswerClient(int $questionId, string $option): void
    {
        if ($this->showResults || ! $this->examSessionId) {
            return;
        }

        if ($this->hasTimeExpired()) {
            $this->handleTimeExpiry();
            return;
        }

        $this->saveAnswerForQuestion($questionId, $option);

        // Sinkronkan state lokal agar tampilan soal yang aktif tetap konsisten.
        if ($questionId === ($this->questionIds[$this->currentQuestionIndex] ?? null)) {
            $this->currentAnswer = $option;
        }
    }

    /** Hanya toggle flag ragu-ragu - tidak mempengaruhi skor - update langsung ke model sudah cukup. */
    #[Renderless]
    public function toggleDoubtfulClient(int $questionId, bool $status): void
    {
        if ($this->showResults || ! $this->examSessionId) {
            return;
        }

        ExamAnswer::firstOrCreate(
            ['exam_session_id' => $this->examSessionId, 'question_id' => $questionId],
            ['answer' => null, 'score' => 0],
        )->update(['is_doubtful' => $status]);
    }

    public function toggleDoubtful(): void
    {
        if (! $this->ensureSessionIsActive()) {
            return;
        }

        $question = $this->currentQuestion;

        if (! $question) {
            return;
        }

        $answer = ExamAnswer::firstOrNew([
            'exam_session_id' => $this->examSessionId,
            'question_id'     => $question->id,
        ]);

        if (! $answer->exists) {
            $answer->answer = $this->currentAnswer ?: null;
            $answer->score  = 0;
        }

        $answer->is_doubtful = ! ($answer->is_doubtful ?? false);

        if ($answer->answer) {
            $answer->score = $answer->calculateScore();
        }

        $answer->save();

        $this->currentDoubtful = (bool) $answer->is_doubtful;
        $this->dispatch('question-flagged', $this->currentDoubtful);
    }

    public function nextQuestion(): void
    {
        if (! $this->ensureSessionIsActive()) {
            return;
        }

        $this->persistCurrentAnswerIfSet();

        if ($this->currentQuestionIndex < $this->totalQuestions - 1) {
            $this->currentQuestionIndex++;
            $this->persistNavigationIndex();
            $this->loadCurrentAnswer();
            $this->dispatch('question-changed');
        }
    }

    public function prevQuestion(): void
    {
        if (! $this->ensureSessionIsActive()) {
            return;
        }

        $this->persistCurrentAnswerIfSet();

        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
            $this->persistNavigationIndex();
            $this->loadCurrentAnswer();
            $this->dispatch('question-changed');
        }
    }

    public function goToQuestion(int $index): void
    {
        if (! $this->ensureSessionIsActive()) {
            return;
        }

        $this->persistCurrentAnswerIfSet();

        if ($index >= 0 && $index < $this->totalQuestions) {
            $this->currentQuestionIndex = $index;
            $this->persistNavigationIndex();
            $this->loadCurrentAnswer();
            $this->dispatch('question-changed');
        }
    }

    // =========================================================================
    // SELESAI / SUBMIT UJIAN
    // =========================================================================

    public function confirmFinish(): void
    {
        $this->showConfirmFinish = true;
    }

    public function cancelFinish(): void
    {
        $this->showConfirmFinish = false;
    }

    public function submitFinish(): void
    {
        if (! $this->ensureSessionIsActive()) {
            return;
        }

        if ($this->examSessionId) {
            $session = ExamSession::find($this->examSessionId);
            if ($session && $session->status === 'ongoing') {
                $this->completeExamSession($session, now());
            }
        }

        $this->showConfirmFinish = false;
        $this->dispatch('exam-finished');
        $this->loadResults();
        $this->step = 'result';
    }

    public function finishAndLogout(): void
    {
        $this->performLogout();

        Notification::make()
            ->title('Ujian Selesai')
            ->body('Terima kasih telah mengikuti ujian.')
            ->success()
            ->send();

        redirect()->route('filament.admin.auth.login');
    }

    // =========================================================================
    // TIMER & PEMANTAUAN SESI
    // =========================================================================

    /** Listener event Livewire v3 - menggantikan array $listeners yang sudah deprecated. */
    #[On('timeExpired')]
    public function handleTimeExpiry(): void
    {
        if (! $this->examSessionId) {
            return;
        }

        $session = ExamSession::find($this->examSessionId);

        if ($session && $session->status === 'ongoing') {
            $finishedAt = $this->endTime ? Carbon::parse($this->endTime) : now();

            if ($finishedAt->isFuture()) {
                $finishedAt = now();
            }

            $this->completeExamSession($session, $finishedAt);
        }

        $this->dispatch('exam-finished');
        $this->loadResults();
        $this->step = 'result';
    }

    public function monitorSessionStatus(): void
    {
        if ($this->showResults || ! $this->examSessionId) {
            return;
        }

        $session = ExamSession::find($this->examSessionId);

        if (! $session) {
            // Sesi dihapus dari luar sistem, misalnya oleh admin.
            $this->finalizeExternallyCompletedSession(null, 'Sesi ujian tidak ditemukan. Silakan hubungi administrator.');
            return;
        }

        if ($session->status !== 'ongoing') {
            $this->finalizeExternallyCompletedSession($session);
            return;
        }

        $session->loadMissing('examParticipant.examPackage');

        if (! $session->examParticipant?->examPackage?->is_active) {
            $this->finalizeExternallyCompletedSession($session, 'Paket ujian sudah ditutup oleh panitia.');
        }
    }

    public function logActivity(string $action, ?string $message = null, string $severity = 'warning'): void
    {
        if (! $this->examSessionId) {
            return;
        }

        $messageMap = [
            'tab_switch'    => 'Peserta berpindah tab atau meminimalkan browser.',
            'window_blur'   => 'Peserta mengklik di luar jendela ujian.',
            'copy_attempt'  => 'Percobaan menyalin teks soal (Copy).',
            'paste_attempt' => 'Percobaan menempel teks (Paste).',
            'right_click'   => 'Percobaan klik kanan (Context Menu).',
        ];

        $logMessage = $message ?? ($messageMap[$action] ?? 'Aktivitas mencurigakan terdeteksi.');

        if (in_array($severity, ['warning', 'danger', 'critical'], true)) {
            $this->violationCount++;
            $this->violationMessage   = $logMessage;
            $this->showViolationModal = true;
        }

        ExamActivityLog::create([
            'exam_session_id' => $this->examSessionId,
            'action'          => $action,
            'message'         => $logMessage,
            'severity'        => $severity,
        ]);
    }

    public function closeViolationModal(): void
    {
        $this->showViolationModal = false;
    }

    // =========================================================================
    // COMPUTED PROPERTIES (PROPERTI KALKULASI)
    // =========================================================================

    public function getCurrentQuestionProperty(): ?Question
    {
        if (empty($this->questionIds) || ! isset($this->questionIds[$this->currentQuestionIndex])) {
            return null;
        }

        return Question::find($this->questionIds[$this->currentQuestionIndex]);
    }

    // =========================================================================
    // RENDER
    // =========================================================================

    public function render(): mixed
    {
        [$questionStatuses, $answeredCount, $doubtfulCount] = $this->buildQuestionStatuses();

        return view('livewire.exam.exam-page', [
            'questionStatuses' => $questionStatuses,
            'answeredCount'    => $answeredCount,
            'unansweredCount'  => max($this->totalQuestions - $answeredCount, 0),
            'doubtfulCount'    => $doubtfulCount,
        ])->layout('layouts.exam', [
            'examTitle'           => $this->examTitle,
            'candidateName'       => $this->candidateName,
            'candidateIdentifier' => $this->candidateIdentifier,
            'endTime'             => $this->endTime,
            'answeredCount'       => $answeredCount,
            'totalQuestions'      => $this->totalQuestions,
            'hideTimer'           => $this->step === 'result',
        ]);
    }

    // =========================================================================
    // PRIVATE - PEMBANTU MOUNT
    // =========================================================================

    /**
     * Cari ExamParticipant untuk pengguna yang sedang login.
     * Prioritas: ID dari PHP session (diset saat login token) - jika tidak ada,
     * ambil record aktif terbaru (fallback untuk admin/testing).
     */
    private function resolveParticipant(mixed $user): ?ExamParticipant
    {
        $participantId = session('exam_participant_id');

        if ($participantId) {
            return ExamParticipant::where('id', $participantId)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
        }

        return ExamParticipant::where('user_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    /**
     * Tentukan tahap (step) mana yang harus ditampilkan berdasarkan status sesi peserta.
     */
    private function resolveSessionStep(ExamParticipant $participant): void
    {
        $session = ExamSession::where('exam_participant_id', $participant->id)
            ->latest()
            ->first();

        // Kasus A: peserta sudah selesai ujian secara manual sebelumnya.
        if ($session && $session->status === 'completed') {
            $this->examSessionId = $session->id;
            $this->questionIds   = $session->answers_meta ?? [];
            $this->loadResults();
            $this->step = 'result';
            return;
        }

        // Kasus B: sesi sedang berjalan - cek apakah waktu ujian sudah habis.
        if ($session && $session->status === 'ongoing') {
            $expirationTime = $session->started_at->copy()->addMinutes($this->durationMinutes);

            if (now()->greaterThan($expirationTime)) {
                $this->examSessionId = $session->id;
                $this->endTime       = $expirationTime->toIso8601String();
                $this->questionIds   = $session->answers_meta ?? [];
                $this->handleTimeExpiry();
                return;
            }

            // Sesi masih valid dan belum kedaluwarsa - lanjutkan langsung ke tampilan soal.
            $this->step = 'exam';
            $this->initializeExamState($session);
            return;
        }

        // Kasus C: belum ada sesi ujian - langsung ke Tata Tertib (lewati verifikasi).
        $this->step = 'rules';
    }

    // =========================================================================
    // PRIVATE - PEMBANTU INISIALISASI SESI UJIAN
    // =========================================================================

    /**
     * Isi semua properti Livewire yang diperlukan untuk menampilkan sesi ujian yang aktif.
     */
    private function initializeExamState(ExamSession $session): void
    {
        $user = Auth::user();

        $this->examSessionId        = $session->id;
        $this->questionIds          = $session->answers_meta ?? [];
        $this->totalQuestions       = count($this->questionIds);
        $this->startedAt            = $session->started_at->toIso8601String();
        $this->candidateName        = $user->name;
        $this->candidateIdentifier  = $user->nip;
        $this->endTime              = $session->started_at->copy()
            ->addMinutes($this->durationMinutes)
            ->toIso8601String();

        // Pulihkan nomor soal terakhir dari PHP session (bertahan meski halaman di-refresh).
        $this->currentQuestionIndex = session("exam_question_index_{$this->examSessionId}", 0);

        $this->initializeClientData();

        if (! empty($this->questionIds)) {
            $this->loadCurrentAnswer();
        }
    }

    /**
     * Isi $questionsJson dan $initialAnswers untuk layer JavaScript, hanya dengan 2 query DB.
     */
    private function initializeClientData(): void
    {
        // Pengaman: reload answers_meta jika karena alasan tertentu masih kosong.
        if (empty($this->questionIds) && $this->examSessionId) {
            $session = ExamSession::find($this->examSessionId);
            if ($session && ! empty($session->answers_meta)) {
                $this->questionIds    = $session->answers_meta;
                $this->totalQuestions = count($this->questionIds);
            }
        }

        // Query 1: ambil semua soal untuk sesi ini sekaligus.
        $questions = Question::whereIn('id', $this->questionIds)->get();

        $this->questionsJson = collect($this->questionIds)
            ->map(function (int $id) use ($questions): ?array {
                $q = $questions->firstWhere('id', $id);

                if (! $q) {
                    return null;
                }

                $options = $q->options;

                // Tangani kemungkinan JSON yang ter-encode dua kali saat disimpan ke DB.
                if (is_string($options)) {
                    $decoded = json_decode($options, true);
                    if (is_array($decoded)) {
                        $options = $decoded;
                    }
                }

                return [
                    'id'            => $q->id,
                    'question_text' => (string) $q->question_text,
                    'options'       => $options,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        // Query 2: ambil semua jawaban yang sudah tersimpan untuk sesi ini.
        $answers = ExamAnswer::where('exam_session_id', $this->examSessionId)->get();

        $this->initialAnswers = [];
        foreach ($this->questionIds as $qid) {
            $ans = $answers->firstWhere('question_id', $qid);
            $this->initialAnswers[$qid] = [
                'answer'   => $ans ? (string) $ans->answer : null,
                'doubtful' => $ans ? (bool) $ans->is_doubtful : false,
                'answered' => $ans && $ans->answer !== null && $ans->answer !== '',
            ];
        }
    }

    private function loadCurrentAnswer(): void
    {
        $questionId = $this->questionIds[$this->currentQuestionIndex] ?? null;

        if (! $questionId) {
            return;
        }

        $answer = ExamAnswer::where('exam_session_id', $this->examSessionId)
            ->where('question_id', $questionId)
            ->first();

        $this->currentAnswer   = $answer?->answer ?? '';
        $this->currentDoubtful = (bool) ($answer?->is_doubtful ?? false);
    }

    // =========================================================================
    // PRIVATE - PEMBANTU SIMPAN JAWABAN & SELESAIKAN UJIAN
    // =========================================================================

    /**
     * Serahkan penyimpanan jawaban dan kalkulasi skor ke ExamSessionService.
     * Satu sumber kebenaran - dipanggil oleh saveAnswer() maupun saveAnswerClient().
     */
    private function saveAnswerForQuestion(int $questionId, string $option): void
    {
        app(ExamSessionService::class)->saveAnswer(new SaveAnswerDTO(
            examSessionId: $this->examSessionId,
            questionId: $questionId,
            answer: $option,
            isDoubtful: false, // status ragu-ragu dikelola terpisah via toggleDoubtful*()
        ));
    }

    /**
     * Serahkan penyelesaian sesi ke ExamSessionService.
     * RuntimeException ditangkap diam-diam jika sesi sudah selesai lebih dulu.
     */
    private function completeExamSession(ExamSession $session, ?Carbon $finishedAt = null): void
    {
        try {
            app(ExamSessionService::class)->finish(new FinishExamDTO(
                examSessionId: $session->id,
                examParticipantId: $session->exam_participant_id,
                finishedAt: $finishedAt ?? now(),
            ));
        } catch (\RuntimeException) {
            // Sesi sudah selesai sebelumnya - abaikan exception.
        }
    }

    private function loadResults(): void
    {
        if (! $this->examSessionId) {
            return;
        }

        $session = ExamSession::find($this->examSessionId);

        $answers        = ExamAnswer::where('exam_session_id', $this->examSessionId)->get();
        $totalQuestions = count($this->questionIds);
        $answeredCount  = $answers->count();
        $totalScore     = $answers->sum('score');
        $correctCount   = $answers->where('score', '>', 0)->count();

        // Hitung total pelanggaran
        $violationCount = ExamActivityLog::where('exam_session_id', $this->examSessionId)->count();

        $this->resultStats = [
            'total_questions' => $totalQuestions,
            'answered'        => $answeredCount,
            'unanswered'      => max($totalQuestions - $answeredCount, 0),
            'correct'         => $correctCount,
            'wrong'           => $answeredCount - $correctCount,
            'total_score'     => $totalScore,
            'violation_count' => $violationCount,
            'start_time'      => $session?->started_at,
            'end_time'        => $session?->finished_at ?? now(),
        ];

        // Sinkronkan total_score jika ada selisih (misal jawaban dihitung ulang setelah submit).
        if ($session) {
            if ((int) $session->total_score !== (int) $totalScore) {
                $session->forceFill(['total_score' => (int) $totalScore])->save();
            }
        }
    }

    // =========================================================================
    // PRIVATE - PEMBANTU RENDER
    // =========================================================================

    /**
     * Bangun array status soal beserta jumlah terjawab dan ragu-ragu untuk render().
     * Hanya melakukan 1 query DB jika ada sesi yang aktif.
     *
     * @return array{0: list<array>, 1: int, 2: int}  [statuses, answeredCount, doubtfulCount]
     */
    private function buildQuestionStatuses(): array
    {
        $answersMap = $this->examSessionId
            ? ExamAnswer::where('exam_session_id', $this->examSessionId)
            ->get()
            ->keyBy('question_id')
            : collect();

        $questionStatuses = collect($this->questionIds)
            ->values()
            ->map(function (int $questionId, int $index) use ($answersMap): array {
                $answer = $answersMap[$questionId] ?? null;

                return [
                    'index'       => $index,
                    'question_id' => $questionId,
                    'number'      => $index + 1,
                    'answered'    => $answer && $answer->answer !== null && $answer->answer !== '',
                    'current'     => $index === $this->currentQuestionIndex,
                    'answer'      => $answer?->answer,
                    'doubtful'    => (bool) ($answer?->is_doubtful ?? false),
                ];
            })
            ->all();

        $answeredCount = $answersMap
            ->filter(fn($a) => $a && $a->answer !== null && $a->answer !== '')
            ->count();

        $doubtfulCount = $answersMap
            ->filter(fn($a) => $a && (bool) $a->is_doubtful)
            ->count();

        return [$questionStatuses, $answeredCount, $doubtfulCount];
    }

    // =========================================================================
    // PRIVATE - PENJAGA STATUS SESI
    // =========================================================================

    private function ensureSessionIsActive(): bool
    {
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

    /**
     * Dipanggil ketika sesi diakhiri oleh pihak luar (admin atau kedaluwarsa di sisi server).
     * Menangani null session dengan aman (misalnya jika record sudah dihapus dari DB).
     */
    private function finalizeExternallyCompletedSession(
        ?ExamSession $session,
        string $message = 'Sesi ujian Anda telah diakhiri oleh pengawas.',
    ): void {
        if ($this->showResults) {
            return;
        }

        if ($session) {
            $this->endTime = optional($session->finished_at)->toIso8601String()
                ?? now()->toIso8601String();

            if ($session->total_score === null) {
                $session->forceFill([
                    'total_score' => (int) $session->answers()->sum('score'),
                ])->save();
            }

            if ($session->examParticipant?->is_active) {
                $session->examParticipant->update(['is_active' => false]);
            }
        } else {
            $this->endTime = now()->toIso8601String();
        }

        $this->showConfirmFinish = false;
        $this->loadResults();
        $this->showResults = true;

        $this->dispatch('exam-stopped', endTime: $this->endTime);

        Notification::make()
            ->title('Ujian dihentikan')
            ->body($message)
            ->warning()
            ->send();
    }

    private function hasTimeExpired(): bool
    {
        if (! $this->endTime) {
            return false;
        }

        // Tambah toleransi 5 detik untuk mengakomodasi latensi jaringan sebelum dinyatakan kedaluwarsa.
        return now()->greaterThan(Carbon::parse($this->endTime)->addSeconds(5));
    }

    // =========================================================================
    // PRIVATE - PEMBANTU NAVIGASI SOAL
    // =========================================================================

    /** Simpan jawaban soal saat ini sebelum berpindah ke soal lain, jika ada jawaban yang dipilih. */
    private function persistCurrentAnswerIfSet(): void
    {
        if ($this->currentQuestion && $this->currentAnswer !== '') {
            $this->saveAnswer($this->currentAnswer);
        }
    }

    /** Simpan nomor soal aktif ke PHP session agar tidak hilang saat halaman di-refresh. */
    private function persistNavigationIndex(): void
    {
        session(["exam_question_index_{$this->examSessionId}" => $this->currentQuestionIndex]);
    }

    /** Hancurkan sesi autentikasi secara bersih (logout + invalidate + regenerate token). */
    private function performLogout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}
