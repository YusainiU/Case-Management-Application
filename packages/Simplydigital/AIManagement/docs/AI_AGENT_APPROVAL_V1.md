# AI Agent Approval Engine — v1

This first-stage feature uses the existing `AIAnalysis` results and does **not** make additional AI API calls.

## Confirmed integration

- `Simplydigital\\AIManagement\\AI\\Services\\CaseAssistant` already produces `suggested_category`.
- The case model is `Simplydigital\\CaseManagement\\Models\\tickets`.
- The allowed category enum is `CaseCategory`: Failure, Part Replacement, Technical Support.
- The existing `CaseRepository::updateTicket()` is the execution path.

## Scope and safety

Only `update_case_category` is supported. The design does **not** attempt to change priority: the ticket model has no direct priority property to update.

A proposal is created by calling:
```php
$proposal = app(\Simplydigital\AIManagement\Services\CategoryProposalService::class)
    ->fromAnalysis($completedAnalysis);
```

Approval and rejection:
```php
app(\Simplydigital\AIManagement\Services\AIApprovalService::class)
    ->approve($proposal->id, auth()->user());
```

This branch is an initial implementation. Do not enable the approval interface for production until integration tests are implemented and reviewed. The executor uses row locks and a snapshot hash; it relies on `CaseRepository` and ticket model logging for the category update.

## Required integration steps

1. Run migrations in a development database.
2. Add an approval authorization gate called `ai-category.approve`, checking BOTH the new `ai.proposals.approve` permission and `case.update` on the target ticket.
3. Trigger proposal generation from a completed Case Assistant analysis, preferably as an optional user action initially.
4. Build the Livewire approval panel with authorize-on-every-request checks.
5. Test queue retries, stale cases, malformed proposals, and role permissions.
6. Add safeguards for execution-time authorisation (including revoked approvals) before enabling production execution.

## Limitations

- The current branch is a foundation, not a production-ready agent.
- `ai_analyses` remains the source of AI outputs.
- Schema references are indexed; final foreign keys can be added after confirming deployment compatibility.
- Future work should introduce an append-only audit ledger and agent runs.

## Development instructions (feature branch only)

Pull `feature/ai-agent-approvals-v1` and run the AI migration on a local database, then `php artisan permissions:sync` (the UserManagement provider now registers that command).

The Case Assistant now contains an embedded `ai-management.category-approvals` Livewire component. It offers preparation of category proposals from existing completed analyses, plus approve/reject controls for authorised case editors with `ai.proposals.approve`.

**Safety default:** `AI_AGENT_EXECUTION_ENABLED=false`. Review actions and decisions are recorded, but no category changes are executed. Approvals made while execution is disabled are not automatically queued later. For controlled execution testing, set `AI_AGENT_EXECUTION_ENABLED=true`, clear cached configuration, and use a *fresh* proposal.

Run the added suite with `php artisan test --filter=AIActionApprovalTest` on a disposable development database. The tests were committed but have not been executed in this environment. Do not merge the draft pull request or use in production until the suite and manual review pass.


## Cross-case approval review queue

The new Livewire page is available at `/ai/approvals` (route `ai.approvals.index`). It lists category-change proposals with pagination, status filtering, current/proposed values, decision history (including reviewer and rejection reason), and recorded execution state/error.

**Access controls:** `case.view` and the new `ai.proposals.view` permission are required to enter the queue. These do not grant approval rights. Approval and rejection continue to require `ai.proposals.approve` and `case.update` for the target case, checked again in the service. Run `php artisan permissions:sync` and explicitly assign the new view permission to intended reviewers/auditors. The Case Assistant has a link to the queue for authorised users.

The table changes from the earlier AI Approval migration are sufficient; there is **no new migration** for the queue.

Run `php artisan test --filter=AIApprovalReviewQueueTest` and `php artisan test --filter=AIActionApprovalTest` on an isolated development test database. The review queue tests are committed but have not been executed in this environment.

Keep `AI_AGENT_EXECUTION_ENABLED=false` when validating the UI. The queue exposes status `approved` even if no execution happened; a missing execution record is shown as `Not recorded`.
