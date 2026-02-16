<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus;

use Illuminate\Contracts\Bus\Dispatcher;
use Shared\Application\Bus\QueryBusInterface;
use Shared\Application\Bus\QueryInterface;

/**
 * Laravel Query Bus Implementation
 *
 * Uses Laravel's native bus for query dispatching
 */
final class LaravelQueryBus implements QueryBusInterface
{
    public function __construct(
        private readonly Dispatcher $bus
    ) {}

    public function ask(QueryInterface $query): mixed
    {
        return $this->bus->dispatch($query);
    }
}
