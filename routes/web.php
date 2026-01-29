<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/ujian', \App\Livewire\Exam\ExamPage::class)
    ->middleware('auth')
    ->name('exam.index');

Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::get('/debug-user-exam/{nip}', function ($nip) {
    $user = \App\Models\User::where('nip', $nip)->first();
    if (!$user) return "User with NIP $nip not found";

    $participant = \App\Models\ExamParticipant::where('user_id', $user->id)->where('is_active', true)->first();
    if (!$participant) return "Participant record not found for user {$user->name}";

    $package = \App\Models\ExamPackage::find($participant->exam_package_id);

    $questionsCount = \App\Models\Question::where('exam_package_id', $participant->exam_package_id)->count();

    $session = \App\Models\ExamSession::where('exam_participant_id', $participant->id)->latest()->first();
    $answersMeta = $session ? $session->answers_meta : 'No Session';

    return [
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_role' => $user->role,
        'participant_id' => $participant->id,
        'participant_token' => $participant->token,
        'package_id_in_participant' => $participant->exam_package_id,
        'package_exists' => $package ? 'Yes' : 'No',
        'questions_count_in_db' => $questionsCount,
        'session_exists' => $session ? 'Yes' : 'No',
        'answers_meta' => $answersMeta,
    ];
});
