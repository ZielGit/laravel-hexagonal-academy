<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus;

use Shared\Application\Bus\EventBusInterface;

/**
 * Laravel Event Bus Implementation
 *
 * Uses Laravel's event dispatcher for domain events
 *
 * @package Shared\Infrastructure\Bus
 */
final class LaravelEventBus implements EventBusInterface
{
    public function __construct(
        private readonly \Illuminate\Contracts\Events\Dispatcher $dispatcher
    ) {
    }

    public function publish(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function publishBatch(array $events): void
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }
}
