<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

/**
 * Command Bus Interface
 *
 * Dispatches commands to their respective handlers.
 * Commands represent intentions to change system state.
 */
interface CommandBusInterface
{
    /**
     * Dispatch a command to its handler
     *
     * @return mixed The handler's return value
     */
    public function dispatch(CommandInterface $command): mixed;
}
