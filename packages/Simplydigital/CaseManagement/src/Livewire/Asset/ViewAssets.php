<?php

namespace Simplydigital\CaseManagement\Livewire\Asset;

use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Models\Asset;

class ViewAssets extends Component
{

    use WithPagination;
    public string $title = 'Assets';
    public int $assetId;
    public Asset $asset;
    public ?int $customerId = null;
    public ?string $filter = null;
    public ?string $sortField = null;
    public ?string $sortDirection = null;
    public bool $displayCreateModal = false;
    public bool $displayUpdateModal = false;
    public bool $displayProfileModal = false;

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
        $this->displayProfileModal = false;
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
