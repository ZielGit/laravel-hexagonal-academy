<?php

namespace Tests\Integration\CourseCatalog;

use BoundedContext\CourseCatalog\Application\UseCase\CreateCourse\CreateCourseCommand;
use BoundedContext\CourseCatalog\Application\UseCase\CreateCourse\CreateCourseHandler;
use BoundedContext\CourseCatalog\Domain\Repository\CourseRepositoryInterface;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shared\Infrastructure\Bus\InMemory\InMemoryEventBus;
use Tests\TestCase;

/**
 * Integration Test for CreateCourseHandler
 *
 * Tests the complete flow from Command → Handler → Repository → EventBus
 * Uses real implementations but in-memory storage.
 */
final class CreateCourseHandlerTest extends TestCase
{
    use RefreshDatabase;

    private CreateCourseHandler $handler;

    private CourseRepositoryInterface $repository;

    private InMemoryEventBus $eventBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(CourseRepositoryInterface::class);
        $this->eventBus = new InMemoryEventBus;
        $this->handler = new CreateCourseHandler($this->repository);
    }

    /** @test */
    public function it_creates_course_successfully(): void
    {
        // Arrange
        $courseId = CourseId::generate()->toString();
        $instructorId = $this->createInstructor()->id;

        $command = new CreateCourseCommand(
            courseId: $courseId,
            title: 'Test Course',
            description: 'This is a test course description with sufficient length',
            price: 99.99,
            currency: 'USD',
            level: 'beginner',
            instructorId: $instructorId,
        );

        // Act
        $response = $this->handler->__invoke($command);

        // Assert
        $this->assertEquals($courseId, $response->courseId);
        $this->assertEquals('Test Course', $response->title);
        $this->assertEquals('draft', $response->status);

        // Verify course exists in repository
        $course = $this->repository->findById(CourseId::fromString($courseId));
        $this->assertNotNull($course);
        $this->assertEquals('Test Course', $course->getTitle()->toString());
    }

    /** @test */
    public function it_stores_events_in_event_store(): void
    {
        // Arrange
        $courseId = CourseId::generate()->toString();
        $instructorId = $this->createInstructor()->id;

        $command = new CreateCourseCommand(
            courseId: $courseId,
            title: 'Test Course',
            description: 'This is a test course description with sufficient length',
            price: 49.99,
            currency: 'USD',
            level: 'intermediate',
            instructorId: $instructorId,
        );

        // Act
        $this->handler->__invoke($command);

        // Assert - Check event_store table
        $this->assertDatabaseHas('event_store', [
            'aggregate_id' => $courseId,
            'event_type' => 'CourseCreated',
            'aggregate_version' => 1,
        ]);
    }

    /** @test */
    public function it_projects_to_read_model(): void
    {
        // Arrange
        $courseId = CourseId::generate()->toString();
        $instructorId = $this->createInstructor()->id;

        $command = new CreateCourseCommand(
            courseId: $courseId,
            title: 'Projected Course',
            description: 'This course should appear in the read model table',
            price: 0, // Free course
            currency: 'USD',
            level: 'beginner',
            instructorId: $instructorId,
        );

        // Act
        $this->handler->__invoke($command);

        // Assert - Check courses table (read model)
        $this->assertDatabaseHas('courses', [
            'course_id' => $courseId,
            'title' => 'Projected Course',
            'status' => 'draft',
            'price_cents' => 0,
            'instructor_id' => $instructorId,
        ]);
    }

    /** @test */
    public function it_throws_exception_for_invalid_title(): void
    {
        // Arrange
        $command = new CreateCourseCommand(
            courseId: CourseId::generate()->toString(),
            title: 'Bad', // Too short
            description: 'Valid description with sufficient length',
            price: 99.99,
            currency: 'USD',
            level: 'beginner',
            instructorId: $this->createInstructor()->id,
        );

        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $this->handler->__invoke($command);
    }

    // ============================================
    // Helper Methods
    // ============================================

    private function createInstructor(): object
    {
        return \App\Models\User::factory()->instructor()->create();
    }
}
