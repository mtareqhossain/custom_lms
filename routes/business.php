<?php
use App\Http\Controllers\Business\{CertificateExportController, DashboardController, StudentImportController};
use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'business'])->prefix('business')->name('business.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/courses/{course}', [DashboardController::class, 'course'])->name('course');
    Route::get('/courses/{course}/certificates.zip', CertificateExportController::class)->name('certificates.export');
    Route::get('/import', [StudentImportController::class, 'create'])->name('import.create');
    Route::post('/import', [StudentImportController::class, 'store'])->name('import.store');
});

// Public verification — no auth, so anyone with the link (an employer, say) can confirm a certificate is real.
Route::get('/certificates/{code}', [CertificateController::class, 'show'])->name('certificates.show');
Route::get('/certificates/{code}/download', [CertificateController::class, 'download'])->middleware('auth')->name('certificates.download');
