<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;

/**
 * Course Cannot Be Edited Exception
 */
final class CourseCannotBeEditedException extends CourseCatalogException
{
    public static function alreadyPublished(CourseId $courseId): self
    {
        return new self(
            sprintf(
                'Course "%s" cannot be edited because it is already published. '.
                'Only draft courses can be edited.',
                $courseId->toString()
            )
        );
    }
}
