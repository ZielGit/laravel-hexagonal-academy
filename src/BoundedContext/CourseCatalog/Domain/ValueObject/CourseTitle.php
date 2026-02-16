<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Course Title
 *
 * Represents a validated course title
 */
final class CourseTitle
{
    private const MIN_LENGTH = 5;

    private const MAX_LENGTH = 200;

    private function __construct(
        private readonly string $value
    ) {
        $this->validate($value);
    }

    public static function fromString(string $title): self
    {
        return new self(trim($title));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(CourseTitle $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(string $title): void
    {
        $length = mb_strlen($title);

        if ($length < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Course title must be at least %d characters', self::MIN_LENGTH)
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Course title cannot exceed %d characters', self::MAX_LENGTH)
            );
        }

        if (empty(trim($title))) {
            throw new InvalidArgumentException('Course title cannot be empty');
        }
    }
}
