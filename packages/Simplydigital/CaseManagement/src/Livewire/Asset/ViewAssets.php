<?php

namespace Simplydigital\CaseManagement\Livewire\Asset;

use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Models\Asset;

class ViewAssets extends Component
{

    use WithPagination;
    public string $title = 'Assets';
    public ?int $assetId = null;
    public Asset $asset;
    public ?int $productId = null;
    public ?int $customerId = null;
    public ?int $supplierId = null;
    public ?string $filter = null;
    public ?string $sortField = null;
    public ?string $sortDirection = null;
    public bool $displayCreateModal = false;
    public bool $displayUpdateModal = false;
    public bool $displayProfileModal = false;
    public bool $displayProductProfile = false;
    public bool $displayCustomerProfile = false;
    public bool $displaySupplierProfile = false;

    /**
    * @var array<string, string>
    */
    protected $listeners = [
        'closeCreateAssetModal' => 'closeCreateModal',
        'closeUpdateAssetModal' => 'closeUpdateModal',
        'closeProfileModal' => 'closeProfileModal',
    ];

    public function closeCreateModal()
    {
        $this->displayCreateModal = false;  
    }

    public function closeUpdateModal()
    {        
        $this->displayUpdateModal = false;
    }

    public function closeProfileModal()
    {        
        $this->resetProfileModal();
    }

    public function sortByField(?string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetProfileModal()
    {
        $this->reset([
            'displayProfileModal',
            'displayProductProfile',
            'displayCustomerProfile',
            'displaySupplierProfile',
        ]);
    }

    public function resetAll()
    {
        $this->reset([
            'filter', 
            'sortField', 
            'sortDirection',
            'displayCreateModal',
            'displayUpdateModal',
            'displayProfileModal',
            'displayProductProfile',
            'displayCustomerProfile',
            'displaySupplierProfile',            
        ]);
    }

    public function openCreateModal()
    {
        $this->resetAll();
        $this->displayCreateModal = true;
    }

    public function openUpdateModal(int $assetId)
    {
        $this->resetAll();
        $this->assetId = $assetId;
        $this->displayUpdateModal = true;
    }
    
    public function openProfileModal(int $assetId)
    {
        $this->resetAll();
        $this->assetId = $assetId;
        $this->displayProfileModal = true;
    }

    public function openProductProfileModal(int|string $productId)
    {
        $this->resetAll();
        $this->productId = (int) $productId;
        $this->displayProductProfile = true;
    }

    public function openCustomerProfileModal(int|string $customerId)
    {
        $this->resetAll();
        $this->customerId = (int) $customerId;
        $this->displayCustomerProfile = true;
    }
    
    public function openSupplierProfileModal(int|string $supplierId)
    {
        $this->resetAll();
        $this->supplierId = (int) $supplierId;
        $this->displaySupplierProfile = true;
    }       

    public function loadAssets()
    {
        $caseAssetServices = app('case-asset-services');
        if($this->customerId) {
            $caseAssetServices->setCustomer($this->customerId);
        }
        if($this->sortField){
            $caseAssetServices->sortByField($this->sortField, $this->sortDirection);
        }
        if($this->filter){
            $caseAssetServices->filterResult($this->filter);
        }        
        $query = $caseAssetServices->getActiveAssets();
        return $query;
    }

    public function render()
    {
        $assets = $this->loadAssets()->paginate(10);
        return view('casemanagement::livewire.asset.view-assets', [
            'assets' => $assets,
        ]);
    }
}
