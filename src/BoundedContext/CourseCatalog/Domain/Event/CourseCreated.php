<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Shared\Domain\Event\DomainEvent;

/**
 * Course Created Event
 *
 * Fired when a new course is created in the system
 */
final class CourseCreated extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $title,
        private readonly string $description,
        private readonly int $priceCents,
        private readonly string $currency,
        private readonly string $level,
        private readonly string $instructorId,
        int $aggregateVersion = 1,
        ?UuidInterface $eventId = null,
        ?DateTimeImmutable $occurredOn = null
    ) {
        parent::__construct($aggregateId, $aggregateVersion, $eventId, $occurredOn);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPriceCents(): int
    {
        return $this->priceCents;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getInstructorId(): string
    {
        return $this->instructorId;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'price_cents' => $this->priceCents,
            'currency' => $this->currency,
            'level' => $this->level,
            'instructor_id' => $this->instructorId,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            aggregateId: $data['aggregate_id'],
            title: $data['title'],
            description: $data['description'],
            priceCents: $data['price_cents'],
            currency: $data['currency'],
            level: $data['level'],
            instructorId: $data['instructor_id'],
            aggregateVersion: $data['aggregate_version'] ?? 1,
            eventId: isset($data['event_id']) ? Uuid::fromString($data['event_id']) : null,
            occurredOn: isset($data['occurred_on']) ? new DateTimeImmutable($data['occurred_on']) : null
        );
    }
}
