<?php

namespace Simplydigital\EventPlannerManagement\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Simplydigital\EventPlannerManagement\Contracts\PlannerEventRepository as PlannerEventRepositoryContract;
use Simplydigital\EventPlannerManagement\DTO\PlannerEvent as PlannerEventDto;
use Simplydigital\EventPlannerManagement\Models\PlannerEvent;
use Simplydigital\EventPlannerManagement\Support\DateRange;

class PlannerEventRepository implements PlannerEventRepositoryContract
{
    public function all(?DateRange $range = null): Collection
    {
        $query = PlannerEvent::query();

        if ($range !== null) {
            $query->where(function ($query) use ($range) {
                $query->whereBetween('starts_at', [$range->start, $range->end])
                    ->orWhereBetween('ends_at', [$range->start, $range->end]);
            });
        }

        return $query->orderBy('starts_at')->get();
    }

    public function find(string $id): ?Model
    {
        return PlannerEvent::query()->find($id);
    }

    public function save(PlannerEventDto $event): Model
    {
        $model = PlannerEvent::query()->find($event->id) ?? new PlannerEvent();

        $model->fill([
            'title' => $event->title,
            'description' => $event->description,
            'type' => $event->type,
            'status' => $event->status,
            'starts_at' => $event->startsAt,
            'ends_at' => $event->endsAt,
            'location' => $event->location,
            'metadata' => $event->metadata,
        ]);

        $model->save();

        return $model;
    }

    public function delete(string $id): bool
    {
        $event = PlannerEvent::query()->find($id);

        if ($event === null) {
            return false;
        }

        return (bool) $event->delete();
    }
}
