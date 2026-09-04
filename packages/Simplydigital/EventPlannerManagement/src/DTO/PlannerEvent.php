<?php

namespace Simplydigital\EventPlannerManagement\DTO;

use Carbon\CarbonInterface;
use Simplydigital\EventPlannerManagement\Services\PlannerEventTypeService;

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
        public readonly ?string $text = null,
        public readonly ?string $icon = null,
        public readonly array $metadata = [],
    ) {}

    public function getColour(): ?string
    {
        if ($this->colour !== null) {
            return $this->colour;
        }

        return app(
            PlannerEventTypeService::class
        )->colour($this->type);
    }

    public function getTextColour(): ?string
    {
        if( $this->text !== null) {
            return $this->text;
        }
        return app(
            PlannerEventTypeService::class
        )->text($this->type);
    }

    public function getIcon(): ?string
    {
        if ($this->icon !== null) {
            return $this->icon;
        }

        return app(
            PlannerEventTypeService::class
        )->icon($this->type);
    }

    public function getLabel(): string
    {
        return app(
            PlannerEventTypeService::class
        )->label($this->type);
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
            'metadata' => $this->metadata,
            'colour' => $this->getColour(),
            'text' => $this->getTextColour(),
            'icon' => $this->getIcon(),            
        ];
    }

    public function toFullCalendar(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start
                ->toIso8601String(),
            'end' => $this->end
                ?->toIso8601String(),
            'allDay' => $this->allDay,
            'url' => $this->url,
            'backgroundColor' => $this->getColour(),
            'borderColor' => $this->getColour(),
            'extendedProps' => [
                'type' => $this->type,
                'description' => $this->description,
                'icon' => $this->getIcon(),
                'metadata' => $this->metadata,
            ],
        ];
    }
}
