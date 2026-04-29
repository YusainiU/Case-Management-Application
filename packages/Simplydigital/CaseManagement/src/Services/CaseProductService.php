<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CaseProductService
{
    public ?string $sortBy = null;
    public string $sortDirection = 'asc';
    public string|int|null $filter = null;

    public function filterResult(string|int $filter)
    {
        $this->filter = $filter;
    }

    public function sortByField(string $sortBy, ?string $sortDirection = null)
    {
        $this->sortBy = $sortBy;
        if ($sortDirection) {   
            $this->sortDirection = $sortDirection;
        }
    }

    public function checkIfDate(string $value)
    {
        return is_string($value) && Carbon::hasFormat($value, 'Y-m-d');
    }

    public function buildOptionsForProductSelect()
    {
        return Product::where('active', true)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function buildProfileItems(Product $product)
    {
        $items = [];
        foreach ($product->getAttributes() as $key => $value) {

            if ($this->checkIfDate($value)) {
                $value = Carbon::parse($value)->format('d/m/Y h:i:s');
            }
            if ($key == 'active') {
                $value = $value ? 'Yes' : 'No';
            }
            $items[] = [
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
            ];
        }
        return $items;
    }

    public function buildFilter(Builder $query)
    {
        if ($this->filter) {
            $filterValue = '%' . $this->filter . '%';
            $query->where(function ($q) use ($filterValue) {
                $q->orWhere('product_code', 'like', $filterValue)
                    ->orWhere('number', 'like', $filterValue)
                    ->orWhere('short_description', 'like', $filterValue)
                    ->orWhere('name', 'like', $filterValue)
                    ->orWhere('model', 'like', $filterValue)
                    ->orWhere('type', 'like ', $filterValue)
                    ->orWhere('make', 'like', $filterValue)
                    ->orWhere('version', 'like', $filterValue);
            });
        }
        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }
        return $query;
    }

    public function getActiveProducts()
    {
        $query = Product::query()->where('active', true);
        $this->buildFilter($query);
        return $query;
    }

    public function getAllProducts()
    {
        $query = Product::query();
        $this->buildFilter($query);
        return $query;
    }

    public function getProductById(int $id)
    {
        return Product::find($id);
    }

    public function createProduct(array $data)
    {
        return Product::create($data);
    }

    public function updateProduct(int $id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }
}
