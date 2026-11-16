# Phase 7 — Human-approved Start Work (Open → In Progress)

A provider-independent, rule-assisted case-action proposal. It reuses the existing AI action approval queue, audit trail, queue job, per-action permission gates, staleness checks and idempotent execution.

**This feature is NOT evidence that technical work has begun.** The manager must confirm this fact before approving. It does not invoke an AI provider. No new database migrations are required.

## Eligibility

- Case must be **Open**, unresolved, not closed and not soft-deleted.
- Case must already have an assigned user (`tickets.assigned_to`).
- The proposal carries only `{"state":"In Progress"}` and expires after 24 hours.
- At most one unexpired pending Start Work proposal can exist for the case.
- The approval service checks a hash of relevant case data. Any intervening assignment/state update invalidates the proposal.
- The execution job repeats the eligibility checks; stale proposals cannot change case state.
- Changes to resolved/closed cases, SLA tasks, site visits and completed cases are **out of scope**.

## Feature flags

```env
AI_AGENT_EXECUTION_ENABLED=false
AI_ENGINEER_EXECUTION_ENABLED=true
AI_WORK_START_EXECUTION_ENABLED=false
AI_ENGINEER_SEMANTIC_ENABLED=false
```

The new `AI_WORK_START_EXECUTION_ENABLED` flag defaults to **false**. Approval is still recorded, but the case does not change until that flag is explicitly enabled **before a fresh approval**. Existing disabled-execution approvals are not replayed later.

Category, engineer assignment and Start Work execution switches are independent.

## Review workflow

1. In development, open a disposable **Open** case with an assigned engineer.
2. Open **Case Assistant (AI)** and find **Start Work (human approval)**.
3. Click **Prepare Start Work approval**. The case remains Open and a Pending proposal is created.
4. Open the AI Approval Queue (or the case panel) as an authorised reviewer; inspect the case and confirm **actual technical work has begun**.
5. Approve to record the decision, or reject to leave the case unchanged.
6. With Start Work execution disabled, the proposal stays Approved and the case remains Open. With execution enabled and a running worker, a newly approved proposal moves the case to In Progress, records Completed, and preserves existing assignment, SLA and resolution data.

The review queue shows action type **Start Work**, current state, proposed state, decision history and execution state.

## Tests (isolated test database only)

```bash
php artisan test --filter=AIWorkStartApprovalTest
php artisan test --filter=AIEngineerSemanticMatchingTest
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIApprovalReviewQueueTest
```

Phase 7 tests cover eligibility, disabled execution, independent flag, idempotency, proposal staleness, viewer and reviewer permissions, shared queue visibility and rejection. The tests were committed through the GitHub connector but **were not executed in the user's local Laravel environment**.

No changes have been merged into `main`. Work remains on `feature/ai-agent-approvals-v1`.
