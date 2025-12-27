<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObject;

use Ramsey\Uuid\Uuid;
use InvalidArgumentException;

/**
 * Base class for Aggregate Identifiers
 *
 * Ensures all aggregate IDs follow the same pattern and validation rules
 *
 * @package Shared\Domain\ValueObject
 */
abstract class AggregateId
{
    /**
     * @param string $value UUID string
     */
    protected function __construct(
        private readonly string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create from string value
     *
     * @param string $value
     * @return static
     */
    public static function fromString(string $value): static
    {
        return new static($value);
    }

    /**
     * Generate new unique identifier
     *
     * @return static
     */
    public static function generate(): static
    {
        return new static(Uuid::uuid4()->toString());
    }

    /**
     * Get string representation
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Magic method for string casting
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Check equality with another identifier
     *
     * @param AggregateId $other
     * @return bool
     */
    public function equals(AggregateId $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Validate UUID format
     *
     * @param string $value
     * @return void
     * @throws InvalidArgumentException
     */
    private function validate(string $value): void
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException(
                sprintf('Invalid UUID format: %s', $value)
            );
        }
    }
}
