<?php

namespace Simplydigital\EngineerManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificates extends Model
{

    use SoftDeletes;

    protected $fillable = [
		'number',
        'name',
        'type',
        'issued_by',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function setNumberAttribute($value)
    {
        $this->attributes['number'] = Config('engineer-management.CERT_PREFIX') . time();
    }

	public function getTypeValue()
	{
		$types = config('engineer-management.certification_types');
		return $types[$this->type] ?? $this->type;
	}

	protected static function booted()
	{
		static::deleting(function ($certificate) {
			$certificate->active = false;
			$certificate->saveQuietly();
		});

		static::restoring(function ($certificate) {
			$certificate->active = true;
			$certificate->saveQuietly();
		});

	}
    
	public function casts()
	{
		return [
			'active' => 'boolean',
		];
	}    
}
