<?php

namespace Simplydigital\CaseManagement\Livewire\Product;

use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Models\Product;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ViewProducts extends Component
{

    use WithPagination;
    public string $title = 'Products';
    public ?int $productId = null;
    public ?product $product = null;
    public ?string $filter = null;
    public ?string $sortField = null;
    public ?string $sortDirection = null;
    public $displayCreateModal = false;
    public $displayUpdateModal = false;
    public $displayProfileModal = false;
    protected $listeners = [
        'closeCreateProductModal' => 'closeCreateModal',
        'closeUpdateProductModal' => 'closeUpdateModal',
        'closeProfileModal' => 'closeProfileModal',
    ];

    public function mount()
    {
        $this->authorize('view', Product::class);
    }


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

    public function sortByField(string $field)
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

    public function openUpdateModal(int|string $productId)
    {
        $this->resetAll();
        $this->productId = (int) $productId;
        $this->displayUpdateModal = true;
    }
    
    public function openProfileModal(int|string $productId)
    {
        $this->resetAll();
        $this->productId = (int) $productId;
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
