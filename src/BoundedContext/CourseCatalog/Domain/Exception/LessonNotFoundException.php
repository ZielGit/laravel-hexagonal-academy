<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\ModuleId;

/**
 * Lesson Not Found Exception
 */
final class LessonNotFoundException extends CourseCatalogException
{
    public static function withId(string $lessonId): self
    {
        return new self(
            sprintf('Lesson with ID "%s" not found', $lessonId)
        );
    }

    public static function inModule(string $lessonId, ModuleId $moduleId): self
    {
        return new self(
            sprintf(
                'Lesson "%s" not found in module "%s"',
                $lessonId,
                $moduleId->toString()
            )
        );
    }
}
