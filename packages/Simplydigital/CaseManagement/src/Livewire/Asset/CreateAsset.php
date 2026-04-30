<?php

namespace Simplydigital\CaseManagement\Livewire\Asset;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\Asset;

class CreateAsset extends ModalComponent
{
    public ?int $assetId = null;
    public ?int $customerId = null;

    public Asset $asset;

    public array $ui = [];

    public array $customers = [];

    public array $products = [];

    public array $suppliers = [];

    public array $form = [];

    public string $closureEvent = 'closeCreateAssetModal';

    protected $rules = [
        'form.customer_id' => 'required',
        'form.product_id' => 'required',
        'form.supplier_id' => 'required',
        'form.short_description' => 'required|string|max:255',
        'form.buy_price' => 'nullable|numeric|decimal:0,2|min:0',
        'form.sell_price' => 'nullable|numeric|decimal:0,2|min:0',
        'form.license_number' => 'nullable|string',
        'form.active' => 'boolean',
        'form.notes' => 'nullable|string|max:255',
        'form.location' => 'nullable|string|max:255',
        'form.technical_specifications' => 'nullable|string|max:255',
        'form.mac_address' => 'nullable|mac_address',
        'form.ip_address' => 'nullable|ip',
    ];

    protected $validationAttributes = [
        'form.short_description' => 'description',
        'form.customer_id' => 'customer',
        'form.product_id' => 'product',
        'form.supplier_id' => 'supplier',
        'form.buy_price' => 'buy price',
        'form.sell_price' => 'sell price',
        'form.license_number' => 'license number',
        'form.active' => 'active',
        'form.notes' => 'notes',
        'form.location' => 'location',
        'form.technical_specifications' => 'techical specifications',
        'form.mac_address' => 'mac address',
        'form.ip_address' => 'ip address',
    ];

    public function mount()
    {
        $this->ui = [
            'title' => 'Create Asset',
            'confirmText' => 'Create',
            'widthsize' => '6xl',
            'cancelThisWith' => 'closeThisModal',
            'confirm' => 'save',
        ];

        $this->declareFormProperties();

        if ($this->assetId) {
            $this->loadAsset();
        }

        $this->loadOptions();
    }

    public function loadOptions()
    {
        $customerService = app('case-customer-services');
        $this->customers = $customerService->buildOptionsForCustomerSelect();
        $supplierService = app('case-supplier-services');
        $this->suppliers = $supplierService->buildOptionsForSupplierSelect();
        $productService = app('case-product-services');
        $this->products = $productService->buildOptionsForProductSelect();
    }

    public function loadAsset()
    {
        $service = app('case-asset-services');
        $this->asset = $service->getAssetById($this->assetId);
        $this->ui['title'] = 'Update Asset';
        $this->ui['confirmText'] = 'Update';
        $this->closureEvent = 'closeUpdateAssetModal';
        $this->loadAssetForm();
    }

    public function declareFormProperties()
    {
        $this->form = [
            'short_description' => '',
            'customer_id' => $this->customerId ?? null,
            'product_id' => null,
            'supplier_id' => null,
            'buy_price' => null,
            'sell_price' => null,
            'license_number' => null,
            'active' => true,
            'notes' => null,
            'location' => null,
            'technical_specifications' => null,
            'mac_address' => null,
            'ip_address' => null,
        ];

    }

    public function loadAssetForm()
    {
        $this->form = [
            'short_description' => $this->asset->short_description,
            'customer_id' => $this->asset->customer_id,
            'product_id' => $this->asset->product_id,
            'supplier_id' => $this->asset->supplier_id,
            'buy_price' => $this->asset->buy_price,
            'sell_price' => $this->asset->sell_price,
            'license_number' => $this->asset->license_number,
            'active' => $this->asset->active,
            'notes' => $this->asset->notes,
            'location' => $this->asset->location,
            'technical_specifications' => $this->asset->technical_specifications,
            'mac_address' => $this->asset->mac_address,
            'ip_address' => $this->asset->ip_address,            
        ];
    }

    public function save()
    {
    
        $this->validate();
        $service = app('case-asset-services');
        if ($this->assetId) {
            $service->updateAsset($this->asset, $this->form);
        } else {
            $service->createAsset($this->form);
        }
        $this->dispatch($this->closureEvent);
    }

    public function resetAll()
    {
        $this->reset([
            'form',
        ]);
    }

    public function closeThisModal()
    {
        $this->resetAll();
        $this->dispatch($this->closureEvent);
    }

    public function render()
    {
        return view('casemanagement::livewire.asset.create-asset', [
            'ui_params' => $this->ui,
        ]);
    }
}
