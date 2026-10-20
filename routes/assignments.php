<?php
use App\Http\Controllers\Business\AssignmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'business', 'permission:business.assignments.manage'])->prefix('business')->name('business.')->group(function () {
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
});
