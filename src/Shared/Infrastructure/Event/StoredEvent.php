<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Event;

use DateTimeImmutable;

/**
 * Stored Event
 *
 * Represents how events are stored in the event store
 */
final class StoredEvent
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $aggregateId,
        public readonly string $aggregateType,
        public readonly int $aggregateVersion,
        public readonly string $eventType,
        public readonly array $eventData,
        public readonly DateTimeImmutable $occurredOn,
        public readonly DateTimeImmutable $recordedOn
    ) {
    }

    public static function fromDomainEvent(
        object $event,
        string $aggregateType
    ): self {
        $reflection = new \ReflectionClass($event);

        return new self(
            eventId: $event->getEventId()->toString(),
            aggregateId: $event->getAggregateId(),
            aggregateType: $aggregateType,
            aggregateVersion: $event->getAggregateVersion(),
            eventType: $reflection->getShortName(),
            eventData: $event->toArray(),
            occurredOn: $event->getOccurredOn(),
            recordedOn: new DateTimeImmutable()
        );
    }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'aggregate_id' => $this->aggregateId,
            'aggregate_type' => $this->aggregateType,
            'aggregate_version' => $this->aggregateVersion,
            'event_type' => $this->eventType,
            'event_data' => json_encode($this->eventData),
            'occurred_on' => $this->occurredOn->format('Y-m-d H:i:s.u'),
            'recorded_on' => $this->recordedOn->format('Y-m-d H:i:s.u'),
        ];
    }
}
