<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use BoundedContext\CourseCatalog\Domain\ValueObject\ModuleId;

/**
 * Module Not Found Exception
 */
final class ModuleNotFoundException extends CourseCatalogException
{
    public static function withId(ModuleId $moduleId): self
    {
        return new self(
            sprintf('Module with ID "%s" not found', $moduleId->toString())
        );
    }

    public static function inCourse(ModuleId $moduleId, CourseId $courseId): self
    {
        return new self(
            sprintf(
                'Module "%s" not found in course "%s"',
                $moduleId->toString(),
                $courseId->toString()
            )
        );
    }
}
