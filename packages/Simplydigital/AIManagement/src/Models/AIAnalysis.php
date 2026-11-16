<?php

namespace Simplydigital\AIManagement\Models;

use Illuminate\Database\Eloquent\Model;

class AIAnalysis extends Model
{
    protected $table = 'ai_analyses';

    protected $fillable = [
        'case_id',
        'user_id',
        'type',
        'model',
        'prompt_version',
        'status',
        'result',
        'error',
    ];

    public function case(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Simplydigital\CaseManagement\Models\tickets::class, 'case_id');
    }

    protected $casts = [
        'result' => 'array',
    ];

}
