<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Application\UseCase\CreateCourse;

use BoundedContext\CourseCatalog\Domain\Model\Course;
use BoundedContext\CourseCatalog\Domain\Repository\CourseRepositoryInterface;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseDescription;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseLevel;
use BoundedContext\CourseCatalog\Domain\ValueObject\CoursePrice;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseTitle;
use BoundedContext\CourseCatalog\Domain\ValueObject\InstructorId;
use Shared\Application\Bus\CommandInterface;

/**
 * Create Course Handler
 *
 * Orchestrates the creation of a new course:
 * 1. Builds Value Objects from raw data
 * 2. Calls the Course factory method
 * 3. Persists via the repository
 */
final class CreateCourseHandler
{
    public function __construct(
        private readonly CourseRepositoryInterface $repository
    ) {}

    /**
     * Handle the CreateCourseCommand
     *
     * @param  CreateCourseCommand  $command
     */
    public function __invoke(CommandInterface $command): CreateCourseResponse
    {
        /** @var CreateCourseCommand $command */

        // Build Value Objects (validation happens here)
        $courseId = CourseId::fromString($command->courseId);
        $title = CourseTitle::fromString($command->title);
        $description = CourseDescription::fromString($command->description);
        $price = CoursePrice::fromAmount($command->price, $command->currency);
        $level = CourseLevel::fromString($command->level);
        $instructorId = InstructorId::fromString($command->instructorId);

        // Create the aggregate via factory method
        // This records the CourseCreated domain event internally
        $course = Course::create(
            courseId: $courseId,
            title: $title,
            description: $description,
            price: $price,
            level: $level,
            instructorId: $instructorId
        );

        // Persist the aggregate
        // Repository will:
        // 1. Save events to event store
        // 2. Publish domain events to event bus
        // 3. Projector will update the read model
        $this->repository->save($course);

        return new CreateCourseResponse(
            courseId: $courseId->toString(),
            title: $title->toString(),
            status: $course->getStatus()->value
        );
    }
}
