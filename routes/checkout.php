<?php
use App\Http\Controllers\{CheckoutController, OrderController, StripeWebhookController};
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/courses/{course:slug}/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders/success', [CheckoutController::class, 'success'])->name('orders.success');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
});

// No auth/CSRF: Stripe calls this server-to-server, verified by signature instead.
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');
