<?php
use App\Http\Controllers\Business\DashboardController as BusinessDashboard;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/student', [StudentDashboard::class, 'index'])->name('student.dashboard');
    Route::get('/student/certificates', [StudentDashboard::class, 'certificates'])->name('student.certificates');
});

Route::middleware(['auth', 'business', 'permission:business.dashboard.view'])->prefix('business')->name('business.')->group(function () {
    Route::get('/students', [BusinessDashboard::class, 'students'])->name('students');
    Route::get('/certificates', [BusinessDashboard::class, 'certificates'])->name('certificates');
});
