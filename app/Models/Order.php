<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'stripe_checkout_session_id', 'stripe_payment_intent_id',
        'amount_cents', 'currency', 'status', 'receipt_url', 'paid_at',
    ];

    protected $casts = ['paid_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}
