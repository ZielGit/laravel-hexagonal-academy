<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\ModuleId;

/**
 * Maximum Lessons Exceeded Exception
 */
final class MaximumLessonsExceededException extends CourseCatalogException
{
    public static function forModule(ModuleId $moduleId, int $maxLessons): self
    {
        return new self(
            sprintf(
                'Module "%s" has reached the maximum number of lessons (%d)',
                $moduleId->toString(),
                $maxLessons
            )
        );
    }
}
