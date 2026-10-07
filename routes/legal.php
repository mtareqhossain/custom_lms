<?php
use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/refund-policy', [LegalController::class, 'refundPolicy'])->name('legal.refund-policy');
