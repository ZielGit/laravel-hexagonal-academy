<?php

declare(strict_types=1);

namespace Shared\Application\Command;

use Shared\Application\Bus\CommandInterface;

/**
 * Abstract base class for Commands
 *
 * Provides common functionality for all commands
 */
abstract class Command implements CommandInterface
{
    /**
     * Get command name for logging/debugging
     */
    public function getCommandName(): string
    {
        $classParts = explode('\\', static::class);

        return end($classParts);
    }
}
