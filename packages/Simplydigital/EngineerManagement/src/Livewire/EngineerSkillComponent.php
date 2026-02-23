<?php

Namespace Simplydigital\EngineerManagement\Livewire;
use Livewire\Component;
use Simplydigital\EngineerManagement\Models\EngineerSkills;
use Simplydigital\EngineerManagement\Models\Skills;
use Livewire\Attributes\On;

class EngineerSkillComponent extends Component
{

    public $skillId;
    public $engineerId;
    public $engineerSkillId; 
    public $engineersWithSkill;
    public $selectedSkill;
    public $displayEngineerSkillDetails = false;

    public function mount()
    {
        $this->initalise();   
    }

    public function initalise()
    {
        $this->engineersWithSkill = $this->getEngineerWithSkill();
        $this->selectedSkill = $this->getSkillDetails();
    }

    #[On('closedAddUpdateEngineerSkillModalComponent')]
    public function refreshEngineerCertifications()
    {
        $this->resetSkillDetails();        
    }

    public function getSkillDetails()
    {
        return Skills::find($this->skillId);
    }

    public function getEngineerWithSkill()
    {
        return EngineerSkills::where('skill_id', $this->skillId)->get();
    }

    public function getEngineerSkillById()
    {
        return EngineerSkills::find($this->engineerSkillId);
    }

    public function closeComponent()
    {
        $this->dispatch('closeEngineerSummaryComponent');
    }

    public function resetSkillDetails()
    {
        $this->engineerSkillId = '';
        $this->displayEngineerSkillDetails = false;
        $this->initalise();
    }

    public function openEngineerSkill($skillId, $engineerId)
    {
        $this->engineerSkillId = $skillId;
        $this->engineerId = $engineerId;
        $this->displayEngineerSkillDetails = true;
    }

    public function render()
    {
        return view('engineermanagement::livewire.engineer-skill-component');
    }
}
