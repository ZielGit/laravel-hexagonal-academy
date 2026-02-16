<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Shared\Domain\Event\DomainEvent;

/**
 * Course Archived Event
 *
 * Fired when a course is archived and no longer available
 */
final class CourseArchived extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $reason,
        int $aggregateVersion = 1,
        ?UuidInterface $eventId = null,
        ?DateTimeImmutable $occurredOn = null
    ) {
        parent::__construct($aggregateId, $aggregateVersion, $eventId, $occurredOn);
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function toArray(): array
    {
        return [
            'reason' => $this->reason,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            aggregateId: $data['aggregate_id'],
            reason: $data['reason'],
            aggregateVersion: $data['aggregate_version'] ?? 1,
            eventId: isset($data['event_id']) ? Uuid::fromString($data['event_id']) : null,
            occurredOn: isset($data['occurred_on']) ? new DateTimeImmutable($data['occurred_on']) : null
        );
    }
}
