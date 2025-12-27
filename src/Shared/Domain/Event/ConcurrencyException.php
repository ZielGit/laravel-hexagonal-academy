<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

use Shared\Domain\ValueObject\AggregateId;

/**
 * Exception thrown on version conflicts
 */
final class ConcurrencyException extends \RuntimeException
{
    public static function versionMismatch(
        AggregateId $aggregateId,
        int $expectedVersion,
        int $actualVersion
    ): self {
        return new self(
            sprintf(
                'Concurrency conflict for aggregate %s. Expected version %d, but actual is %d',
                $aggregateId->toString(),
                $expectedVersion,
                $actualVersion
            )
        );
    }
}
