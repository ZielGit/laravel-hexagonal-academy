<?php

namespace Tests\Feature\CourseCatalog;

use App\Models\User;
use BoundedContext\CourseCatalog\Domain\Model\Course;
use BoundedContext\CourseCatalog\Domain\Repository\CourseRepositoryInterface;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseDescription;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseLevel;
use BoundedContext\CourseCatalog\Domain\ValueObject\CoursePrice;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseTitle;
use BoundedContext\CourseCatalog\Domain\ValueObject\InstructorId;
use BoundedContext\CourseCatalog\Domain\ValueObject\ModuleId;
use BoundedContext\CourseCatalog\Infrastructure\Persistence\Eloquent\CourseReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test for Publishing Courses
 */
class PublishCourseApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function instructor_can_publish_own_course_with_sufficient_content(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();
        $courseId = $this->createCourseWithContent($instructor->id);

        // Act
        $response = $this->actingAs($instructor, 'sanctum')
            ->postJson("/api/v1/instructor/courses/{$courseId}/publish");

        // Assert
        $response->assertStatus(200)
            ->assertJson(['message' => 'Course published successfully']);

        $this->assertDatabaseHas('courses', [
            'course_id' => $courseId,
            'status' => 'published',
        ]);

        $this->assertNotNull(
            CourseReadModel::where('course_id', $courseId)->first()->published_at
        );
    }

    /** @test */
    public function it_prevents_publishing_course_without_sufficient_content(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();
        $courseId = $this->createCourseWithoutContent($instructor->id);

        // Act
        $response = $this->actingAs($instructor, 'sanctum')
            ->postJson("/api/v1/instructor/courses/{$courseId}/publish");

        // Assert
        $response->assertStatus(422); // Or 400
    }

    /** @test */
    public function instructor_cannot_publish_other_instructors_course(): void
    {
        // Arrange
        $instructor1 = User::factory()->instructor()->create();
        $instructor2 = User::factory()->instructor()->create();
        $courseId = $this->createCourseWithContent($instructor1->id);

        // Act
        $response = $this->actingAs($instructor2, 'sanctum')
            ->postJson("/api/v1/instructor/courses/{$courseId}/publish");

        // Assert
        $response->assertStatus(403);
    }

    // ============================================
    // Helper Methods
    // ============================================

    private function createCourseWithContent(string $instructorId): string
    {
        $courseId = CourseId::generate();
        $course = Course::create(
            courseId: $courseId,
            title: CourseTitle::fromString('Complete Test Course'),
            description: CourseDescription::fromString(
                'A complete course with modules and lessons ready for publishing'
            ),
            price: CoursePrice::fromAmount(99.99),
            level: CourseLevel::INTERMEDIATE,
            instructorId: InstructorId::fromString($instructorId)
        );

        // Add module with 3 lessons
        $moduleId = ModuleId::generate();
        $course->addModule($moduleId, 'Introduction');
        $course->addLessonToModule($moduleId, 'lesson-1', 'Lesson 1', 10);
        $course->addLessonToModule($moduleId, 'lesson-2', 'Lesson 2', 15);
        $course->addLessonToModule($moduleId, 'lesson-3', 'Lesson 3', 20);

        $repository = $this->app->make(CourseRepositoryInterface::class);
        $repository->save($course);

        return $courseId->toString();
    }

    private function createCourseWithoutContent(string $instructorId): string
    {
        $courseId = CourseId::generate();
        $course = Course::create(
            courseId: $courseId,
            title: CourseTitle::fromString('Incomplete Test Course'),
            description: CourseDescription::fromString(
                'An incomplete course without sufficient content'
            ),
            price: CoursePrice::fromAmount(49.99),
            level: CourseLevel::BEGINNER,
            instructorId: InstructorId::fromString($instructorId)
        );

        $repository = $this->app->make(CourseRepositoryInterface::class);
        $repository->save($course);

        return $courseId->toString();
    }
}
