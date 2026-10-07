<?php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    protected $model = \App\Models\Course::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'instructor_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'summary' => fake()->sentence(),
            'level' => 'beginner',
            'status' => 'published',
            'pricing_type' => 'free',
            'price_cents' => 0,
            'currency' => 'GBP',
            'access_days' => null,
            'published_at' => now(),
        ];
    }

    public function paid(int $cents = 4900, ?int $accessDays = null): static
    {
        return $this->state(fn () => [
            'pricing_type' => 'paid',
            'price_cents' => $cents,
            'access_days' => $accessDays,
            'stripe_price_id' => 'price_test_'.fake()->unique()->numberBetween(1, 99999),
        ]);
    }

    public function withLessons(int $count = 3): static
    {
        return $this->afterCreating(function (\App\Models\Course $course) use ($count) {
            $section = \App\Models\Section::create(['course_id' => $course->id, 'title' => 'Section 1', 'position' => 1]);
            for ($i = 1; $i <= $count; $i++) {
                \App\Models\Lesson::create([
                    'section_id' => $section->id, 'title' => "Lesson $i", 'type' => 'text',
                    'content' => '<p>Content</p>', 'position' => $i,
                ]);
            }
        });
    }
}
