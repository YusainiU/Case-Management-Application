<?php

namespace Simplydigital\EventPlannerManagement\DTO;

use Carbon\CarbonInterface;
use InvalidArgumentException;

final class PlannerEvent
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly CarbonInterface $start,
        public readonly ?CarbonInterface $end = null,
        public readonly bool $allDay = false,
        public readonly string $type = 'default',
        public readonly ?string $url = null,
        public readonly ?string $description = null,
        public readonly ?string $colour = null,
        public readonly ?string $icon = null,
        public readonly array $metadata = [],
    ) {
        if ($this->end < $this->start) {
            throw new InvalidArgumentException('Planner events must end after they start.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start->toIso8601String(),
            'end' => $this->end?->toIso8601String(),
            'allDay' => $this->allDay,
            'type' => $this->type,
            'url' => $this->url,
            'description' => $this->description,
            'colour' => $this->colour,
            'icon' => $this->icon,
            'metadata' => $this->metadata,
        ];
    }
}
