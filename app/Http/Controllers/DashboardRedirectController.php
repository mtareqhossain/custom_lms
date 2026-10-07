<?php
namespace App\Http\Controllers;

/** Replaces Phase 5: students now land on their own dashboard. */
class DashboardRedirectController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        return match (true) {
            $user->hasRole('admin') => redirect('/admin'),
            $user->hasRole('instructor') => redirect()->route('instructor.dashboard'),
            $user->hasRole('business') && $user->organization_id => redirect()->route('business.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }
}
