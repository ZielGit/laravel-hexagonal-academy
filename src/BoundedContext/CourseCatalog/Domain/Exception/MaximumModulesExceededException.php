<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;

/**
 * Maximum Modules Exceeded Exception
 */
final class MaximumModulesExceededException extends CourseCatalogException
{
    public static function forCourse(CourseId $courseId, int $maxModules): self
    {
        return new self(
            sprintf(
                'Course "%s" has reached the maximum number of modules (%d)',
                $courseId->toString(),
                $maxModules
            )
        );
    }
}
