<?php

namespace Simplydigital\CaseManagement\Livewire\Asset;

use Livewire\Component;
use Livewire\WithPagination;

class ViewAssets extends Component
{

    use WithPagination;
    public $assetId;
    public $asset;
    public $customerId;
    public $filter;
    public $sortField;
    public $sortDirection;
    public $displayCreateModal = false;
    public $displayUpdateModal = false;
    public $displayProfileModal = false;
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
        $this->displayProfileModal = false;
    }

    public function sortByField($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
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
        ]);
    }

    public function openCreateModal()
    {
        $this->resetAll();
        $this->displayCreateModal = true;
    }

    public function openUpdateModal($assetId)
    {
        $this->resetAll();
        $this->assetId = $assetId;
        $this->displayUpdateModal = true;
    }
    
    public function openProfileModal($assetId)
    {
        $this->resetAll();
        $this->assetId = $assetId;
        $this->displayProfileModal = true;
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
