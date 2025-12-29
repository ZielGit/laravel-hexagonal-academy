<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

/**
 * Event Bus Interface
 *
 * Publishes domain events to registered subscribers.
 *
 * @package Shared\Application\Bus
 */
interface EventBusInterface
{
    /**
     * Publish a domain event
     *
     * @param object $event
     * @return void
     */
    public function publish(object $event): void;

    /**
     * Publish multiple domain events
     *
     * @param array<object> $events
     * @return void
     */
    public function publishBatch(array $events): void;
}
