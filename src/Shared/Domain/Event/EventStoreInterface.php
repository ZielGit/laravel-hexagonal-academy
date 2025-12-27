<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

use Shared\Domain\ValueObject\AggregateId;

/**
 * Event Store Interface
 *
 * Repository for storing and retrieving domain events
 *
 * @package Shared\Domain\Event
 */
interface EventStoreInterface
{
    /**
     * Append events to the event stream
     *
     * @param AggregateId $aggregateId
     * @param array<DomainEvent> $events
     * @param int $expectedVersion For optimistic concurrency control
     * @return void
     * @throws ConcurrencyException if version mismatch
     */
    public function append(
        AggregateId $aggregateId,
        array $events,
        int $expectedVersion
    ): void;

    /**
     * Load all events for an aggregate
     *
     * @param AggregateId $aggregateId
     * @return array<DomainEvent>
     */
    public function load(AggregateId $aggregateId): array;

    /**
     * Load events from a specific version
     *
     * @param AggregateId $aggregateId
     * @param int $fromVersion
     * @return array<DomainEvent>
     */
    public function loadFromVersion(
        AggregateId $aggregateId,
        int $fromVersion
    ): array;

    /**
     * Check if aggregate exists
     *
     * @param AggregateId $aggregateId
     * @return bool
     */
    public function exists(AggregateId $aggregateId): bool;

    /**
     * Get the current version of an aggregate
     *
     * @param AggregateId $aggregateId
     * @return int
     */
    public function getVersion(AggregateId $aggregateId): int;

    /**
     * Get all events of a specific type
     *
     * @param string $eventType
     * @return array<DomainEvent>
     */
    public function loadByEventType(string $eventType): array;
}
