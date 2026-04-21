<?php

namespace Simplydigital\CaseManagement\Livewire\Product;

use LivewireUI\Modal\ModalComponent;
use PSpell\Config;

class CreateProduct extends ModalComponent
{

    public $productId;
    public $ui = [];
    public $name = '';
    public $product_code = '';
    public $short_description = '';
    public $type = '';
    public $make = '';
    public $model = '';
    public $version = '';
    public $active = true;   
    public $types = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'product_code' => 'required|string|max:255',
        'short_description' => 'nullable|string',
        'type' => 'required|string|max:255',
        'make' => 'required|string|max:255',
        'model' => 'nullable|string',
        'version' => 'nullable|string',
        'active' => 'boolean',
    ];

    public function mount()
    {
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
        $this->types = config('casemanagement.productTypes');
    }

    public function loadProduct()
    {
        $caseProductServices = app('case-product-services');
        $product = $caseProductServices->getProductById($this->productId);
        if($product){
            $this->name = $product->name;
            $this->product_code = $product->product_code;
            $this->short_description = $product->short_description;
            $this->type = $product->type;
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
            'type',
            'make',
            'model',
            'version',
            'active',
        );

    }

    public function closeThisModal()
    {
        $this->resetAll();
        $this->dispatch('closeCreateProductModal');
    }
    
    public function save()
    {
        $this->validate();
        $input = [
            'name' => $this->name,
            'product_code' => $this->product_code,
            'short_description' => $this->short_description,
            'type' => $this->type,
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