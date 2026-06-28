<?php

namespace Simplydigital\CaseManagement\Livewire\Supplier;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\Supplier;
class CreateSupplier extends ModalComponent
{

    public array $ui = [];
    public int|string|null $supplierId = null;
    public ?Supplier $supplier = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?string $telephone = null;
    public ?string $address_1 = null;
    public ?string $address_2 = null;
    public ?string $address_3 = null;
    public ?string $address_4 = null;
    public ?string $short_description = null;
    public ?string $url = null;
    public bool $active = true;


    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'telephone' => 'nullable|string|max:20',
        'address_1' => 'nullable|string|max:255',
        'address_2' => 'nullable|string|max:255',
        'address_3' => 'nullable|string|max:255',
        'address_4' => 'nullable|string|max:255',
        'url' => 'nullable|url',
        'active' => 'boolean',
        'short_description' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->ui = [
            'title' => 'Create Supplier',
            'confirmText' => 'Create',
        ];
        if ($this->supplierId) {
            $this->ui = [
                'title' => 'Update Supplier',
                'confirmText' => 'Update'
            ];
            $this->loadSupplier();
        }
    }

    public function loadSupplier()
    {
        if ($this->supplierId) {
            $caseSupplierServices = app('case-supplier-services');
            $this->supplier = $caseSupplierServices->getSupplierById($this->supplierId);
            $this->name = $this->supplier->name;
            $this->email = $this->supplier->email;
            $this->telephone = $this->supplier->telephone;
            $this->address_1 = $this->supplier->address_1;
            $this->address_2 = $this->supplier->address_2;
            $this->address_3 = $this->supplier->address_3;
            $this->address_4 = $this->supplier->address_4;      
            $this->url = $this->supplier->url;
            $this->active = $this->supplier->active;
            $this->short_description = $this->supplier->short_description;
        }
    }   

    public function save()
    {
        $this->validate();

        $caseSupplierServices = app('case-supplier-services');
        $input = [
            'name' => $this->name,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'address_3' => $this->address_3,
            'address_4' => $this->address_4,
            'url' => $this->url,
            'short_description' => $this->short_description,
            'active' => $this->active,
        ];

        if($this->supplierId){
            $caseSupplierServices->updateSupplier($this->supplierId, $input);
        } else {
            $caseSupplierServices->createSupplier($input);
        }

        $this->closeThisModal();
    }

    public function closeThisModal()
    {
        $this->dispatch('closeCreateSupplierModal');
    }
    public function render()
    {
        return view('casemanagement::livewire.supplier.create-supplier', [
            'ui_params' => $this->ui,
        ]);
    }
}   