<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/lms.php';
// require __DIR__.'/checkout.php';
require __DIR__.'/business.php';
require __DIR__.'/phase5.php';
require __DIR__.'/phase6.php';
require __DIR__.'/legal.php';
require __DIR__.'/two-factor.php';
require __DIR__.'/chatbot.php';
require __DIR__.'/quiz.php';
