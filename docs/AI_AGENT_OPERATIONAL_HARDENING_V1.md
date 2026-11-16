# Phase 5: AI approval execution operations

Phase 5 builds on category and engineer approvals without changing the human approval requirement.

## States and audit trail

- `pending`: waiting for a reviewer.
- `approved`: human approval recorded. If the relevant execution flag was enabled at approval time, the execution table has a `queued` record.
- `completed`: the case was updated successfully and the execution table records `completed`.
- `stale`: the underlying case changed or an engineer became ineligible before execution. The case is **not** updated by the stale job.
- `failed`: execution exhausted its bounded queue retries. The proposal and execution audit both record `failed`. This is *not* permission to rerun blindly.
- `rejected`: reviewer rejected; no execution is dispatched.

The review queue has a Failed status filter and displays the execution record. The actual error text shown to reviewers is generic; exception details belong in application logs and queue infrastructure, not in browser pages.

For newly approved actions with execution enabled, the queued audit row is inserted atomically with the review decision and the job is dispatched after the transaction commits. A queue worker must process the job. Previously approved actions made with execution disabled are **not** retroactively dispatched.

## Operating the development queue

For a disposable local environment:

```bash
php artisan optimize:clear
php artisan queue:work --queue=default --tries=3
```

Run the worker in a separate terminal and restart it after pulling changes with `php artisan queue:restart`. For the database queue driver, ensure the queue tables exist and workers can access the configured database.

You can inspect failed jobs using:

```bash
php artisan queue:failed
```

Also check `storage/logs/laravel.log` and the proposal's audit record in the review queue.

**Do not run `queue:retry all` or manually change a Failed or Stale proposal back to Approved.** Investigate the exception, current assignee/category, reviewer permissions, engineer eligibility, expiry and source hash. Then generate a fresh proposal and request fresh human approval when appropriate. Automatic job retries happen only within the normal retry budget; after failure, a new decision is required.

## Safety controls

```env
AI_AGENT_EXECUTION_ENABLED=false
AI_ENGINEER_EXECUTION_ENABLED=false
```

These flags remain independent. Enable only the intended action type on a development/test database. Human approval and current authorisation are mandatory for either action. The job revalidates the approval, payload hash, case snapshot and engineer eligibility prior to any write.

The case modal refreshes its backing case data every 10 seconds while the AI Assistant tab is open. The review list also polls for queue state changes.

## Regression tests

Use only the project’s isolated **disposable MySQL test database**:

```bash
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIApprovalReviewQueueTest
```

The Phase 5 tests additionally assert queued audit records, stale outcomes, exhausted failure handling, redacted error details, and Failed review filtering. These tests were committed via GitHub and **have not yet been executed locally**.

## Deliberate limitations

- This release does not provide a manual **Retry** button: that could replay an expired or invalid approval.
- A worker that's not running will leave an action in `approved` / `queued` until the queue is processed or inspected. Monitoring worker uptime is an operational requirement.
- Attempts are observable through the queue infrastructure and failed job records. The proposal audit stores final states, not a full per-attempt event log.
- Do not enable production execution before confirming actual queue supervision, alerting, backups and business approval policies.
