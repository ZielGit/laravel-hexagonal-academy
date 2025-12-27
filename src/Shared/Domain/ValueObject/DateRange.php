<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObject;

use InvalidArgumentException;

/**
 * DateRange Value Object
 *
 * Represents a period between two dates
 */
final class DateRange
{
    private function __construct(
        private readonly \DateTimeImmutable $startDate,
        private readonly \DateTimeImmutable $endDate
    ) {
        $this->validate();
    }

    public static function fromDates(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate
    ): self {
        return new self($startDate, $endDate);
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getDurationInDays(): int
    {
        return $this->startDate->diff($this->endDate)->days;
    }

    public function contains(\DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }

    public function overlaps(DateRange $other): bool
    {
        return $this->startDate <= $other->endDate
            && $this->endDate >= $other->startDate;
    }

    private function validate(): void
    {
        if ($this->startDate > $this->endDate) {
            throw new InvalidArgumentException(
                'Start date must be before or equal to end date'
            );
        }
    }
}
