<?php

namespace Simplydigital\CaseManagement\Livewire\Asset;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\Asset;

class AssetProfile extends ModalComponent
{

    public string $title = 'Asset Profile';
    public int $assetId;
    public Asset $asset;
    public array $assetItems = [];


    public function mount(int $assetId)
    {
        $this->authorize('viewAny', Asset::class);
        $this->assetId = $assetId;
        $this->loadAsset();
        $this->buildProfileItems();
    }

    public function loadAsset()
    {
        $services = app('case-asset-services');
        $this->asset = $services->getAssetById($this->assetId);
    }

    public function buildProfileItems()
    {
        $services = app('case-asset-services');
        $items = $services->buildProfileItems($this->asset);
        $this->assetItems = $items;
    }

    public function closeThisModal()
    {
        $this->dispatch('close-profile-modal');   
    }    
        
    public function render()
    {
        return view('casemanagement::livewire.asset.asset-profile');
    }

}
