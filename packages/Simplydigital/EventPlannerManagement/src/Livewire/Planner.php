<?php

namespace Simplydigital\EventPlannerManagement\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\EventPlannerManagement\Services\PlannerService;

class Planner extends Component
{
    public string $view = 'dayGridMonth';

    public string $date;

    public bool $showEventModal = false;

    public ?tickets $case = null;

    /**
     * @var array<string, string>
     */
    protected $listeners = [
        'closeProfileModal' => 'closeEventModal',
    ];

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function closeEventModal(): void
    {
        $this->showEventModal = false;
    }

    public function openEventModal(int $caseId): void
    {
        $this->case = tickets::find($caseId);
        $this->showEventModal = true;
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
    
        $events = $planner
            ->events($start, $end, auth()->id())
            ->map(fn ($event) => $event->toArray())
            ->all();

        return $events;
    }

    public function previous()
    {
        $this->date = Carbon::parse($this->date)->subMonth()->toDateString();
    }

    public function next()
    {
        $this->date = Carbon::parse($this->date)->addMonth()->toDateString();
    }

    public function today()
    {
        $this->date = now()->toDateString();
    }

    public function getCalendarDays(): array
    {
        $date = Carbon::parse($this->date);
        $start = $date->copy()->startOfMonth()->startOfWeek();
        $end = $date->copy()->endOfMonth()->endOfWeek();
        $days = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $days[] = [
                'date' => $day->copy(),
                'isCurrentMonth' => $day->month === $date->month,
                'isToday' => $day->isToday(),
            ];
        }

        return $days;
    }

    public function render(PlannerService $planner)
    {
        $events = $this->getEvents($planner);

        return view(
            'eventplanner::livewire.planner',
            [
                'events' => $events,
                'calendarDays' => $this->getCalendarDays(),
            ]
        );
    }
}
