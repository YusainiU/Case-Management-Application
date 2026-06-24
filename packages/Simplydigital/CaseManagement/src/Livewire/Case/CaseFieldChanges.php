<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Models\slaTask;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTableSpatieLogService;

class CaseFieldChanges extends Component
{
    use WithPagination;

    public ?tickets $case;

    public ?slaTask $slaTask;

    // public function tableActivities()
    // {
    //     $activities = new Collection();
    //     $service = app(CaseTableSpatieLogService::class);
    //     if($this->case){
    //         $activities =  $service->tableActivities($this->case)->get();
    //     }
    //     if($this->slaTask){
    //         $activities =  $service->tableActivities($this->slaTask)->get();
    //     }
    //     if(!$activities->isEmpty()) {
    //         return $service->flattenActivities($activities);
    //     }
    //     return $activities;
    // }

    public function tableActivities()
    {
        $service = app(CaseTableSpatieLogService::class);

        $activities = collect();

        if ($this->case) {
            $activities = $activities->merge(
                $service->tableActivities($this->case)->get()
            );
        }

        if ($this->slaTask) {
            $activities = $activities->merge(
                $service->tableActivities($this->slaTask)->get()
            );
        }
        $activities = $activities
            ->sortByDesc('created_at')
            ->values();

        return $activities->isNotEmpty()
            ? $service->flattenActivities($activities)
            : $activities;
    }

    public function buildUI()
    {
        return [
            'title' => 'Change Audit',
        ];
    }

    public function render()
    {
        $this->slaTask = $this->case->slaTasks;
        $acts = $this->tableActivities();
        $ui = $this->buildUI();

        return view('casemanagement::livewire.case.case-field-changes', [
            'activities' => $acts,
            'ui_params' => $ui,
        ]);
    }
}
