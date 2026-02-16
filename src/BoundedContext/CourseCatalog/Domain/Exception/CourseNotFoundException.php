<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;

/**
 * Course Not Found Exception
 */
final class CourseNotFoundException extends CourseCatalogException
{
    public static function withId(CourseId $courseId): self
    {
        return new self(
            sprintf('Course with ID "%s" not found', $courseId->toString())
        );
    }
}
