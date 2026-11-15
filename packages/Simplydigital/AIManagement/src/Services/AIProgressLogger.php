<?php

namespace Simplydigital\AIManagement\Services;

use Carbon\CarbonInterface;
use Simplydigital\AIManagement\Models\AIActionProposal;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\CaseManagement\Models\TaskLogs;

/**
 * Internal, human-readable mirror of the AI audit trail in the case Progress tab.
 *
 * Call inside the same DB transaction as the corresponding lifecycle change.
 * Each event has a stable key so queue retries and repeated callbacks do not
 * create duplicate progress entries.
 */
class AIProgressLogger
{
    private const SOURCE = 'ai_agent';

    public function proposal(AIActionProposal $proposal, string $event, ?int $actorId = null, ?CarbonInterface $occurredAt = null): void
    {
        $action = match ($proposal->action_type) {
            'update_case_category' => 'Category change',
            'assign_case_engineer' => 'Engineer assignment',
            'start_case_work' => 'Start Work',
            default => 'AI action',
        };

        $target = match ($proposal->action_type) {
            'update_case_category' => 'Proposed category: '.(string) ($proposal->payload['category'] ?? 'unknown'),
            'assign_case_engineer' => 'Proposed engineer user ID: '.(string) ($proposal->payload['engineer_user_id'] ?? 'unknown'),
            'start_case_work' => 'Proposed state: '.(string) ($proposal->payload['state'] ?? 'unknown'),
            default => '',
        };

        $message = match ($event) {
            'prepared' => 'Proposal prepared for human review. No case changes were made.',
            'approved' => 'Proposal approved by a reviewer. Execution may still be disabled or awaiting a worker.',
            'rejected' => 'Proposal rejected by a reviewer. No case changes were made.',
            'queued' => 'Approved action queued for execution.',
            'completed' => 'Approved action executed successfully.',
            'stale' => 'Execution skipped because the case or assignee changed or is no longer eligible.',
            'failed' => 'Execution failed after queue retries. Review the internal execution audit.',
            default => 'AI action status updated.',
        };

        $this->write(
            (int) $proposal->case_id,
            'proposal:'.$proposal->id.':'.$event,
            trim($action.' — '.$message.' '.$target).PHP_EOL.'Proposal ID: '.$proposal->id,
            $actorId,
            $occurredAt,
        );
    }

    public function analysis(AIAnalysis $analysis, string $event, ?int $actorId = null, ?CarbonInterface $occurredAt = null): void
    {
        $name = match ($analysis->type) {
            'case_assistant' => 'Case Assistant analysis',
            'engineer_semantic_skills' => 'Semantic engineer-skill analysis',
            default => 'AI analysis',
        };
        $message = match ($event) {
            'requested' => 'requested for queued processing',
            'completed' => 'completed; recommendations are advisory and require human review',
            'failed' => 'failed; no AI recommendation was applied',
            default => 'status updated',
        };
        $this->write(
            (int) $analysis->case_id,
            'analysis:'.$analysis->id.':'.$event,
            $name.' '.$message.'.'.PHP_EOL.'Analysis ID: '.$analysis->id,
            $actorId ?? ($analysis->user_id ? (int) $analysis->user_id : null),
            $occurredAt,
        );
    }

    private function write(int $caseId, string $eventKey, string $description, ?int $actorId, ?CarbonInterface $occurredAt): void
    {
        // Fits the existing short_description varchar. The key is deliberately
        // independent of any AI-generated wording.
        $key = 'AI:'.$eventKey;
        if (TaskLogs::query()->where('ticket_id', $caseId)
            ->where('source', self::SOURCE)
            ->where('short_description', $key)->exists()) {
            return;
        }

        $log = new TaskLogs([
            'ticket_id' => $caseId,
            'name' => 'AI Agent',
            'short_description' => $key,
            'type' => 'AI Agent',
            'source' => self::SOURCE,
            'user_id' => $actorId,
            'description' => $description,
            'require_attention' => false,
            'visible_to_external' => false,
        ]);
        if ($occurredAt) {
            $log->created_at = $occurredAt;
            $log->updated_at = $occurredAt;
        }
        $log->save();
    }
}
