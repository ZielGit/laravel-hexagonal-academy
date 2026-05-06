<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Application\UseCase\PublishCourse;

use BoundedContext\CourseCatalog\Domain\Repository\CourseRepositoryInterface;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use Shared\Application\Bus\CommandInterface;

final class PublishCourseHandler
{
    public function __construct(
        private readonly CourseRepositoryInterface $repository
    ) {}

    /**
     * Ownership is enforced by HTTP middleware before this runs.
     */
    public function __invoke(CommandInterface $command): void
    {
        /** @var PublishCourseCommand $command */
        $courseId = CourseId::fromString($command->courseId);

        $course = $this->repository->findByIdOrFail($courseId);

        $course->publish();
        $this->repository->save($course);
    }
}
