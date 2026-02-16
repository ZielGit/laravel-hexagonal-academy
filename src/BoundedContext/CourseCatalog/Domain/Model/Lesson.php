<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Model;

/**
 * Lesson Entity (Part of Module)
 */
final class Lesson
{
    public function __construct(
        private readonly string $id,
        private readonly string $title,
        private readonly int $durationMinutes,
        private readonly int $order
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDuration(): int
    {
        return $this->durationMinutes;
    }

    public function getOrder(): int
    {
        return $this->order;
    }
}
