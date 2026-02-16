<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Model;

/**
 * Module Entity (Part of Course Aggregate)
 */
final class Module
{
    private array $lessons = [];

    public function __construct(
        private readonly string $id,
        private readonly string $title,
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

    public function getOrder(): int
    {
        return $this->order;
    }

    public function addLesson(Lesson $lesson): void
    {
        $this->lessons[$lesson->getId()] = $lesson;
    }

    public function getLessons(): array
    {
        return array_values($this->lessons);
    }

    public function getTotalDuration(): int
    {
        return array_reduce(
            $this->lessons,
            fn ($total, $lesson) => $total + $lesson->getDuration(),
            0
        );
    }
}
