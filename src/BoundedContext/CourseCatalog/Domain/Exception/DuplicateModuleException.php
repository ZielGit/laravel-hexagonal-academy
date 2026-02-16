<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;

/**
 * Duplicate Module Exception
 */
final class DuplicateModuleException extends CourseCatalogException
{
    public static function withTitle(string $title, CourseId $courseId): self
    {
        return new self(
            sprintf(
                'A module with title "%s" already exists in course "%s"',
                $title,
                $courseId->toString()
            )
        );
    }
}
