<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Simplydigital\CaseManagement\Services\CaseTableSpatieLogService;
use Simplydigital\CaseManagement\Models\tickets;

class CaseFieldChanges extends Component
{
    use WithPagination;
    public ?tickets $case; 

    public function tableActivities(): Collection
    {
        $activities = new Collection();
        $service = app(CaseTableSpatieLogService::class);
        $activities =  $service->tableActivities($this->case)->paginate(50);
        if(!$activities->isEmpty()) {            
            return $service->flattenActivities($activities);
        }
        return $activities;
    }

    public function render()
    {
        $acts = $this->tableActivities();
        return view('casemanagement::livewire.case.case-field-changes',[
            'activities' => $acts,
        ]);
    }

}
