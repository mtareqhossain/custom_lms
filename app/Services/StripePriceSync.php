<?php
namespace App\Services;

use App\Models\Course;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;

/** Keeps each paid course's Stripe Price in sync with its title/price/currency. */
class StripePriceSync
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }

    public function sync(Course $course): void
    {
        if ($course->pricing_type !== \App\Enums\PricingType::Paid) {
            return;
        }

        // A Stripe Price is immutable once created, so a real price/currency change means a new one.
        if ($course->stripe_price_id) {
            $existing = $this->stripe->prices->retrieve($course->stripe_price_id);
            if ($existing->unit_amount === $course->price_cents && strtolower($existing->currency) === strtolower($course->currency)) {
                $this->stripe->products->update($existing->product, ['name' => $course->title]);
                return;
            }
        }

        $product = $this->stripe->products->create(['name' => $course->title]);
        $price = $this->stripe->prices->create([
            'product' => $product->id,
            'unit_amount' => $course->price_cents,
            'currency' => strtolower($course->currency),
        ]);

        $course->forceFill(['stripe_price_id' => $price->id])->saveQuietly();
    }
}
