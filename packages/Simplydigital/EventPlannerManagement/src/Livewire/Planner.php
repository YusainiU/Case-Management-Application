<?php

namespace Simplydigital\EventPlannerManagement\Livewire;


use Livewire\Component;
use Carbon\Carbon;
use Simplydigital\EventPlannerManagement\Services\PlannerService;

class Planner extends Component
{

    public string $view = 'dayGridMonth';

    public string $date;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function getEvents(
        PlannerService $planner
    ): array {
        $date = Carbon::parse($this->date);

        if ($this->view === 'timeGridWeek') {
            $start = $date->copy()->startOfWeek();
            $end = $date->copy()->endOfWeek();
        } else {
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();
        }

        return $planner
            ->events(
                $start,
                $end,
                auth()->id()
            )
            ->map(
                fn ($event) => $event->toArray()
            )
            ->values()
            ->all();
    }

    public function render()
    {
        return view(
            'eventplanner::livewire.planner'
        );
    }    
}
