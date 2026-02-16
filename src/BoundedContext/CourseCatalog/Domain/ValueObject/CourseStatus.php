<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\ValueObject;

/**
 * Course Status
 *
 * Lifecycle status of a course
 */
enum CourseStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case SUSPENDED = 'suspended';

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }

    public function isArchived(): bool
    {
        return $this === self::ARCHIVED;
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    public function canBePublished(): bool
    {
        return $this === self::DRAFT || $this === self::SUSPENDED;
    }

    public function canBeArchived(): bool
    {
        return $this === self::PUBLISHED || $this === self::SUSPENDED;
    }

    public function canBeEdited(): bool
    {
        return $this === self::DRAFT;
    }

    public static function fromString(string $status): self
    {
        return self::from(strtolower($status));
    }
}
