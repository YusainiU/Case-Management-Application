# Phase 10 — AI-assisted resolution notification hardening

Phase 10 hardens the **optional, human-checked** Resolve action in Phase 9. It does not change the non-AI Close & Resolve form.

## Why this phase exists

The existing `CaseTicketServices::resolveCase()` performs the normal case update, SLA update, system activity log and customer email together. When the AI assistant called it inside a larger transaction, the email could be sent **before** the database transaction committed. A later failure could roll back the case while the customer had already been notified.

To avoid this, the existing case service now accepts a backward-compatible `notifyCustomer = true` argument. Its existing callers behave unchanged. Only the optional AI-assisted resolution passes `notifyCustomer: false`, then writes a durable notification outbox row in the **same transaction** as the case resolution and internal handoff.

A `SendAIResolutionNotification` job is dispatched **after commit**, reads the recorded resolution and calls the **same `EmailService::caseResolved()`** used by Close & Resolve. No AI provider is involved.

## Database and queue

A migration adds `ai_resolution_notifications` with a unique `case_id`. Its statuses are:

| Status | Meaning |
| --- | --- |
| `pending` | Committed; awaiting worker, or retry scheduled |
| `sending` | Worker claimed notification and is submitting |
| `sent` | Standard mail service completed without reporting no recipient; **delivery is not confirmed** |
| `skipped` | Case no longer valid or no eligible customer recipient available |
| `failed` | Mail submission threw an exception and retries were exhausted |

The case’s Progress log records internal `AI Agent` entries for **queued**, **sent**, **skipped**, and **failed**. No exception trace or credential appears in these logs or the case UI.

The worker has three attempts and backoffs of 10/60/300 seconds. A repeated job invocation with a terminal status will not intentionally send another email. The unique row and claim status prevent normal duplicate submissions. **Exactly-once delivery cannot be guaranteed** without mail-provider idempotency: an unexpected process crash between handing off a message and recording `sent` may need human reconciliation. If status is stuck at `sending`, review the provider and application logs before manually retrying. Do not blindly reset it.

## Operator steps

Before testing, configure a safe mail sandbox (e.g., the application’s test-only mail transport), then:

```bash
git pull --ff-only origin feature/ai-agent-approvals-v1
php artisan migrate
php artisan optimize:clear
php artisan queue:restart
php artisan queue:work --queue=default --tries=3
```

The queue worker is required for customer notification delivery on the AI-assisted resolution path (the normal manual Resolve/Close behaviour is unchanged). If it is not running, the case can still be resolved but notification remains **Queued**.

After a staff member selects **Also resolve this case using the existing Close & Resolve process**, fills all required fields and explicitly saves, the case and SLA become Resolved, the standard resolution and reviewed-handoff progress logs are added, and the notification is queued after commit. The UI displays current notification status; **Submitted to mail service** does not imply delivery.

With the checkbox **unchecked**, no notification outbox row is created and the case remains unchanged.

## Regression tests

On the isolated test database:

```bash
php artisan test --filter=AIResolutionReadinessTest
php artisan test --filter=AITroubleshootingReviewTest
php artisan test --filter=AIProgressLoggingTest
php artisan test --filter=AIWorkStartApprovalTest
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIApprovalReviewQueueTest
php artisan test --filter=AIEngineerSemanticMatchingTest
```

The Phase 10 tests exercise real case and SLA state transitions, mail worker claim/idempotency, no-recipient skip, generic terminal failure, and transactional rollback if the resolver fails. They were committed but **not run locally**.

No changes have been merged into `main`.
