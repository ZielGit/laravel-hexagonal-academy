<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

/**
 * Query Bus Interface
 *
 * Dispatches queries to their respective handlers.
 * Queries represent read operations that don't modify state.
 */
interface QueryBusInterface
{
    /**
     * Dispatch a query to its handler
     *
     * @return mixed The query result
     */
    public function ask(QueryInterface $query): mixed;
}
