<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

/**
 * Event Bus Interface
 *
 * Publishes domain events to registered subscribers.
 */
interface EventBusInterface
{
    /**
     * Publish a domain event
     */
    public function publish(object $event): void;

    /**
     * Publish multiple domain events
     *
     * @param  array<object>  $events
     */
    public function publishBatch(array $events): void;
}
