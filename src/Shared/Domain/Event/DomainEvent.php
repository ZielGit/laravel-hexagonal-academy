<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Base class for all Domain Events
 *
 * Domain Events represent something that happened in the domain
 * that domain experts care about.
 *
 * @package Shared\Domain\Event
 */
abstract class DomainEvent
{
    /**
     * Unique identifier for this event
     */
    private UuidInterface $eventId;

    /**
     * When the event occurred
     */
    private DateTimeImmutable $occurredOn;

    /**
     * ID of the aggregate that generated this event
     */
    protected string $aggregateId;

    /**
     * Version of the aggregate when event was created
     */
    private int $aggregateVersion;

    /**
     * Constructor
     *
     * @param string $aggregateId
     * @param int $aggregateVersion
     * @param UuidInterface|null $eventId
     * @param DateTimeImmutable|null $occurredOn
     */
    public function __construct(
        string $aggregateId,
        int $aggregateVersion = 1,
        ?UuidInterface $eventId = null,
        ?DateTimeImmutable $occurredOn = null
    ) {
        $this->aggregateId = $aggregateId;
        $this->aggregateVersion = $aggregateVersion;
        $this->eventId = $eventId ?? Uuid::uuid4();
        $this->occurredOn = $occurredOn ?? new DateTimeImmutable();
    }

    /**
     * Get event unique identifier
     *
     * @return UuidInterface
     */
    public function getEventId(): UuidInterface
    {
        return $this->eventId;
    }

    /**
     * Get when the event occurred
     *
     * @return DateTimeImmutable
     */
    public function getOccurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * Get aggregate ID that generated this event
     *
     * @return string
     */
    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * Get aggregate version when event was created
     *
     * @return int
     */
    public function getAggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    /**
     * Get event name for routing/logging
     *
     * @return string
     */
    public function getEventName(): string
    {
        $classParts = explode('\\', static::class);
        return end($classParts);
    }

    /**
     * Serialize event to array for storage
     *
     * Must be implemented by concrete events to include all relevant data
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * Deserialize event from stored data
     *
     * Must be implemented by concrete events to reconstruct from storage
     *
     * @param array<string, mixed> $data
     * @return static
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Get event payload for serialization
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'event_id' => $this->eventId->toString(),
            'aggregate_id' => $this->aggregateId,
            'aggregate_version' => $this->aggregateVersion,
            'occurred_on' => $this->occurredOn->format('Y-m-d H:i:s.u'),
            'event_name' => $this->getEventName(),
            'data' => $this->toArray(),
        ];
    }
}
