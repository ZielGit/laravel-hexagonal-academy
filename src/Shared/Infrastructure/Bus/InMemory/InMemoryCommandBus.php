<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus\InMemory;

use Shared\Application\Bus\CommandBusInterface;
use Shared\Application\Bus\CommandInterface;

/**
 * In-Memory Command Bus
 *
 * Synchronous command bus for testing purposes
 */
final class InMemoryCommandBus implements CommandBusInterface
{
    /**
     * @var array<string, callable>
     */
    private array $handlers = [];

    public function register(string $commandClass, callable $handler): void
    {
        $this->handlers[$commandClass] = $handler;
    }

    public function dispatch(CommandInterface $command): mixed
    {
        $commandClass = get_class($command);

        if (!isset($this->handlers[$commandClass])) {
            throw new \RuntimeException(
                sprintf('No handler registered for command: %s', $commandClass)
            );
        }

        return ($this->handlers[$commandClass])($command);
    }
}
