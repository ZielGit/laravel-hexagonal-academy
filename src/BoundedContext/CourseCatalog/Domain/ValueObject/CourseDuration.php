<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Course Duration
 *
 * Total estimated duration in minutes
 */
final class CourseDuration
{
    private const MIN_DURATION = 5; // 5 minutes minimum

    private const MAX_DURATION = 100000; // ~1666 hours max

    private function __construct(
        private readonly int $minutes
    ) {
        $this->validate();
    }

    public static function fromMinutes(int $minutes): self
    {
        return new self($minutes);
    }

    public static function fromHours(float $hours): self
    {
        return new self((int) ($hours * 60));
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getHours(): float
    {
        return round($this->minutes / 60, 2);
    }

    public function getFormattedDuration(): string
    {
        $hours = floor($this->minutes / 60);
        $mins = $this->minutes % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $mins);
        }

        return sprintf('%d minutes', $mins);
    }

    private function validate(): void
    {
        if ($this->minutes < self::MIN_DURATION) {
            throw new InvalidArgumentException(
                sprintf('Course duration must be at least %d minutes', self::MIN_DURATION)
            );
        }

        if ($this->minutes > self::MAX_DURATION) {
            throw new InvalidArgumentException(
                sprintf('Course duration cannot exceed %d minutes', self::MAX_DURATION)
            );
        }
    }
}
