<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;

/**
 * Course Cannot Be Archived Exception
 */
final class CourseCannotBeArchivedException extends CourseCatalogException
{
    public static function invalidStatus(CourseId $courseId, string $currentStatus): self
    {
        return new self(
            sprintf(
                'Course "%s" cannot be archived from status "%s"',
                $courseId->toString(),
                $currentStatus
            )
        );
    }

    public static function hasActiveEnrollments(CourseId $courseId, int $enrollmentCount): self
    {
        return new self(
            sprintf(
                'Course "%s" cannot be archived: has %d active enrollments',
                $courseId->toString(),
                $enrollmentCount
            )
        );
    }
}
