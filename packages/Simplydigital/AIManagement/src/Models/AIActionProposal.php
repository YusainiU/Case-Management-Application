<?php

namespace Simplydigital\AIManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AIActionProposal extends Model
{
    use HasUuids;

    protected $table = 'ai_action_proposals';

    protected $fillable = [
        'case_id', 'ai_analysis_id', 'action_type', 'payload',
        'source_hash', 'payload_hash', 'reason', 'status', 'expires_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'expires_at' => 'immutable_datetime',
    ];
}
