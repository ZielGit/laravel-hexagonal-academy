<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;

/**
 * Course Cannot Be Published Exception
 */
final class CourseCannotBePublishedException extends CourseCatalogException
{
    public static function insufficientContent(CourseId $courseId): self
    {
        return new self(
            sprintf(
                'Course "%s" cannot be published: insufficient content. '.
                'Minimum 1 module with at least 3 lessons required.',
                $courseId->toString()
            )
        );
    }

    public static function invalidStatus(CourseId $courseId, string $currentStatus): self
    {
        return new self(
            sprintf(
                'Course "%s" cannot be published from status "%s"',
                $courseId->toString(),
                $currentStatus
            )
        );
    }
}
