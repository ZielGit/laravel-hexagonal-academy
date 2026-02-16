<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Course Price
 */
final class CoursePrice
{
    private const MIN_PRICE = 0; // Free courses allowed

    private const MAX_PRICE = 999999; // $9,999.99

    private function __construct(
        private readonly int $cents,
        private readonly string $currency
    ) {
        $this->validate();
    }

    public static function fromAmount(float $amount, string $currency = 'USD'): self
    {
        return new self((int) ($amount * 100), strtoupper($currency));
    }

    public static function fromCents(int $cents, string $currency = 'USD'): self
    {
        return new self($cents, strtoupper($currency));
    }

    public static function free(string $currency = 'USD'): self
    {
        return new self(0, strtoupper($currency));
    }

    public function getAmount(): float
    {
        return $this->cents / 100;
    }

    public function getCents(): int
    {
        return $this->cents;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function isFree(): bool
    {
        return $this->cents === 0;
    }

    public function equals(CoursePrice $other): bool
    {
        return $this->cents === $other->cents
            && $this->currency === $other->currency;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->getAmount(),
            'cents' => $this->cents,
            'currency' => $this->currency,
            'is_free' => $this->isFree(),
        ];
    }

    private function validate(): void
    {
        if ($this->cents < self::MIN_PRICE) {
            throw new InvalidArgumentException('Course price cannot be negative');
        }

        if ($this->cents > self::MAX_PRICE) {
            throw new InvalidArgumentException(
                sprintf('Course price cannot exceed %s', self::MAX_PRICE / 100)
            );
        }

        if (strlen($this->currency) !== 3) {
            throw new InvalidArgumentException(
                sprintf('Invalid currency code: %s', $this->currency)
            );
        }
    }
}
