<?php

namespace Tests\Integration\CourseCatalog;

use BoundedContext\CourseCatalog\Domain\Model\Course;
use BoundedContext\CourseCatalog\Domain\Repository\CourseRepositoryInterface;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseDescription;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseLevel;
use BoundedContext\CourseCatalog\Domain\ValueObject\CoursePrice;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseTitle;
use BoundedContext\CourseCatalog\Domain\ValueObject\InstructorId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration Test for CourseRepository
 *
 * Tests the repository with real database connections
 */
final class CourseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CourseRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(CourseRepositoryInterface::class);
    }

    /** @test */
    public function it_saves_and_retrieves_course(): void
    {
        // Arrange
        $courseId = CourseId::generate();
        $course = $this->createTestCourse($courseId);

        // Act
        $this->repository->save($course);
        $retrieved = $this->repository->findById($courseId);

        // Assert
        $this->assertNotNull($retrieved);
        $this->assertEquals($courseId, $retrieved->getAggregateId());
        $this->assertEquals('Test Course', $retrieved->getTitle()->toString());
    }

    /** @test */
    public function it_returns_null_for_non_existent_course(): void
    {
        // Arrange
        $nonExistentId = CourseId::generate();

        // Act
        $result = $this->repository->findById($nonExistentId);

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_checks_course_existence(): void
    {
        // Arrange
        $courseId = CourseId::generate();
        $course = $this->createTestCourse($courseId);
        $this->repository->save($course);

        // Act & Assert
        $this->assertTrue($this->repository->exists($courseId));
        $this->assertFalse($this->repository->exists(CourseId::generate()));
    }

    /** @test */
    public function it_finds_courses_by_instructor(): void
    {
        // Arrange
        $instructorId = InstructorId::generate();

        $course1 = $this->createTestCourse(CourseId::generate(), $instructorId);
        $course2 = $this->createTestCourse(CourseId::generate(), $instructorId);
        $course3 = $this->createTestCourse(CourseId::generate(), InstructorId::generate());

        $this->repository->save($course1);
        $this->repository->save($course2);
        $this->repository->save($course3);

        // Act
        $courses = $this->repository->findByInstructor($instructorId);

        // Assert
        $this->assertCount(2, $courses);
    }

    /** @test */
    public function it_updates_aggregate_on_subsequent_saves(): void
    {
        // Arrange
        $courseId = CourseId::generate();
        $course = $this->createTestCourse($courseId);
        $this->repository->save($course);

        // Act - Update the course
        $retrievedCourse = $this->repository->findById($courseId);
        $retrievedCourse->update(
            CourseTitle::fromString('Updated Title')
        );
        $this->repository->save($retrievedCourse);

        // Assert
        $finalCourse = $this->repository->findById($courseId);
        $this->assertEquals('Updated Title', $finalCourse->getTitle()->toString());
    }

    // ============================================
    // Helper Methods
    // ============================================

    private function createTestCourse(
        CourseId $courseId,
        ?InstructorId $instructorId = null
    ): Course {
        return Course::create(
            courseId: $courseId,
            title: CourseTitle::fromString('Test Course'),
            description: CourseDescription::fromString(
                'This is a test course with sufficient description length'
            ),
            price: CoursePrice::fromAmount(99.99),
            level: CourseLevel::BEGINNER,
            instructorId: $instructorId ?? InstructorId::generate()
        );
    }
}
