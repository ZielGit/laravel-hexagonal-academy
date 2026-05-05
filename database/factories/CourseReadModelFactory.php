<?php

namespace Database\Factories;

use BoundedContext\CourseCatalog\Infrastructure\Persistence\Eloquent\CourseReadModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseReadModel>
 */
class CourseReadModelFactory extends Factory
{
    protected $model = CourseReadModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => (string) Str::uuid(),
            'title' => fake()->sentence(8),
            'description' => fake()->paragraphs(3, true),
            'price_cents' => fake()->numberBetween(0, 999_999),
            'currency' => 'USD',
            'level' => 'beginner',
            'status' => 'published',
            'instructor_id' => (string) Str::uuid(),
            'total_modules' => 0,
            'total_lessons' => 0,
            'duration_minutes' => 0,
            'published_at' => now(),
        ];
    }
}
