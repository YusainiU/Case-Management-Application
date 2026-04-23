<?php

namespace Simplydigital\CaseManagement\Livewire\Supplier;

use LivewireUI\Modal\ModalComponent;
class CreateSupplier extends ModalComponent
{

    public $ui = [];
    public $supplierId;
    public $supplier;
    public $name;
    public $email;
    public $telephone;
    public $address_1;
    public $address_2;
    public $address_3;
    public $address_4;
    public $url;
    public $active = true;


    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:suppliers,email',
        'telephone' => 'nullable|string|max:20',
        'address_1' => 'nullable|string|max:255',
        'address_2' => 'nullable|string|max:255',
        'address_3' => 'nullable|string|max:255',
        'address_4' => 'nullable|string|max:255',
        'url' => 'nullable|url',
        'active' => 'boolean',
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
            'active' => $this->active,
        ];

        if($this->supplierId){
            $caseSupplierServices->updateSupplier($this->supplierId, $input);
        } else {
            $caseSupplierServices->createSupplier($input);
        }

        $this->dispatch('closeCreateSupplierModal');
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