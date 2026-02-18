<?php

namespace Tests\Feature\CourseCatalog;

use App\Models\User;
use BoundedContext\CourseCatalog\Infrastructure\Persistence\Eloquent\CourseReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test for Listing Courses API
 */
class ListCoursesApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_published_courses(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();

        // Create courses
        CourseReadModel::factory()->create([
            'title' => 'Published Course 1',
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        CourseReadModel::factory()->create([
            'title' => 'Published Course 2',
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        CourseReadModel::factory()->create([
            'title' => 'Draft Course',
            'status' => 'draft',
            'instructor_id' => $instructor->id,
        ]);

        // Act
        $response = $this->getJson('/api/v1/courses');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['title' => 'Published Course 1'])
            ->assertJsonFragment(['title' => 'Published Course 2'])
            ->assertJsonMissing(['title' => 'Draft Course']);
    }

    /** @test */
    public function it_filters_courses_by_level(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();

        CourseReadModel::factory()->create([
            'title' => 'Beginner Course',
            'status' => 'published',
            'level' => 'beginner',
            'instructor_id' => $instructor->id,
        ]);

        CourseReadModel::factory()->create([
            'title' => 'Advanced Course',
            'status' => 'published',
            'level' => 'advanced',
            'instructor_id' => $instructor->id,
        ]);

        // Act
        $response = $this->getJson('/api/v1/courses?level=beginner');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Beginner Course'])
            ->assertJsonMissing(['title' => 'Advanced Course']);
    }

    /** @test */
    public function it_paginates_results(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();

        CourseReadModel::factory(20)->create([
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);

        // Act
        $response = $this->getJson('/api/v1/courses?per_page=10');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);
    }

    /** @test */
    public function it_returns_course_with_stats(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();

        CourseReadModel::factory()->create([
            'title' => 'Course with Stats',
            'status' => 'published',
            'total_modules' => 5,
            'total_lessons' => 25,
            'duration_minutes' => 300,
            'instructor_id' => $instructor->id,
        ]);

        // Act
        $response = $this->getJson('/api/v1/courses');

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment([
                'stats' => [
                    'total_modules' => 5,
                    'total_lessons' => 25,
                    'duration' => '5h 0m',
                    'duration_minutes' => 300,
                ],
            ]);
    }
}
