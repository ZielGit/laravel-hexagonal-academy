<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Base class for Aggregate Identifiers
 *
 * Ensures all aggregate IDs follow the same pattern and validation rules
 */
abstract class AggregateId
{
    /**
     * @param  string  $value  UUID string
     */
    protected function __construct(
        private readonly string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create from string value
     */
    public static function fromString(string $value): static
    {
        return new static($value);
    }

    /**
     * Generate new unique identifier
     */
    public static function generate(): static
    {
        return new static(Uuid::uuid4()->toString());
    }

    /**
     * Get string representation
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Magic method for string casting
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Check equality with another identifier
     */
    public function equals(AggregateId $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Validate UUID format
     *
     * @throws InvalidArgumentException
     */
    private function validate(string $value): void
    {
        if (! Uuid::isValid($value)) {
            throw new InvalidArgumentException(
                sprintf('Invalid UUID format: %s', $value)
            );
        }
    }
}
