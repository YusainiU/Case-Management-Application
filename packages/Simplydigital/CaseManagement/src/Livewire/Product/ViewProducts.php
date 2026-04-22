<?php

namespace Simplydigital\CaseManagement\Livewire\Product;

use Livewire\Component;
use Livewire\WithPagination;

class ViewProducts extends Component
{

    use WithPagination;
    public $title = 'Products';
    public $productId;
    public $product;
    public $filter;
    public $sortField;
    public $sortDirection;
    public $displayCreateModal = false;
    public $displayUpdateModal = false;
    public $displayProfileModal = false;
    protected $listeners = [
        'closeCreateProductModal' => 'closeCreateModal',
        'closeUpdateProductModal' => 'closeUpdateModal',
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

    public function loadProducts()
    {
        $caseProductServices = app('case-product-services');
        if($this->sortField){
            $caseProductServices->sortByField($this->sortField, $this->sortDirection);
        }
        if($this->filter){
            $caseProductServices->filterResult($this->filter);
        }        
        $query = $caseProductServices->getActiveProducts();
        return $query;
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

    public function resetAll(){
        $this->reset(
            'filter', 
            'sortField', 
            'sortDirection',
            'displayCreateModal',
            'displayUpdateModal',
            'displayProfileModal',
        );
    }

    public function openCreateModal()
    {
        $this->resetAll();
        $this->displayCreateModal = true;
    }

    public function openUpdateModal($productId)
    {
        $this->resetAll();
        $this->productId = $productId;
        $this->displayUpdateModal = true;
    }
    
    public function openProfileModal($productId)
    {
        $this->resetAll();
        $this->productId = $productId;
        $this->displayProfileModal = true;
    }

    public function render()
    {
        $products = $this->loadProducts()->paginate(10);
        return view('casemanagement::livewire.product.view-products', [
            'products' => $products,
        ]);
    }
}
