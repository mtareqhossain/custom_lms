<?php
namespace App\Http\Controllers;

use App\Models\Enrollment;

class MyCoursesController extends Controller
{
    public function __invoke()
    {
        $enrollments = Enrollment::with(['course.instructor', 'certificate'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('learn.my-courses', compact('enrollments'));
    }
}
