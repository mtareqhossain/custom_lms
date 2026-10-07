<?php

use App\Http\Controllers\CourseChatbotController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:chatbot'])->group(function () {
    Route::post('/courses/{course}/chatbot/ask', [CourseChatbotController::class, 'ask'])->name('chatbot.ask');
    Route::get('/courses/{course}/chatbot/history', [CourseChatbotController::class, 'history'])->name('chatbot.history');
});
