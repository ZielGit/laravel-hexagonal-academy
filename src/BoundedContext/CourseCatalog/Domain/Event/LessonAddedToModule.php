<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Shared\Domain\Event\DomainEvent;

/**
 * Lesson Added To Module Event
 *
 * Fired when a new lesson is added to a module
 */
final class LessonAddedToModule extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $moduleId,
        private readonly string $lessonId,
        private readonly string $lessonTitle,
        private readonly int $durationMinutes,
        private readonly int $order,
        int $aggregateVersion = 1,
        ?UuidInterface $eventId = null,
        ?DateTimeImmutable $occurredOn = null
    ) {
        parent::__construct($aggregateId, $aggregateVersion, $eventId, $occurredOn);
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    public function getLessonId(): string
    {
        return $this->lessonId;
    }

    public function getLessonTitle(): string
    {
        return $this->lessonTitle;
    }

    public function getDurationMinutes(): int
    {
        return $this->durationMinutes;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function toArray(): array
    {
        return [
            'module_id' => $this->moduleId,
            'lesson_id' => $this->lessonId,
            'lesson_title' => $this->lessonTitle,
            'duration_minutes' => $this->durationMinutes,
            'order' => $this->order,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            aggregateId: $data['aggregate_id'],
            moduleId: $data['module_id'],
            lessonId: $data['lesson_id'],
            lessonTitle: $data['lesson_title'],
            durationMinutes: $data['duration_minutes'],
            order: $data['order'],
            aggregateVersion: $data['aggregate_version'] ?? 1,
            eventId: isset($data['event_id']) ? Uuid::fromString($data['event_id']) : null,
            occurredOn: isset($data['occurred_on']) ? new DateTimeImmutable($data['occurred_on']) : null
        );
    }
}
