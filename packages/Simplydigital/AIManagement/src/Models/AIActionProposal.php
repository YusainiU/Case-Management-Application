<?php

namespace Simplydigital\AIManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Simplydigital\CaseManagement\Models\tickets;

class AIActionProposal extends Model
{
    use HasUuids;

    protected $table = 'ai_action_proposals';

    protected $fillable = [
        'case_id', 'ai_analysis_id', 'action_type', 'payload',
        'source_hash', 'payload_hash', 'reason', 'status', 'expires_at',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(tickets::class, 'case_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(AIActionApproval::class, 'proposal_id')->oldest();
    }

    public function execution(): HasOne
    {
        return $this->hasOne(AIActionExecution::class, 'proposal_id');
    }

    protected $casts = [
        'payload' => 'array',
        'expires_at' => 'immutable_datetime',
    ];
}
