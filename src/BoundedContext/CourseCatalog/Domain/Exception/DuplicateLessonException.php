<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\ModuleId;

/**
 * Duplicate Lesson Exception
 */
final class DuplicateLessonException extends CourseCatalogException
{
    public static function withTitle(string $title, ModuleId $moduleId): self
    {
        return new self(
            sprintf(
                'A lesson with title "%s" already exists in module "%s"',
                $title,
                $moduleId->toString()
            )
        );
    }
}
