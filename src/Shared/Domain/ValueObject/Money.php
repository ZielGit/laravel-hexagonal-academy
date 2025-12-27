<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Money Value Object
 *
 * Handles monetary amounts with currency
 */
final class Money
{
    private function __construct(
        private readonly int $amount, // Amount in cents to avoid float issues
        private readonly string $currency
    ) {
        $this->validate();
    }

    public static function fromAmount(float $amount, string $currency = 'USD'): self
    {
        return new self((int)($amount * 100), strtoupper($currency));
    }

    public static function fromCents(int $cents, string $currency = 'USD'): self
    {
        return new self($cents, strtoupper($currency));
    }

    public function getAmount(): float
    {
        return $this->amount / 100;
    }

    public function getCents(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        return new self((int)($this->amount * $multiplier), $this->currency);
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->getAmount(),
            'cents' => $this->amount,
            'currency' => $this->currency,
        ];
    }

    private function validate(): void
    {
        if (strlen($this->currency) !== 3) {
            throw new InvalidArgumentException(
                sprintf('Invalid currency code: %s', $this->currency)
            );
        }
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot operate on different currencies: %s and %s',
                    $this->currency,
                    $other->currency
                )
            );
        }
    }
}
