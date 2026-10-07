<?php
use App\Http\Controllers\{CatalogController, EnrollController, LearnController, MyCoursesController};
use Illuminate\Support\Facades\Route;

Route::get('/courses', [CatalogController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CatalogController::class, 'show'])->name('courses.show');

Route::middleware('auth')->group(function () {
    Route::post('/courses/{course:slug}/enroll', EnrollController::class)->name('courses.enroll');
    Route::get('/my-courses', MyCoursesController::class)->name('my.courses');
    Route::get('/learn/{course:slug}/{lesson?}', [LearnController::class, 'show'])->name('learn.show');
    Route::post('/learn/{course:slug}/{lesson}/complete', [LearnController::class, 'complete'])->name('learn.complete');
});
