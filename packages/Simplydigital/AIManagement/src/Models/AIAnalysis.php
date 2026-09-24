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

    protected $casts = [
        'result' => 'array',
    ];

}
