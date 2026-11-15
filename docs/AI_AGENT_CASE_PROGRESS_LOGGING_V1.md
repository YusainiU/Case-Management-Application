# AI Agent actions in the Case Progress log

AI approvals and AI analyses are now mirrored into the existing `task_logs` table (Case > **Progress**, under **Case Activity**). The durable AI proposal/approval/execution tables remain the authoritative audit trail.

## Events recorded

| Workflow | Events |
| --- | --- |
| Category change proposal | prepared, approved/rejected, queued when execution enabled, completed/stale/failed |
| Engineer assignment proposal | prepared, approved/rejected, queued when execution enabled, completed/stale/failed |
| Start Work proposal | prepared, approved/rejected, queued when execution enabled, completed/stale/failed |
| Case Assistant analysis | requested, completed or terminally failed |
| Semantic engineer-skill analysis | requested, completed or terminally failed |

- Entries have `type = 'AI Agent'`, `source = 'ai_agent'`, and `visible_to_external = false`.
- They are **internal activity/audit notes**, not customer-facing comments. No customer emails are sent and no urgent-attention flags are set.
- Human proposal and review events identify the actor when known. Background-worker entries show as System.
- The case-progress entry includes the proposal/analysis ID, the action type, the event and the relevant intended target; it does not copy AI prompts, detailed case content, secrets, provider errors or raw exception traces.
- AI progress entries are read-only in the Progress Livewire interface; the normal edit/delete/urgent controls do not apply, and the server rejects attempts to alter or delete such entries through that component.
- Event keys are stable, so retrying an execution job or re-running the backfill does not normally duplicate progress entries. They are not a substitute for DB-level append-only auditing.

## Existing history (optional backfill)

The logger affects **new events** after deployment. Historical AI analysis, proposal, approval and execution records can be reconstructed **where the original audit data exists**, without inventing missing intermediate events.

First take a database backup, then preview:

```bash
php artisan ai:backfill-case-progress
```

To write the missing internal logs:

```bash
php artisan ai:backfill-case-progress --write
```

The command is opt-in and can be rerun; it checks the event keys before writing. Original audit timestamps are used when available. Historical execution data generally stores the **latest** execution status only, so intermediate queue retries and states that were never stored cannot be reconstructed accurately. The preview displays the number of existing AI proposals and analyses eligible for processing, not an exact count of missing timeline entries.

Run the backfill while workers aren't simultaneously producing the same legacy events to avoid concurrent insertion races.

## Acceptance tests

Using the isolated **test** database:

```bash
php artisan test --filter=AIProgressLoggingTest
php artisan test --filter=AIWorkStartApprovalTest
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIEngineerSemanticMatchingTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIApprovalReviewQueueTest
```

Then, in development, prepare and approve a new Start Work proposal for a disposable case. Navigate to **Progress** and confirm that *prepared*, *approved*, *queued* and *completed* appear as internal AI Agent entries. Verify the user cannot edit or delete those entries. With execution disabled, only the **prepared** and **approved** entries are expected.

## Compatibility

- No additional database migration is required.
- No AI provider is required for rule-assisted Start Work or for logging events.
- This work stays on `feature/ai-agent-approvals-v1`; draft PR #29 is not merged into `main`.
- PHP/Laravel tests must be run locally: GitHub-only changes have not been executed in the developer's environment.
