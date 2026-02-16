<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Shared\Domain\Event\DomainEvent;

/**
 * Module Added To Course Event
 *
 * Fired when a new module is added to a course
 */
final class ModuleAddedToCourse extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $moduleId,
        private readonly string $moduleTitle,
        private readonly int $order,
        int $aggregateVersion = 1,
        ?UuidInterface $eventId = null,
        ?DateTimeImmutable $occurredOn = null
    ) {
        parent::__construct($aggregateId, $aggregateVersion, $eventId, $occurredOn);
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    public function getModuleTitle(): string
    {
        return $this->moduleTitle;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function toArray(): array
    {
        return [
            'module_id' => $this->moduleId,
            'module_title' => $this->moduleTitle,
            'order' => $this->order,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            aggregateId: $data['aggregate_id'],
            moduleId: $data['module_id'],
            moduleTitle: $data['module_title'],
            order: $data['order'],
            aggregateVersion: $data['aggregate_version'] ?? 1,
            eventId: isset($data['event_id']) ? Uuid::fromString($data['event_id']) : null,
            occurredOn: isset($data['occurred_on']) ? new DateTimeImmutable($data['occurred_on']) : null
        );
    }
}
