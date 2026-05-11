<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CaseSupplierService
{
    protected Builder $query;
    protected ?Supplier $supplier = null;
    protected string|int|null $filter = null;
    protected ?string $sortBy = null;
    protected ?string $sortByDirection = 'asc';

    public function __construct()
    {
        $this->query = Supplier::query();
    }

    public function filterResult(string|int $filter)
    {
        $this->filter = $filter;
    }

    public function sortByField(string $field, ?string $direction = null)
    {

        $this->sortBy = $field;
        $this->sortByDirection = $direction ?? 'asc';
    }

    public function checkIfDate(string $value)
    {
        return is_string($value) && Carbon::hasFormat($value, 'Y-m-d');
    }

    public function buildOptionsForSupplierSelect()
    {
        return Supplier::where('active', true)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function buildProfileItems(Supplier $supplier)
    {
        $items = [];
        foreach ($supplier->getAttributes() as $key => $value) {

            if ($value && $this->checkIfDate($value)) {
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

    public function getSupplierById(int $id)
    {
        return Supplier::findOrFail($id);
    }

    public function getAllSuppliers()
    {        
        return $this->buildFilter();
    }

    public function createSupplier(array $data)
    {
        return Supplier::create($data);
    }

    public function updateSupplier(int $id, array $data)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier;
    }

}
