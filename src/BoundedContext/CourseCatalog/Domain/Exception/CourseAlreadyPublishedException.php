<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;

/**
 * Course Already Published Exception
 */
final class CourseAlreadyPublishedException extends CourseCatalogException
{
    public static function withId(CourseId $courseId): self
    {
        return new self(
            sprintf('Course "%s" is already published', $courseId->toString())
        );
    }
}
