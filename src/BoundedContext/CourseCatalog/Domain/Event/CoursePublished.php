<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Shared\Domain\Event\DomainEvent;

/**
 * Course Published Event
 *
 * Fired when a course is published and becomes available to students
 */
final class CoursePublished extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $title,
        private readonly DateTimeImmutable $publishedAt,
        int $aggregateVersion = 1,
        ?UuidInterface $eventId = null,
        ?DateTimeImmutable $occurredOn = null
    ) {
        parent::__construct($aggregateId, $aggregateVersion, $eventId, $occurredOn);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPublishedAt(): DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'published_at' => $this->publishedAt->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            aggregateId: $data['aggregate_id'],
            title: $data['title'],
            publishedAt: new DateTimeImmutable($data['published_at']),
            aggregateVersion: $data['aggregate_version'] ?? 1,
            eventId: isset($data['event_id']) ? Uuid::fromString($data['event_id']) : null,
            occurredOn: isset($data['occurred_on']) ? new DateTimeImmutable($data['occurred_on']) : null
        );
    }
}
