<?php

declare(strict_types=1);

namespace Shared\Application\Query;

use Shared\Application\Bus\QueryInterface;

/**
 * Abstract base class for Queries
 *
 * Provides common functionality for all queries
 */
abstract class Query implements QueryInterface
{
    /**
     * Get query name for logging/debugging
     */
    public function getQueryName(): string
    {
        $classParts = explode('\\', static::class);

        return end($classParts);
    }
}
