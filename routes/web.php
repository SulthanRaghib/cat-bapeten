<?php

use Illuminate\Support\Facades\Route;

// Alias bawaan Laravel `route('login')` -> arahkan ke login Filament
Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');


Route::get('/ujian', \App\Livewire\Exam\ExamPage::class)
    ->middleware('auth')
    ->name('exam.index');

// Admin: upload gambar untuk bank soal (RichEditor image insert widget)
Route::post('/admin/question-image-upload', [\App\Http\Controllers\QuestionImageUploadController::class, 'store'])
    ->middleware('auth')
    ->name('question.image.upload');

Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Admin: unduh BAP (Berita Acara Pelaksanaan) — token dari cache one-time
Route::get('/admin/bap/download', [\App\Http\Controllers\BapController::class, 'download'])
    ->middleware('auth')
    ->name('bap.download');

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
