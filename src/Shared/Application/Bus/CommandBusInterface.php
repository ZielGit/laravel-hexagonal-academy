<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

/**
 * Command Bus Interface
 *
 * Dispatches commands to their respective handlers.
 * Commands represent intentions to change system state.
 *
 * @package Shared\Application\Bus
 */
interface CommandBusInterface
{
    /**
     * Dispatch a command to its handler
     *
     * @param CommandInterface $command
     * @return mixed The handler's return value
     */
    public function dispatch(CommandInterface $command): mixed;
}
