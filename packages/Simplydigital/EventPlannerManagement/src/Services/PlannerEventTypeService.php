<?php

namespace Simplydigital\EventPlannerManagement\Services;

class PlannerEventTypeService
{
    public function get(string $type): array
    {
        return config("planner.event_types.{$type}",[]);
    }

    public function colour(string $type): ?string
    {
        return $this->get($type)['colour'] ?? null;
    }

    public function text(string $type): ?string
    {
        return $this->get($type)['text'] ?? null;
    }

    public function label(string $type): string
    {
        return $this->get($type)['label']
            ?? ucfirst(str_replace('_', ' ', $type));
    }

    public function icon(string $type): ?string
    {
        return $this->get($type)['icon'] ?? null;
    }

    public function exists(string $type): bool
    {
        return config("planner.event_types.{$type}") !== null;
    }

    public function all(): array
    {
        return config('planner.event_types',[]);
    }
}