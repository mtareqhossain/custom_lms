<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\HomepageContent;
use App\Models\Organization;
use App\Models\User;

/** Replaces the Phase 15 controller: homepage copy now comes from HomepageContent, not hardcoded arrays. */
class HomeController extends Controller
{
    public function __invoke()
    {
        $featured = Course::where('status', 'published')->withCount('lessons')->latest('published_at')->limit(8)->get();

        $categories = Category::whereHas('courses', fn ($q) => $q->where('status', 'published'))
            ->withCount(['courses' => fn ($q) => $q->where('status', 'published')])
            ->orderByDesc('courses_count')
            ->limit(8)
            ->get();

        $instructors = User::role('instructor')
            ->withCount('taughtCourses')
            ->having('taught_courses_count', '>', 0)
            ->orderByDesc('taught_courses_count')
            ->limit(4)
            ->get();

        $stats = [
            'organizations' => Organization::count(),
            'certificates' => Certificate::count(),
            'courses' => Course::where('status', 'published')->count(),
        ];

        $content = HomepageContent::current();

        return view('home', compact('featured', 'categories', 'instructors', 'stats', 'content'));
    }
}
