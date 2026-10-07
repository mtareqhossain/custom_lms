<?php
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::prefix('instructor')->name('instructor.')->group(function () {
        Route::get('/', [InstructorDashboard::class, 'index'])->name('dashboard');
        Route::get('/courses/{course}', [InstructorDashboard::class, 'course'])->name('course');
        Route::get('/courses/{course}/export', [InstructorDashboard::class, 'export'])->name('export');
    });
});
