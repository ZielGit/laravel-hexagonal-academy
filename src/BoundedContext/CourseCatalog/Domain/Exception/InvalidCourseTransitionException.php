<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Exception;

/**
 * Invalid Course Transition Exception
 */
final class InvalidCourseTransitionException extends CourseCatalogException
{
    public static function fromTo(string $from, string $to): self
    {
        return new self(
            sprintf(
                'Invalid course status transition from "%s" to "%s"',
                $from,
                $to
            )
        );
    }
}
