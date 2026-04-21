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
