<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus\InMemory;

use Shared\Application\Bus\QueryBusInterface;
use Shared\Application\Bus\QueryInterface;

/**
 * In-Memory Query Bus
 *
 * Synchronous query bus for testing purposes
 */
final class InMemoryQueryBus implements QueryBusInterface
{
    /**
     * @var array<string, callable>
     */
    private array $handlers = [];

    public function register(string $queryClass, callable $handler): void
    {
        $this->handlers[$queryClass] = $handler;
    }

    public function ask(QueryInterface $query): mixed
    {
        $queryClass = get_class($query);

        if (! isset($this->handlers[$queryClass])) {
            throw new \RuntimeException(
                sprintf('No handler registered for query: %s', $queryClass)
            );
        }

        return ($this->handlers[$queryClass])($query);
    }
}
