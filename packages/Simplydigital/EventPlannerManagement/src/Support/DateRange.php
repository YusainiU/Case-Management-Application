<?php

namespace Simplydigital\EventPlannerManagement\Support;

use DateTimeInterface;
use InvalidArgumentException;

final class DateRange
{
    public function __construct(
        public readonly DateTimeInterface $start,
        public readonly DateTimeInterface $end,
    ) {
        if ($this->end < $this->start) {
            throw new InvalidArgumentException('The date range end must be after the start date.');
        }
    }

    public static function from(string $start, string $end): self
    {
        return new self(
            new \DateTimeImmutable($start),
            new \DateTimeImmutable($end),
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->format(DATE_ATOM),
            'end' => $this->end->format(DATE_ATOM),
        ];
    }
}
