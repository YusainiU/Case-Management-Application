<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\Asset;
use Illuminate\Database\Eloquent\Builder;

class CaseAssetServices
{
    private ?Customer $customer = null;
    private ?string $sortBy = null;
    private string $sortDirection = 'asc';
    private string|int|null $filter = null;
    
    public function setCustomer(int $customerId)
    {
        $this->customer = Customer::find($customerId);
    }

    public function sortByField(string $sortBy, ?string $sortDirection = null)
    {
        $this->sortBy = $sortBy;
        if ($sortDirection) {
            $this->sortDirection = $sortDirection;
        }
    }
    
    public function filterResult(string|int $filter)
    {
        $this->filter = $filter;
    }    

    public function getActiveAssets()
    {

        $query = Asset::query()->where('active', true);
        if($this->customer) {
            $query->where('customer_id', $this->customer->id);
        }        
        $this->buildFilter($query);        
        return $query;

    }

    public function getAllAssets()
    {
        $query = Asset::query();
        if($this->customer) {
            $query->where('customer_id', $this->customer->id)->get();
        }
        $this->buildFilter($query);
        return $query;
    }
    
    public function buildOptionsForAssetSelect(?int $customerId = null)
    {
        $opts = Asset::query();
        if($customerId){
            $opts->where('customer_id', $customerId);
        }
        return $opts->where('active', true)
            ->orderBy('short_description', 'asc')
            ->pluck('short_description', 'id')
            ->toArray();
    }

    public static function getAssetsFromArrayOfIds(array $ids)
    {
        return Asset::whereIn('id', $ids)->get();
    }

    public function buildProfileItems(Asset $asset)
    {
        $items = [];
        $service = app('case-management-utility-services');
        foreach ($asset->getAttributes() as $key => $value) {
            if ($value && $this->checkIfDate($value)) {
                $value = $service::displayDateValue($value);
            }
            if ($key == 'active') {
                $value = $value ? 'Yes' : 'No';
            }
            if($key == 'sell_price') {
                $value = $asset->getBuyPriceFormattedAttribute($value);
            }
            if($key == 'buy_price'){
                $value = $asset->getSellPriceFormattedAttribute($value);
            }
            if($key == 'customer_id'){
                $value = $asset->customer->name;
            }
            if($key == 'product_id') {
                $value = $asset->product->name;
            }
            if($key == 'supplier_id') {
                $value = $asset->supplier->name;
            }
            $items[] = [
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
            ];
        }
        return $items;
    }

    private function buildFilter(Builder $query)
    {
        if($this->filter) {
            $filterValue = '%' . $this->filter . '%';
            $query->where(function ($q) use ($filterValue) {
                $q->orWhere('short_description', 'like', $filterValue)
                    ->orWhere('number', 'like', $filterValue)
                    ->orWhere('short_description', 'like', $filterValue)
                    ->orWhere('location', 'like', $filterValue)
                    ->orWhereHas('product', function($q) use($filterValue){
                        $q->where('name', 'like', $filterValue);
                    })
                    ->orWhereHas('supplier', function($q) use($filterValue){
                        $q->where('name', 'like', $filterValue);
                    })
                    ->orWhereHas('customer', function($q) use($filterValue){
                        $q->where('name', 'like', $filterValue);
                    });
            });
        }

        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query;
    }
    
    public function getAssetById(int $assetId)
    {
        return Asset::find($assetId);
    }

    public function createAsset(array $data)
    {
        $data = $this->normaliseDecimalValues($data);
        return Asset::create($data);
    }

    public function updateAsset(Asset $asset, array $data)
    {
        if ($asset) {
            $data = $this->normaliseDecimalValues($data);
            $asset->update($data);
            return $asset;
        }
        return null;
    }

    public function normaliseDecimalValues(array $data)
    {
        $service = app('case-management-utility-services');
        $data['buy_price'] = $service::normaliseDecimalPriceValues($data['buy_price']);
        $data['sell_price'] = $service::normaliseDecimalPriceValues($data['sell_price']);
        return $data;
    }

    public function checkIfDate(string $value)
    {
        $service = app('case-management-utility-services');
        return $service::checkIfDate($value);
    }    

}