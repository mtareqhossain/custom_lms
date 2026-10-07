<?php

use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('/quizzes/{quiz}/start', [QuizController::class, 'start'])->name('quiz.start');
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('quiz.submit');
    Route::get('/quiz-attempts/{attempt}/result', [QuizController::class, 'result'])->name('quiz.result');
});
