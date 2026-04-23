<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\Supplier;
use Carbon\Carbon;

class CaseSupplierService
{
    protected $query;
    protected $supplier;
    protected $filter;
    protected $sortBy;
    protected $sortByDirection;

    public function __construct()
    {
        $this->query = Supplier::query();
    }

    public function filterResult($filter)
    {
        $this->filter = $filter;
    }

    public function sortByField($field, $direction)
    {

        $this->sortBy = $field;
        $this->sortByDirection = $direction;
    }

    public function checkIfDate($value)
    {
        return is_string($value) && Carbon::hasFormat($value, 'Y-m-d');
    }

    public function buildProfileItems($supplier)
    {
        $items = [];
        foreach ($supplier->getAttributes() as $key => $value) {

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

    public function buildFilter()
    {
        if ($this->filter) {
            $filterValue = '%' . $this->filter . '%';
            $this->query->where(function ($q) use ($filterValue) {
                $q->orWhere('name', 'like', $filterValue)
                    ->orWhere('number', 'like', $filterValue);
            });
            if ($this->sortBy) {
                $this->query->orderBy($this->sortBy, $this->sortByDirection);
            }
        }
        return $this->query;
    }

    public function getAllActiveSuppliers()
    {
        $this->query->where('active', true);
        return $this->buildFilter();
    }

    public function getSupplierById($id)
    {
        return Supplier::findOrFail($id);
    }

    public function getAllSuppliers()
    {        
        return $this->buildFilter();
    }

    public function createSupplier($data)
    {
        return Supplier::create($data);
    }

    public function updateSupplier($id, $data)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier;
    }

}
