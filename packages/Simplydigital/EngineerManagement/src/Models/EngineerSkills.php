<?php

namespace Simplydigital\EngineerManagement\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class EngineerSkills extends Model
{
	
	protected $fillable = [
		'number',
		'engineer_id',
		'skill_id',
		'skill_level',
		'description',
		'active',
	];

	protected $casts = [
		'active' => 'boolean',
	];

	public function setNumberAttribute($value)
	{
		$this->attributes['number'] = Config('engineer-management.ENGSKL_PREFIX') . time();
	}

	public function getSkillLevelValue()
	{
		$skillLevels = config('engineer-management.skill_levels');
		return $skillLevels[$this->skill_level] ?? $this->skill_level;
	}

	public function engineer(): BelongsTo
	{
		return $this->belongsTo(EngineerManagement::class, 'engineer_id');
	}

	public function skill()
	{
		return $this->belongsTo(Skills::class, 'skill_id');
	}

	public function casts(): array
	{
		return [
			'active' => 'boolean',
		];
	}



}

