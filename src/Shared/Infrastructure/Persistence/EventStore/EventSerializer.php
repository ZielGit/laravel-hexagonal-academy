<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\EventStore;

use Shared\Domain\Event\DomainEvent;

/**
 * Event Serializer
 *
 * Handles serialization and deserialization of domain events
 * for storage in the event store.
 */
final class EventSerializer
{
    /**
     * Map of event type names to their fully qualified class names
     *
     * @var array<string, string>
     */
    private array $eventMap = [];

    /**
     * Register an event class for deserialization
     *
     * @param  string  $eventType  Short event name (e.g., 'CourseCreated')
     * @param  string  $eventClass  Fully qualified class name
     */
    public function register(string $eventType, string $eventClass): void
    {
        $this->eventMap[$eventType] = $eventClass;
    }

    /**
     * Deserialize a database row into a DomainEvent
     *
     * @throws \RuntimeException
     */
    public function deserialize(object $row): DomainEvent
    {
        $eventType = $row->event_type;

        if (! isset($this->eventMap[$eventType])) {
            throw new \RuntimeException(
                sprintf('No class registered for event type: %s', $eventType)
            );
        }

        $eventClass = $this->eventMap[$eventType];
        $data = json_decode($row->event_data, true);

        $data['aggregate_id'] = $row->aggregate_id;
        $data['aggregate_version'] = $row->aggregate_version;
        $data['event_id'] = $row->event_id;
        $data['occurred_on'] = $row->occurred_on;

        return $eventClass::fromArray($data);
    }

    /**
     * Get the aggregate type for a given event
     */
    public function getAggregateType(DomainEvent $event): string
    {
        // Extract the bounded context from the event namespace
        // E.g.: BoundedContext\CourseCatalog\Domain\Event\CourseCreated → CourseCatalog
        $parts = explode('\\', get_class($event));

        return $parts[1] ?? 'Unknown';
    }
}
