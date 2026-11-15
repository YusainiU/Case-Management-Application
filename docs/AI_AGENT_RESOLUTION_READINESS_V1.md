# Phase 9 — Resolution Readiness Handoff (human evidence only)

Phase 9 extends the successfully tested Troubleshooting Review workflow with a staff-authored *resolution handoff*. It is a **preparation step**, not an AI auto-resolve action, not a proposal to auto-close, and not a diagnosis. The application's existing Resolve/Close screen and SLA rules are unchanged.

## Workflow

1. Open an unresolved case in **Case Assistant (AI)**.
2. Use **Troubleshooting Review** to save a staff-reviewed internal progress note (Phase 8).
3. In **Resolution Readiness — Staff Handoff**, select the reviewed troubleshooting progress log.
4. Select **Prepare resolution handoff**. The case and selected note are snapshotted.
5. Enter **Fix actually applied** and **Verification actually performed**, each with details of completed work. Enter remaining risks/follow-up if any.
6. Tick the explicit verification attestation, then **Save internal resolution handoff**.
7. Review the new staff-attributed internal entry under **Case > Progress**. Only if staff have independently determined the case is resolved, use the existing **Close & Resolve** tab. No values are automatically copied into that form.

The handoff is `task_logs.type='Internal Update'`, `source='ai_resolution_review'`, `visible_to_external=false`, `require_attention=false`. It is tagged with the supporting progress log's number, and its description includes staff-entered work and verification evidence. It is *not* a protected system audit event; normal internal-progress-log editing rules apply.

## Optional direct resolution (explicit staff consent)

The Resolution Readiness panel now provides an **unchecked-by-default** checkbox:

**Also resolve this case using the existing Close & Resolve process**

When **unchecked**, the existing Phase 9 behaviour is unchanged: save a private internal handoff only. The case, SLA and customer communications are unaffected.

When **checked**, the same staff member must provide the standard resolution fields: **Resolution type**, **Resolved by** (from the same internal-user options as Close & Resolve) and **Resolved at** (must not be in the future). The fix description and verification evidence remain mandatory, and the staff member must attest that the work was actually done.

On Save, the application rechecks permissions and the case/source-note snapshot, verifies a case SLA task exists, and invokes **`CaseTicketServices::resolveCase()`**, exactly as the existing Resolve/Close form does. This path:

- Changes `tickets.state` to **Resolved**, sets resolution type/notes/resolver/time;
- Changes the associated SLA task state to **Resolved**;
- Writes the existing standard resolution system log;
- **Queues the standard customer-resolution email service after the case/SLA transaction commits** (Phase 10 outbox);
- Writes the staff-reviewed resolution handoff to Case Progress.

It **does not close the case**; closure remains a separate action on the existing Close & Resolve tab.

The opt-in is **human-authorised**, not a background AI decision. No AI model can tick the checkbox by itself, and no new AI provider or execution flag is introduced.

**Important:** The optional AI-assisted path now defers standard customer mail through a durable, case-unique outbox. A queue worker must run to submit it, and submission is not delivery confirmation. The existing non-AI Close & Resolve behaviour remains unchanged. See `docs/AI_AGENT_RESOLUTION_NOTIFICATION_HARDENING_V1.md`. Use a disposable development case and mocked/sandboxed mail delivery when first testing. If no customer email recipient is available, the worker records Skipped; the application does not manufacture a recipient.

## Safeguards

- Only a staff member who can **view and update** the case may prepare or save.
- Cannot prepare or save on resolved, closed or soft-deleted cases.
- Cannot prepare with an unreviewed AI proposal or analysis: the source must be an existing `source='ai_assisted'` troubleshooting note on the same case.
- Saving checks the original case fields and selected source note, including its updated timestamp and content; if either changes, the draft must be prepared again.
- Saving requires nontrivial staff-provided work and verification descriptions and explicit attestation.
- This phase makes **no external AI calls** and requires no AI provider for saving an existing evidence-backed note.
- By default (checkbox unchecked), saving does **not** modify `tickets.state`, resolution fields, SLA tasks, assignments, or customer messages. When explicitly checked and validated, the normal resolution service updates state, resolution fields and SLA and triggers the ordinary customer notification. It still never closes the case.
- The system cannot independently confirm that staff text is truthful. The human reviewer owns that decision.

## Verification

Use the isolated Laravel test database:

```bash
php artisan test --filter=AIResolutionReadinessTest
php artisan test --filter=AITroubleshootingReviewTest
php artisan test --filter=AIProgressLoggingTest
php artisan test --filter=AIWorkStartApprovalTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIEngineerSemanticMatchingTest
php artisan test --filter=AIApprovalReviewQueueTest
```

In the browser, first test the **unchecked** path: prepare a handoff, document a fix and an actual retest, attest, save, and verify the case remains unchanged. Next, on a separate disposable development case with an SLA task, select the **resolve** checkbox, fill type/resolver/date, attest, and save. Verify the case and SLA are **Resolved**, the standard resolution and handoff logs exist, and the customer notification is routed to a test/sandbox inbox. Verify the case was not **Closed**. Check that viewer-only users cannot save. Changes remain on `feature/ai-agent-approvals-v1` in draft PR #29, not merged to `main`.

These tests were not executed in the user's local Laravel environment.
