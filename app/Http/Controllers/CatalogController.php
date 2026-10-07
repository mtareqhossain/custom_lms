<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

/** Replaces the Phase 2 controller: adds a category filter alongside search and pricing. */
class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::query()
            ->where('status', 'published')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$s}%")->orWhere('summary', 'like', "%{$s}%")))
            ->when($request->pricing, fn ($q, $p) => $q->where('pricing_type', $p))
            ->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->withCount('lessons')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::whereHas('courses', fn ($q) => $q->where('status', 'published'))->orderBy('name')->get();

        return view('catalog.index', compact('courses', 'categories'));
    }

    public function show(Course $course)
    {
        $user = auth()->user();
        $canPreview = $user && ($user->hasRole('admin') || $user->id === $course->instructor_id);
        abort_unless($course->status === 'published' || $canPreview, 404);

        $course->load('sections.lessons', 'instructor');
        $enrollment = $user ? Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->first() : null;

        return view('catalog.show', compact('course', 'enrollment'));
    }
}
