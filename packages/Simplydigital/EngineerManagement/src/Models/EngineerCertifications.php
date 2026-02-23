<?php

namespace Simplydigital\EngineerManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngineerCertifications extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'certification_number',
		'certificate_id',
		'engineer_id',
        'issued_date',
        'expiry_date',
		'expired',
        'active',
    ];

    protected $casts = [
		'expired' => 'boolean',
        'active' => 'boolean',
		'issued_date' => 'datetime:d-m-Y H:i:s',
		'expiry_date' => 'datetime:d-m-Y H:i:s',
    ];

	public function setCertificationNumberAttribute($value)
	{
		$this->attributes['certification_number'] = Config('engineer-management.ENGCERT_PREFIX') . time();
	}

	public function isExpired()
	{
		if($this->expiry_date && $this->expiry_date->isPast()){
			return true;
		}
		return false;
	}

	public function formatIssuedDate()
	{
		return $this->issued_date ? $this->issued_date->format('d-m-Y') : null;
	}

	public function formatExpiryDate()
	{
		return $this->expiry_date ? $this->expiry_date->format('d-m-Y') : null;
	}

	public function getActiveAttribute($value)
	{
		return (bool) $value;
	}

	public function engineer()
	{
		return $this->belongsTo(EngineerManagement::class, 'engineer_id');
	}

	public function certificate()
	{
		return $this->belongsTo(Certificates::class, 'certificate_id');
	}

	protected static function booted()
	{
		static::deleting(function ($certificate) {
			$certificate->active = false;
			$certificate->expired = true;
			$certificate->expiry_date = now();
			$certificate->saveQuietly();
		});

		static::restoring(function ($certificate) {
			$certificate->active = true;
			$certificate->saveQuietly();
		});

		static::updated(function($certificate){
			if($certificate->expired){
				$certificate->expiry_date = now();
				$certificate->active = false;
				$certificate->saveQuietly();
			}
			if($certificate->expiry_date && $certificate->isExpired()){
				$certificate->expired = true;
				$certificate->active = false;
				$certificate->saveQuietly();
			}			
		});
	}
    
	public function casts()
	{
		return [
			'expired' => 'boolean',
			'active' => 'boolean',
			'issued_date' => 'datetime:d-m-Y H:i:s',
			'expiry_date' => 'datetime:d-m-Y H:i:s',			
		];
	}    
}
