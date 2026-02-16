<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus;

use Illuminate\Contracts\Bus\Dispatcher;
use Shared\Application\Bus\CommandBusInterface;
use Shared\Application\Bus\CommandInterface;

/**
 * Laravel Command Bus Implementation
 *
 * Uses Laravel's native bus for command dispatching
 */
final class LaravelCommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly Dispatcher $bus
    ) {}

    public function dispatch(CommandInterface $command): mixed
    {
        return $this->bus->dispatch($command);
    }
}
