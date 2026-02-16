<?php

declare(strict_types=1);

namespace Shared\Domain\Aggregate;

use Shared\Domain\Event\DomainEvent;
use Shared\Domain\ValueObject\AggregateId;

/**
 * Base class for all Aggregate Roots using Event Sourcing
 *
 * This abstract class provides the foundation for implementing
 * Event Sourced aggregates following DDD principles.
 */
abstract class AggregateRoot
{
    /**
     * Recorded domain events pending to be dispatched
     *
     * @var array<DomainEvent>
     */
    private array $recordedEvents = [];

    /**
     * Version number for optimistic concurrency control
     */
    private int $aggregateVersion = 0;

    /**
     * Unique identifier for this aggregate
     */
    protected AggregateId $aggregateId;

    /**
     * Record a new domain event
     *
     * Events are not dispatched immediately but stored to be
     * published after the aggregate is persisted.
     */
    protected function recordThat(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
        $this->aggregateVersion++;
    }

    /**
     * Apply an event to reconstitute aggregate state
     *
     * This method is used when replaying events from the event store
     * to rebuild the aggregate's current state.
     */
    public function apply(DomainEvent $event): void
    {
        $method = $this->getApplyMethod($event);

        if (method_exists($this, $method)) {
            $this->$method($event);
        }

        $this->aggregateVersion++;
    }

    /**
     * Get all recorded events and clear the list
     *
     * @return array<DomainEvent>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    /**
     * Get recorded events without clearing them
     *
     * @return array<DomainEvent>
     */
    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }

    /**
     * Reconstitute aggregate from historical events
     *
     * @param  array<DomainEvent>  $events
     */
    public static function reconstituteFromEvents(
        AggregateId $aggregateId,
        array $events
    ): static {
        $aggregate = new static($aggregateId);

        foreach ($events as $event) {
            $aggregate->apply($event);
        }

        $aggregate->recordedEvents = [];

        return $aggregate;
    }

    /**
     * Get the aggregate's unique identifier
     */
    public function getAggregateId(): AggregateId
    {
        return $this->aggregateId;
    }

    /**
     * Get current version for optimistic locking
     */
    public function getAggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    /**
     * Determine the apply method name for an event
     *
     * Convention: apply{EventClassName}
     * Example: CourseCreated -> applyCourseCreated()
     */
    private function getApplyMethod(DomainEvent $event): string
    {
        $classParts = explode('\\', get_class($event));
        $eventName = end($classParts);

        return 'apply'.$eventName;
    }

    /**
     * Check if aggregate has uncommitted changes
     */
    public function hasUncommittedChanges(): bool
    {
        return count($this->recordedEvents) > 0;
    }

    /**
     * Mark changes as committed
     */
    public function markChangesAsCommitted(): void
    {
        $this->recordedEvents = [];
    }
}
