<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Course Description
 */
final class CourseDescription
{
    private const MIN_LENGTH = 20;

    private const MAX_LENGTH = 5000;

    private function __construct(
        private readonly string $value
    ) {
        $this->validate($value);
    }

    public static function fromString(string $description): self
    {
        return new self(trim($description));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getExcerpt(int $length = 150): string
    {
        if (mb_strlen($this->value) <= $length) {
            return $this->value;
        }

        return mb_substr($this->value, 0, $length).'...';
    }

    private function validate(string $description): void
    {
        $length = mb_strlen($description);

        if ($length < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Course description must be at least %d characters', self::MIN_LENGTH)
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Course description cannot exceed %d characters', self::MAX_LENGTH)
            );
        }
    }
}
