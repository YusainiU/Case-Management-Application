<?php

namespace Simplydigital\CaseManagement\Livewire\Product;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Models\Product;
class ProductProfile extends ModalComponent
{
    public string $title = 'Product Profile';
    public ?int $productId = null;
    public ?product $product;
    public array $productItems = [];

    public function mount()
    {
        $this->authorize('viewAny', Product::class);
        $caseProductServices = app('case-product-services');
        $this->product = $caseProductServices->getProductById($this->productId);
        $this->buildProfileItems();
    }

    public function buildProfileItems()
    {
        $caseProductServices = app('case-product-services');
        $items = $caseProductServices->buildProfileItems($this->product);
        $this->productItems = $items;
    }
    
    public function closeThisModal()
    {
        $this->dispatch('close-profile-modal');   
    }

    public function render()
    {
        return view('casemanagement::livewire.product.product-profile');
    }
}