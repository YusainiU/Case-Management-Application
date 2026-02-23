<?php

namespace Simplydigital\EngineerManagement\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Simplydigital\EngineerManagement\Models\EngineerManagement;
use Simplydigital\EngineerManagement\Models\Certificates;
use Simplydigital\EngineerManagement\Models\Skills;
class EngineersSummaryComponent extends Component
{

    public $engineerId;
    public $skillId;
    public $certificationId;
    public $engineers;
    public $engineer;
    public $skills;
    public $certifications;
    public $selectedEngineer = false;
    public $selectedSkill = false;
    public $selectedCertification = false;
    public $displayFilterOptions = true;

    public function mount()
    {
        $this->initialise();
    }

    public function initialise()
    {
        $this->engineers = $this->getAllActiveEngineers();
        $this->skills = $this->getAllSkills();
        $this->certifications = $this->getAllCertifications();      
    }   

    #[On('closeEngineerSummaryComponent')]
    public function closeFilter()
    {
        $this->resetSelection();
    }

    public function getAllActiveEngineers()
    {
        return EngineerManagement::where('active', true)->get();
    }

    public function getAllCertifications()
    {
        return Certificates::where('active', true)->get();
    }

    public function getSelectedEngineer()
    {
        return EngineerManagement::find($this->engineerId);
    }

    public function getAllSkills()
    {
        return Skills::get();
    }

    public function getEngineersBySkillId()
    {
        return EngineerManagement::with('skills')
            ->whereHas('skills', function ($query) {
                $query->where('active', true)
                      ->where('id', $this->skillId);
            })
            ->get();
    }

    public function getEngineersByCertificationId()
    {
        return EngineerManagement::with('certifications')
            ->whereHas('certifications', function ($query) {
                $query->where('active', true)
                      ->where('id', $this->certificationId);
            })
            ->get();
    }

    public function selectEngineer($id)
    {
        $this->resetSelection();
        $this->displayFilterOptions = false;
        $this->selectedEngineer = true;
        $this->engineerId = $id;
        $this->engineer = $this->getSelectedEngineer();
    }

    public function selectSkill($skill)
    {
        $this->resetSelection();
        $this->displayFilterOptions = false;
        $this->selectedSkill = true;
        $this->skillId = $skill;
    }

    public function selectCertification($id)
    {
        $this->resetSelection();
        $this->displayFilterOptions = false;
        $this->selectedCertification = true;
        $this->certificationId = $id;
    }

    public function resetSelection()
    {
        $this->selectedEngineer = false;
        $this->selectedSkill = false;
        $this->selectedCertification = false;
        $this->engineerId = null;
        $this->skillId = null;
        $this->certificationId = null;
        $this->displayFilterOptions = true;
    }
    
    public function render()
    {
        return view('engineermanagement::livewire.engineers-summary-component');
    }   

}


