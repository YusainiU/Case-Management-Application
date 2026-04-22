<?php

namespace Simplydigital\CaseManagement\Livewire\Product;

use LivewireUI\Modal\ModalComponent;
class ProductProfile extends ModalComponent
{
    public $title = 'Product Profile';
    public $productId;
    public $product;
    public $productItems = [];

    public function mount($productId)
    {
        $this->productId = $productId;
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
        $this->dispatch('closeProfileModal');   
    }

    public function render()
    {
        return view('casemanagement::livewire.product.product-profile');
    }
}