<?php

namespace Simplydigital\CaseManagement\Models;

use League\Flysystem\FilesystemReader;
use Simplydigital\CaseManagement\Enums\CustomerChildTypes;
use Simplydigital\CaseManagement\Enums\CustomerPrimaryTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Simplydigital\CaseManagement\Services\CaseManagementUtilityServices;
use Simplydigital\FileUploadManagement\Contracts\FileOwner;

class Customer extends Model implements FileOwner
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'short_description',
        'primary_type',
        'child_type',
        'url',
        'address_1',
        'address_2',
        'address_3',
        'telephone_1',
        'telephone_2',
        'telephone_3',
        'parent_company',
        'active',
        'portal_enabled',
    ];


    protected static function newFactory()
    {
        return \Simplydigital\CaseManagement\Database\Factories\CustomerFactory::new();
    }

    protected function casts(): array
    {
        return [
            'primary_type' => CustomerPrimaryTypes::class,
            'child_type' => CustomerChildTypes::class,
            'active' => 'boolean',
            'portal_enabled' => 'boolean',
        ];
    }

    protected static function booted()
    {
        
        static::creating(function ($model) {
            $prefix = config('case-management.CUSTOMER_PREFIX');            
            $model->number = CaseManagementUtilityServices::generateNumber($prefix, Customer::class);
        });
    }

    public function getFileOwnerType(): string
    {
        return 'Customers';
    }

    public function getFileOwnerIdentifier(): string
    {
        return $this->customer_number;
    }    
    
    public function parentCompany()
    {
        return $this->belongsTo(Customer::class,'parent_company');
    }

    public function getAddress()
    {
        if($this->address_1) {
            $address = [
                $this->address_1,
                $this->address_2,
                $this->address_3,
            ];
            return $address;
        }
        return [];
    }    
    
}
