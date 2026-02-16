<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Shared\Domain\Event\DomainEvent;

/**
 * Course Updated Event
 *
 * Fired when course details are modified
 */
final class CourseUpdated extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly array $changes,
        int $aggregateVersion = 1,
        ?UuidInterface $eventId = null,
        ?DateTimeImmutable $occurredOn = null
    ) {
        parent::__construct($aggregateId, $aggregateVersion, $eventId, $occurredOn);
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function hasChanged(string $field): bool
    {
        return isset($this->changes[$field]);
    }

    public function getOldValue(string $field): mixed
    {
        return $this->changes[$field]['old'] ?? null;
    }

    public function getNewValue(string $field): mixed
    {
        return $this->changes[$field]['new'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'changes' => $this->changes,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            aggregateId: $data['aggregate_id'],
            changes: $data['changes'],
            aggregateVersion: $data['aggregate_version'] ?? 1,
            eventId: isset($data['event_id']) ? Uuid::fromString($data['event_id']) : null,
            occurredOn: isset($data['occurred_on']) ? new DateTimeImmutable($data['occurred_on']) : null
        );
    }
}
