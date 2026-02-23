<?php

Namespace Simplydigital\EngineerManagement\Livewire;

use Livewire\Component;
use Simplydigital\EngineerManagement\Models\EngineerManagement;
use Livewire\Attributes\On;

Class EngineerDetailComponent extends Component
{
    public $engineerId;
    public $engineer;
    public $engSkills;
    public $engCertifications;
    public $selectedEngineerCertificateId;
    public $selectedEngineerSkillId;
    public $openCertificateModal = false;
    public $openSkillModal = false;

    public function mount()
    {
        $this->initialise();
    }

    public function initialise()
    {
        $this->engineer = $this->getEngineerDetail();
        $this->engSkills = $this->getEngineerSkills();
        $this->engCertifications = $this->getEngineerCertifications();  
    }

    #[On('closedAddUpdateEngineerCertificateModalComponent')]
    public function refreshEngineerCertifications()
    {
        $this->resetModalComponent();        
    }
    
    #[On('closedAddUpdateEngineerSkillModalComponent')]
    public function refreshEngineerSkills()
    {
        $this->resetModalComponent();
    }

    public function closeComponent()
    {
        $this->dispatch('closeEngineerSummaryComponent');
    }

    public function resetModalComponent()
    {
        $this->selectedEngineerSkillId = null;
        $this->selectedEngineerCertificateId = null;
        $this->openCertificateModal = false;
        $this->openSkillModal = false;
    }

    public function openCertificate($certificateId)
    {        
        $this->resetModalComponent();
        $this->selectedEngineerCertificateId = $certificateId;
        $this->openCertificateModal = true;
    }

    public function openSkill($skillId)
    {
        $this->resetModalComponent();
        $this->selectedEngineerSkillId = $skillId;
        $this->openSkillModal = true;
    }

    public function getEngineerDetail()
    {
        return EngineerManagement::find($this->engineerId);
    }   

    public function getEngineerSkills()
    {
        $skills = EngineerManagement::where('id', $this->engineerId)
            ->with('skills')
            ->whereHas('skills', function ($query) {
                $query->where('active', true);
            })
            ->get()->first();
            return $skills ? $skills->skills : collect();
    }

    public function getEngineerCertifications()
    {
        $cert =  EngineerManagement::where('id', $this->engineerId)
            ->with('certifications')
            ->whereHas('certifications', function ($query) {
                $query->where('active', true);
            })
            ->get()->first();
        return $cert ? $cert->certifications : collect();
    }   

    public function render()
    {
        return view('engineermanagement::livewire.engineer-detail-component');
    }

}
