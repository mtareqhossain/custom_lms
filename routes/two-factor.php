<?php

use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

// Guest-only: a user mid-2FA-challenge is NOT yet authenticated, so these use
// the "guest" middleware alias just like the login routes do.
Route::middleware('guest')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'show'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor-challenge/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend');
});
