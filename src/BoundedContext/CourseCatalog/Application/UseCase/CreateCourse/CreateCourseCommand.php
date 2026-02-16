<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Application\UseCase\CreateCourse;

use Shared\Application\Command\Command;

/**
 * Create Course Command
 *
 * Carries the data needed to create a new course.
 * Commands are immutable data transfer objects.
 */
final class CreateCourseCommand extends Command
{
    public function __construct(
        public readonly string $courseId,
        public readonly string $title,
        public readonly string $description,
        public readonly float $price,
        public readonly string $currency,
        public readonly string $level,
        public readonly string $instructorId,
    ) {}
}
