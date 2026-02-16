<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus\InMemory;

use Shared\Application\Bus\EventBusInterface;

/**
 * In-Memory Event Bus
 *
 * Collects events for testing purposes
 */
final class InMemoryEventBus implements EventBusInterface
{
    /**
     * @var array<object>
     */
    private array $publishedEvents = [];

    public function publish(object $event): void
    {
        $this->publishedEvents[] = $event;
    }

    public function publishBatch(array $events): void
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }

    /**
     * Get all published events
     *
     * @return array<object>
     */
    public function getPublishedEvents(): array
    {
        return $this->publishedEvents;
    }

    /**
     * Check if specific event was published
     */
    public function hasPublished(string $eventClass): bool
    {
        foreach ($this->publishedEvents as $event) {
            if ($event instanceof $eventClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get events of specific type
     *
     * @return array<object>
     */
    public function getEventsOfType(string $eventClass): array
    {
        return array_filter(
            $this->publishedEvents,
            fn ($event) => $event instanceof $eventClass
        );
    }

    /**
     * Clear all published events
     */
    public function clear(): void
    {
        $this->publishedEvents = [];
    }

    /**
     * Get count of published events
     */
    public function count(): int
    {
        return count($this->publishedEvents);
    }
}
