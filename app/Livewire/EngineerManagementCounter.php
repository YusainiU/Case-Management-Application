<?php
Namespace App\Livewire;
use Livewire\Component;
use Simplydigital\EngineerManagement\Services\EngineerManagementServices;
class EngineerManagementCounter extends Component
{

    public $selectedCount = 0;
    public $selectCounter = 'allEngineers';
    public $counterLabel = '';

    public function mount(EngineerManagementServices $engineerManagementService)
    {
        switch($this->selectCounter)
        {
            case 'allEngineers':
                $this->countAllEngineers($engineerManagementService);
                $this->counterLabel = 'Total Engineers';
                break;
            case 'allCertificates':
                $this->countAllCertificates($engineerManagementService);
                $this->counterLabel = 'Total Certificates';
                break;
            case 'allSkills':
                $this->countAllSkills($engineerManagementService);
                $this->counterLabel = 'Total Skills';
                break;
            case 'expiredCertifications':
                $this->countTotalExpiredCertifications($engineerManagementService);
                $this->counterLabel = 'Expired Certifications';
                break;
            default:
                $this->selectedCount = 0;
                break;
        }
        
    }

    public function countTotalExpiredCertifications($engineerManagementService)
    {
        $this->selectedCount = $engineerManagementService->getTotalExpiredCertificationsCount();
    }

    public function countAllEngineers($engineerManagementService)
    {
        $e = $engineerManagementService->getAllEngineers();
        $this->selectedCount = $e->count();
    }

    public function countAllCertificates($engineerManagementService)
    {
        $c = $engineerManagementService->getAllCertificates();
        $this->selectedCount = $c->count();
    }

    public function countAllSkills($engineerManagementService)
    {
        $s = $engineerManagementService->getAllSkills();
        $this->selectedCount = $s->count();
    }

    public function render()
    {
        return view('livewire.engineer-management-counter');
    }
}