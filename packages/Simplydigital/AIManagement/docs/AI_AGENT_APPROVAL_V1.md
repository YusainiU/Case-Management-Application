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
