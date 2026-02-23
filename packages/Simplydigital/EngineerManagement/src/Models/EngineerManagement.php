<?php

namespace Simplydigital\EngineerManagement\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EngineerManagement extends Model
{
	
	protected $fillable = [
		'number',
		'user_id',
		'position',
		'manager',
		'active',
		'department',
	];

	public function setNumberAttribute($value)
	{
		$this->attributes['number'] = Config('engineer-management.ENG_PREFIX') . time();
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function engmanager(): BelongsTo
	{
		return $this->belongsTo(User::class, 'manager');
	}

	public function skills(): HasMany
	{
		return $this->hasMany(EngineerSkills::class, 'engineer_id');
	}

	public function certifications(): HasMany
	{
		return $this->hasMany(EngineerCertifications::class, 'engineer_id');
	}

	public function casts(): array
	{
		return [
			'active' => 'boolean',
		];
	}

}

