<?php
use App\Http\Controllers\Business\{CertificateExportController, DashboardController, StudentImportController};
use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Business\PeopleController;

Route::middleware(['auth', 'business', 'permission:business.dashboard.view'])->prefix('business')->name('business.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/courses/{course}', [DashboardController::class, 'course'])->name('course');
});

Route::middleware(['auth', 'business', 'permission:business.export'])->prefix('business')->name('business.')->group(function () {
    Route::get('/courses/{course}/certificates.zip', CertificateExportController::class)->name('certificates.export');
});

Route::middleware(['auth', 'business', 'permission:students.import'])->prefix('business')->name('business.')->group(function () {
    Route::get('/import', [StudentImportController::class, 'create'])->name('import.create');
    Route::post('/import', [StudentImportController::class, 'store'])->name('import.store');
});

// Public verification — no auth, so anyone with the link (an employer, say) can confirm a certificate is real.
Route::get('/certificates/{code}', [CertificateController::class, 'show'])->name('certificates.show');
Route::get('/certificates/{code}/download', [CertificateController::class, 'download'])->middleware('auth')->name('certificates.download');

Route::middleware(['auth', 'business', 'permission:students.manage'])->prefix('business')->name('business.')->group(function () {
    Route::get('/people/create', [PeopleController::class, 'create'])->name('people.create');
    Route::post('/people', [PeopleController::class, 'store'])->name('people.store');
    Route::get('/people/{user}/edit', [PeopleController::class, 'edit'])->name('people.edit');
    Route::put('/people/{user}', [PeopleController::class, 'update'])->name('people.update');
    Route::get('/people/{user}', [PeopleController::class, 'show'])->name('people.show');
});

Route::middleware(['auth', 'business', 'permission:business.export'])->prefix('business')->name('business.')->group(function () {
    Route::get('/export', [PeopleController::class, 'export'])->name('roster.export');
});
