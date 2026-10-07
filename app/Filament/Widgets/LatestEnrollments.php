<?php
namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestEnrollments extends TableWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest enrollments')
            ->query(Enrollment::query()->with(['user', 'course', 'organization'])->latest())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Learner'),
                Tables\Columns\TextColumn::make('course.title')->label('Course')->limit(40),
                Tables\Columns\TextColumn::make('organization.name')->placeholder('Individual'),
                Tables\Columns\TextColumn::make('source')->badge(),
                Tables\Columns\TextColumn::make('progress_percent')->label('Progress')->suffix('%'),
                Tables\Columns\TextColumn::make('created_at')->label('Enrolled')->since(),
            ])
            ->paginated([5]);
    }
}
