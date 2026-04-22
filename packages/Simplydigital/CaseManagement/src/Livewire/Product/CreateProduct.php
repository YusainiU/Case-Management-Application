<?php

namespace Simplydigital\CaseManagement\Livewire\Product;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\CaseManagement\Enums\ProductTypes;

class CreateProduct extends ModalComponent
{

    public $productId;
    public $ui = [];
    public $name = '';
    public $product_code = '';
    public $short_description = '';
    public $product_type = '';
    public $make = '';
    public $model = '';
    public $version = '';
    public $active = true;   
    public $product_types = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'product_code' => 'nullable|string|max:255',
        'short_description' => 'nullable|string',
        'product_type' => 'required',
        'make' => 'nullable|string|max:255',
        'model' => 'nullable|string',
        'version' => 'nullable|string',
        'active' => 'boolean',
    ];

    public function mount()
    {
        $this->loadTypes();        
        if($this->productId){            
            $this->updateMode();
        }else{
            $this->createMode();
        }
    }

    public function createMode()
    {
        $this->ui['title'] = 'Create Product';
        $this->ui['confirmText'] = 'Create';
    }

    public function updateMode()
    {
        $this->ui['title'] = 'Update Product';
        $this->ui['confirmText'] = 'Update';
        $this->loadProduct();
    }

    public function loadTypes()
    {
        $this->product_types = ProductTypes::options();
    }

    public function loadProduct()
    {
        $caseProductServices = app('case-product-services');
        $product = $caseProductServices->getProductById($this->productId);
        if($product){
            $this->name = $product->name;
            $this->product_code = $product->product_code;
            $this->short_description = $product->short_description;
            $this->product_type = $product->type;
            $this->make = $product->make;
            $this->model = $product->model;
            $this->version = $product->version;
            $this->active = $product->active;            
        }
    }   

    public function resetAll()
    {

        $this->reset(
            'name',
            'product_code',
            'short_description',
            'product_type',
            'make',
            'model',
            'version',
            'active',
        );

    }

    public function closeThisModal()
    {
        $this->resetAll();
        if($this->productId){
            $this->dispatch('closeUpdateProductModal');
        }else{          
            $this->dispatch('closeCreateProductModal');
        }
    }
    
    public function save()
    {
        $this->validate();
        $input = [
            'name' => $this->name,
            'product_code' => $this->product_code,
            'short_description' => $this->short_description,
            'type' => $this->product_type,
            'make' => $this->make,
            'model' => $this->model,
            'version' => $this->version,
        ];
        $services = app('case-product-services');
        if($this->productId){
            $services->updateProduct($this->productId, $input);
        }else{
            $services->createProduct($input);
        }
        $this->closeThisModal();  
    }

    public function render()
    {
        return view('casemanagement::livewire.product.create-product',[
            'ui_params' => $this->ui,
        ]);
    }
}