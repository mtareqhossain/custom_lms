<?php
namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Jobs\ImportStudentsJob;
use App\Models\BulkImport;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Replaces the Phase 4 controller: only courses assigned to the organization can be used. */
class StudentImportController extends Controller
{
    private function allowedCourses(int $orgId)
    {
        return Course::where('status', 'published')
            ->whereIn('id', DB::table('course_organization')->where('organization_id', $orgId)->select('course_id'))
            ->orderBy('title');
    }

    public function create()
    {
        $orgId = Auth::user()->organization_id;
        $courses = $this->allowedCourses($orgId)->get();
        $recent = BulkImport::where('organization_id', $orgId)->where('type', 'students')->latest()->limit(10)->get();

        return view('business.import', compact('courses', 'recent'));
    }

    public function store(Request $request)
    {
        $orgId = Auth::user()->organization_id;

        $data = $request->validate([
            'course_id' => ['required', Rule::exists('course_organization', 'course_id')->where('organization_id', $orgId)],
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $course = $this->allowedCourses($orgId)->findOrFail($data['course_id']);
        $path = $request->file('file')->store('bulk-imports', 'local');

        $import = BulkImport::create([
            'user_id' => Auth::id(),
            'organization_id' => $orgId,
            'type' => 'students',
            'file_path' => $path,
            'status' => 'pending',
        ]);

        ImportStudentsJob::dispatch($import->id, $course->id);

        return redirect()->route('business.import.create')->with('status', 'Import started. Refresh in a moment to see the result.');
    }
}
