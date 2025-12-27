<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

use Shared\Domain\ValueObject\AggregateId;

/**
 * Event Stream
 *
 * Represents a stream of events for an aggregate
 */
final class EventStream
{
    /**
     * @param AggregateId $aggregateId
     * @param array<DomainEvent> $events
     * @param int $version
     */
    public function __construct(
        private readonly AggregateId $aggregateId,
        private readonly array $events,
        private readonly int $version
    ) {
    }

    public function getAggregateId(): AggregateId
    {
        return $this->aggregateId;
    }

    /**
     * @return array<DomainEvent>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function isEmpty(): bool
    {
        return empty($this->events);
    }

    public function count(): int
    {
        return count($this->events);
    }
}
