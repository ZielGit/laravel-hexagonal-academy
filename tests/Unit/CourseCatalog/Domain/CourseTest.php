<?php

namespace Tests\Unit\CourseCatalog\Domain;

use BoundedContext\CourseCatalog\Domain\Event\CourseArchived;
use BoundedContext\CourseCatalog\Domain\Event\CourseCreated;
use BoundedContext\CourseCatalog\Domain\Event\CoursePublished;
use BoundedContext\CourseCatalog\Domain\Event\ModuleAddedToCourse;
use BoundedContext\CourseCatalog\Domain\Exception\CourseAlreadyPublishedException;
use BoundedContext\CourseCatalog\Domain\Exception\CourseCannotBeEditedException;
use BoundedContext\CourseCatalog\Domain\Exception\CourseCannotBePublishedException;
use BoundedContext\CourseCatalog\Domain\Model\Course;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseDescription;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseLevel;
use BoundedContext\CourseCatalog\Domain\ValueObject\CoursePrice;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseStatus;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseTitle;
use BoundedContext\CourseCatalog\Domain\ValueObject\InstructorId;
use BoundedContext\CourseCatalog\Domain\ValueObject\ModuleId;
use PHPUnit\Framework\TestCase;

/**
 * Course Aggregate Unit Tests
 *
 * Tests the business logic of the Course aggregate in isolation.
 * No dependencies on infrastructure (database, HTTP, etc.)
 */
class CourseTest extends TestCase
{
    private CourseId $courseId;

    private InstructorId $instructorId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->courseId = CourseId::generate();
        $this->instructorId = InstructorId::generate();
    }

    /** @test */
    public function it_creates_a_course_in_draft_status(): void
    {
        // Arrange & Act
        $course = $this->createCourse();

        // Assert
        $this->assertEquals(CourseStatus::DRAFT, $course->getStatus());
        $this->assertFalse($course->getStatus()->isPublished());
        $this->assertEquals(0, $course->getModuleCount());
    }

    /** @test */
    public function it_records_course_created_event_on_creation(): void
    {
        // Arrange & Act
        $course = $this->createCourse();
        $events = $course->pullDomainEvents();

        // Assert
        $this->assertCount(1, $events);
        $this->assertInstanceOf(CourseCreated::class, $events[0]);
        $this->assertEquals('Laravel Hexagonal Architecture', $events[0]->getTitle());
    }

    /** @test */
    public function it_publishes_course_with_sufficient_content(): void
    {
        // Arrange
        $course = $this->createCourse();
        $this->addModulesAndLessons($course);
        $course->pullDomainEvents(); // Clear creation events

        // Act
        $course->publish();
        $events = $course->pullDomainEvents();

        // Assert
        $this->assertTrue($course->getStatus()->isPublished());
        $this->assertNotNull($course->getPublishedAt());
        $this->assertCount(1, $events);
        $this->assertInstanceOf(CoursePublished::class, $events[0]);
    }

    /** @test */
    public function it_throws_exception_when_publishing_without_sufficient_content(): void
    {
        // Arrange
        $course = $this->createCourse();
        // No modules or lessons added

        // Assert
        $this->expectException(CourseCannotBePublishedException::class);

        // Act
        $course->publish();
    }

    /** @test */
    public function it_throws_exception_when_publishing_already_published_course(): void
    {
        // Arrange
        $course = $this->createCourse();
        $this->addModulesAndLessons($course);
        $course->publish();

        // Assert
        $this->expectException(CourseAlreadyPublishedException::class);

        // Act
        $course->publish();
    }

    /** @test */
    public function it_updates_course_details_when_in_draft(): void
    {
        // Arrange
        $course = $this->createCourse();
        $course->pullDomainEvents(); // Clear creation events

        $newTitle = CourseTitle::fromString('Updated Course Title');
        $newPrice = CoursePrice::fromAmount(199.99);

        // Act
        $course->update($newTitle, null, $newPrice);
        $events = $course->pullDomainEvents();

        // Assert
        $this->assertEquals($newTitle, $course->getTitle());
        $this->assertEquals($newPrice, $course->getPrice());
        $this->assertCount(1, $events);
    }

    /** @test */
    public function it_throws_exception_when_updating_published_course(): void
    {
        // Arrange
        $course = $this->createCourse();
        $this->addModulesAndLessons($course);
        $course->publish();

        // Assert
        $this->expectException(CourseCannotBeEditedException::class);

        // Act
        $course->update(CourseTitle::fromString('New Title'));
    }

    /** @test */
    public function it_adds_module_to_course(): void
    {
        // Arrange
        $course = $this->createCourse();
        $course->pullDomainEvents();

        $moduleId = ModuleId::generate();
        $moduleTitle = 'Introduction to Hexagonal Architecture';

        // Act
        $course->addModule($moduleId, $moduleTitle);
        $events = $course->pullDomainEvents();

        // Assert
        $this->assertEquals(1, $course->getModuleCount());
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ModuleAddedToCourse::class, $events[0]);
    }

    /** @test */
    public function it_archives_course(): void
    {
        // Arrange
        $course = $this->createCourse();
        $this->addModulesAndLessons($course);
        $course->publish();
        $course->pullDomainEvents();

        // Act
        $course->archive('Course outdated');
        $events = $course->pullDomainEvents();

        // Assert
        $this->assertTrue($course->getStatus()->isArchived());
        $this->assertCount(1, $events);
        $this->assertInstanceOf(CourseArchived::class, $events[0]);
    }

    /** @test */
    public function it_reconstitutes_from_events(): void
    {
        // Arrange
        $courseId = CourseId::generate();
        $events = [
            new CourseCreated(
                aggregateId: $courseId->toString(),
                title: 'Test Course',
                description: 'Test description with at least twenty characters',
                priceCents: 9999,
                currency: 'USD',
                level: 'beginner',
                instructorId: InstructorId::generate()->toString(),
                aggregateVersion: 1
            ),
        ];

        // Act
        $course = Course::reconstituteFromEvents($courseId, $events);

        // Assert
        $this->assertEquals($courseId, $course->getAggregateId());
        $this->assertEquals('Test Course', $course->getTitle()->toString());
        $this->assertEquals(CourseStatus::DRAFT, $course->getStatus());
        $this->assertEmpty($course->getRecordedEvents()); // No new events
    }

    // ============================================
    // Helper Methods
    // ============================================

    private function createCourse(): Course
    {
        return Course::create(
            courseId: $this->courseId,
            title: CourseTitle::fromString('Laravel Hexagonal Architecture'),
            description: CourseDescription::fromString(
                'Learn how to build scalable applications using hexagonal architecture'
            ),
            price: CoursePrice::fromAmount(99.99),
            level: CourseLevel::ADVANCED,
            instructorId: $this->instructorId
        );
    }

    private function addModulesAndLessons(Course $course): void
    {
        // Add module
        $moduleId = ModuleId::generate();
        $course->addModule($moduleId, 'Introduction Module');

        // Add 3 lessons (minimum required)
        $course->addLessonToModule($moduleId, 'lesson-1', 'Lesson 1', 10);
        $course->addLessonToModule($moduleId, 'lesson-2', 'Lesson 2', 15);
        $course->addLessonToModule($moduleId, 'lesson-3', 'Lesson 3', 20);
    }
}
