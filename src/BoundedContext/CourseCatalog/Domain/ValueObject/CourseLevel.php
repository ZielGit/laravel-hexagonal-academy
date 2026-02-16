<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\ValueObject;

/**
 * Course Level
 *
 * Represents the difficulty level of a course
 */
enum CourseLevel: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
    case EXPERT = 'expert';

    public function getLabel(): string
    {
        return match ($this) {
            self::BEGINNER => 'Beginner',
            self::INTERMEDIATE => 'Intermediate',
            self::ADVANCED => 'Advanced',
            self::EXPERT => 'Expert',
        };
    }

    public function getOrder(): int
    {
        return match ($this) {
            self::BEGINNER => 1,
            self::INTERMEDIATE => 2,
            self::ADVANCED => 3,
            self::EXPERT => 4,
        };
    }

    public static function fromString(string $level): self
    {
        return self::from(strtolower($level));
    }
}
