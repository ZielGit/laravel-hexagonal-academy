<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

/**
 * Query Handler Interface
 *
 * All query handlers must implement this interface
 *
 * @template T of QueryInterface
 */
interface QueryHandlerInterface
{
    /**
     * Handle the query
     *
     * @param QueryInterface $query
     * @return mixed
     */
    public function __invoke(QueryInterface $query): mixed;
}
