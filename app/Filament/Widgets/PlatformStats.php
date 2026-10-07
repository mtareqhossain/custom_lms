<?php
namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    protected function getStats(): array
    {
        $enrollments = Enrollment::count();
        $completed = Enrollment::whereNotNull('completed_at')->count();

        return [
            Stat::make('Learners', User::role('student')->count())
                ->description(Organization::count().' organizations')
                ->descriptionIcon('heroicon-m-building-office'),
            Stat::make('Published courses', Course::where('status', 'published')->count())
                ->description(Course::where('status', 'draft')->count().' drafts'),
            Stat::make('Enrollments', $enrollments)
                ->description(Enrollment::where('created_at', '>=', now()->subDays(7))->count().' in the last 7 days')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Completion rate', ($enrollments ? (int) round($completed / $enrollments * 100) : 0).'%')
                ->description($completed.' completed'),
        ];
    }
}
