<?php
namespace App\Http\Controllers;

use App\Enums\EnrollmentSource;
use App\Models\Course;
use App\Models\Enrollment;

/** Replaces the Phase 2 EnrollController: paid courses now redirect to Stripe Checkout. */
class EnrollController extends Controller
{
    public function __invoke(Course $course)
    {
        abort_unless($course->status === 'published', 404);

        if (! $course->isFree()) {
            return redirect()->route('checkout.store', $course);
        }

        Enrollment::grant(auth()->user(), $course, EnrollmentSource::Free);

        return redirect()->route('learn.show', $course);
    }
}
