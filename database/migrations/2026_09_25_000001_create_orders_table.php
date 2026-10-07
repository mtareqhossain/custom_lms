<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->string('stripe_checkout_session_id')->unique();
            $t->string('stripe_payment_intent_id')->nullable();
            $t->unsignedInteger('amount_cents');
            $t->char('currency', 3);
            $t->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $t->string('receipt_url')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('orders'); }
};
