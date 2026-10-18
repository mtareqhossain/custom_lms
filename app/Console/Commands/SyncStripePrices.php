<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class SyncStripePrices extends Command
{
    protected $signature = 'courses:sync-stripe-prices';
    protected $description = 'Create a Stripe Price for every paid, published course that is missing one';

    public function handle(): int
    {
        $stripe = new StripeClient(config('cashier.secret'));

        $courses = Course::where('pricing_type', 'paid')->whereNull('stripe_price_id')->get();

        if ($courses->isEmpty()) {
            $this->info('Nothing to do — every paid course already has a Stripe price.');
            return self::SUCCESS;
        }

        foreach ($courses as $course) {
            $price = $stripe->prices->create([
                'unit_amount' => $course->price_cents,
                'currency' => strtolower($course->currency),
                'product_data' => [
                    'name' => $course->title,
                ],
            ]);

            $course->update(['stripe_price_id' => $price->id]);

            $this->info("Created {$price->id} for \"{$course->title}\"");
        }

        return self::SUCCESS;
    }
}
