<?php
namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Widgets\ChartWidget;

class EnrollmentsChart extends ChartWidget
{
    protected static ?string $heading = 'Enrollments, last 30 days';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '260px';
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $counts = Enrollment::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, count(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $labels = [];
        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $start->copy()->addDays($i);
            $labels[] = $day->format('j M');
            $data[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Enrollments',
                'data' => $data,
                'borderColor' => '#0B6E6E',
                'backgroundColor' => 'rgba(11,110,110,0.12)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $labels,
        ];
    }
}
