<?php
namespace Simplydigital\EngineerManagement\Livewire;

use Simplydigital\EngineerManagement\Models\Certificates;
use LivewireUI\Modal\ModalComponent;
use PSpell\Config;

class AddUpdateCertificateModalComponent extends ModalComponent
{

    public $certificateId = null;
    public $name = '';
    public $type = '';
    public $issued_by = '';
    public $certificates;
    public $certificateTypes = [];
    public $certificateIssuers = [];
    public $active = true;
    public $modalTitle = 'Add Certificate';
    public $modalAction = 'save';
    public $modalClose = 'close';
    public $showList = false;
    public $rules = [
        'name' => 'required|string|max:255',
        'type' => 'nullable|string|max:255',
        'issued_by' => 'nullable|string|max:1000',
        'active' => 'boolean',
    ];

    public function maxWidth(): string
    {
        // Supported: 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'
        return '6xl';
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'issued_by' => 'nullable|string|max:1000',
            'active' => 'boolean',
        ];
    }

    public function mount()
    {
        if ($this->certificateId) {            
            $this->loadCertificate($this->certificateId);
        }
        $this->certificateTypes = config('engineer-management.certification_types');
        $this->certificateIssuers = config('engineer-management.certificate_issuers');
    }

    
    public function loadCertificate($certificateId)
    {
        $certificate = Certificates::find($certificateId);
        if($certificate->exists()){
            $this->modalTitle = 'Update Certificate';
            $this->name = $certificate->name;
            $this->type = $certificate->type;
            $this->active = $certificate->active;
            $this->issued_by = $certificate->issued_by;
        }   
    }

    public function save()
    {
        $this->validate();    
        if ($this->certificateId) {
            $certificate = Certificates::find($this->certificateId);
            $certificate->update([
                'name' => $this->name, 
                'type' => $this->type,
                'issued_by' => $this->issued_by,
                'active' => $this->active,
            ]);
        } else {
            Certificates::create([
                'number' => null,
                'name' => $this->name, 
                'type' => $this->type,
                'issued_by' => $this->issued_by,
                'active' => $this->active,
            ]);
        }
        $this->dispatch('certificateSaved');
    }
    
    public function close()
    {
        $this->resetForm();
        $this->dispatch('closeModal');          
    }

    public function resetForm()
    {
        $this->certificateId = null;
        $this->name = '';
        $this->type = '';
        $this->issued_by = '';
        $this->active = true;
        $this->modalTitle = 'Add Certificate';
        $this->modalAction = 'save';
    }

    public function render()
    {
        return view('engineermanagement::livewire.add-update-certificate-modal-component');
    }


}
                
