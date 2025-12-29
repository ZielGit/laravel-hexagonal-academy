<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

/**
 * Command Handler Interface
 *
 * All command handlers must implement this interface
 *
 * @template T of CommandInterface
 */
interface CommandHandlerInterface
{
    /**
     * Handle the command
     *
     * @param CommandInterface $command
     * @return mixed
     */
    public function __invoke(CommandInterface $command): mixed;
}
