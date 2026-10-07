<?php
namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    public function store(Course $course)
    {
        abort_unless($course->status === 'published' && ! $course->isFree(), 404);
        abort_if($course->stripe_price_id === null, 422, 'This course is not ready for checkout yet.');

        $existing = Enrollment::where('user_id', Auth::id())->where('course_id', $course->id)->first();
        if ($existing && $existing->isActive()) {
            return redirect()->route('learn.show', $course);
        }

        $stripe = new StripeClient(config('cashier.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => Auth::user()->email,
            'line_items' => [['price' => $course->stripe_price_id, 'quantity' => 1]],
            'success_url' => route('orders.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('courses.show', $course),
            'metadata' => ['user_id' => Auth::id(), 'course_id' => $course->id],
        ]);

        return redirect($session->url);
    }

    public function success()
    {
        return view('orders.success');
    }
}
