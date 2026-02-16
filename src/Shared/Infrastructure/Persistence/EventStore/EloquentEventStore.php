<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\EventStore;

use Illuminate\Database\ConnectionInterface;
use Shared\Domain\Event\ConcurrencyException;
use Shared\Domain\Event\DomainEvent;
use Shared\Domain\Event\EventStoreInterface;
use Shared\Domain\ValueObject\AggregateId;

/**
 * Eloquent Event Store Implementation
 *
 * Stores domain events in a relational database using Laravel's
 * query builder for performance and simplicity.
 *
 * Table: event_store
 * - event_id (uuid, PK)
 * - aggregate_id (uuid, indexed)
 * - aggregate_type (string)
 * - aggregate_version (int)
 * - event_type (string)
 * - event_data (json)
 * - occurred_on (datetime)
 * - recorded_on (datetime)
 */
final class EloquentEventStore implements EventStoreInterface
{
    private const TABLE = 'event_store';

    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly EventSerializer $serializer
    ) {}

    /**
     * {@inheritdoc}
     */
    public function append(
        AggregateId $aggregateId,
        array $events,
        int $expectedVersion
    ): void {
        $this->db->transaction(function () use ($aggregateId, $events, $expectedVersion) {
            $currentVersion = $this->getVersion($aggregateId);

            // Optimistic concurrency control
            if ($currentVersion !== $expectedVersion) {
                throw ConcurrencyException::versionMismatch(
                    $aggregateId,
                    $expectedVersion,
                    $currentVersion
                );
            }

            foreach ($events as $event) {
                $this->db->table(self::TABLE)->insert([
                    'event_id' => $event->getEventId()->toString(),
                    'aggregate_id' => $aggregateId->toString(),
                    'aggregate_type' => $this->serializer->getAggregateType($event),
                    'aggregate_version' => $event->getAggregateVersion(),
                    'event_type' => $event->getEventName(),
                    'event_data' => json_encode($event->toArray()),
                    'occurred_on' => $event->getOccurredOn()->format('Y-m-d H:i:s.u'),
                    'recorded_on' => now()->format('Y-m-d H:i:s.u'),
                ]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function load(AggregateId $aggregateId): array
    {
        $rows = $this->db->table(self::TABLE)
            ->where('aggregate_id', $aggregateId->toString())
            ->orderBy('aggregate_version', 'asc')
            ->get();

        return $this->deserializeRows($rows->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromVersion(
        AggregateId $aggregateId,
        int $fromVersion
    ): array {
        $rows = $this->db->table(self::TABLE)
            ->where('aggregate_id', $aggregateId->toString())
            ->where('aggregate_version', '>=', $fromVersion)
            ->orderBy('aggregate_version', 'asc')
            ->get();

        return $this->deserializeRows($rows->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function exists(AggregateId $aggregateId): bool
    {
        return $this->db->table(self::TABLE)
            ->where('aggregate_id', $aggregateId->toString())
            ->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(AggregateId $aggregateId): int
    {
        return (int) $this->db->table(self::TABLE)
            ->where('aggregate_id', $aggregateId->toString())
            ->max('aggregate_version') ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByEventType(string $eventType): array
    {
        $rows = $this->db->table(self::TABLE)
            ->where('event_type', $eventType)
            ->orderBy('recorded_on', 'asc')
            ->get();

        return $this->deserializeRows($rows->toArray());
    }

    /**
     * Deserialize raw database rows into DomainEvent objects
     *
     * @param  array<object>  $rows
     * @return array<DomainEvent>
     */
    private function deserializeRows(array $rows): array
    {
        return array_map(
            fn ($row) => $this->serializer->deserialize($row),
            $rows
        );
    }
}
