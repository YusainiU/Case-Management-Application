<?php

namespace Simplydigital\EngineerManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skills extends Model
{
	use SoftDeletes;

	protected $fillable = [
		'number',
		'skills',
		'description',
		'active',
	];

	protected $casts = [
		'active' => 'boolean',
	];

	public function setNumberAttribute($value)
	{
		$this->attributes['number'] = Config('engineer-management.SKILL_PREFIX') . time();
	}

	public function engineer(): BelongsTo
	{
		return $this->belongsTo(EngineerManagement::class, 'engineer_id');
	}

	protected static function booted()
	{
		static::deleting(function ($skill) {
			$skill->active = false;
			$skill->saveQuietly();
		});

		static::restoring(function ($skill) {
			$skill->active = true;
			$skill->saveQuietly();
		});

	}

	public function casts()
	{
		return [
			'active' => 'boolean',
		];
	}
}
