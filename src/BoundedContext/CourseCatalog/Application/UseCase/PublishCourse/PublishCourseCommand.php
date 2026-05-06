<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Application\UseCase\PublishCourse;

use Shared\Application\Command\Command;

final class PublishCourseCommand extends Command
{
    public function __construct(
        public readonly string $courseId,
        public readonly string $instructorId,
    ) {}
}
