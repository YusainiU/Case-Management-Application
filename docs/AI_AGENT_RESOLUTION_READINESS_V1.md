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

## Safeguards

- Only a staff member who can **view and update** the case may prepare or save.
- Cannot prepare or save on resolved, closed or soft-deleted cases.
- Cannot prepare with an unreviewed AI proposal or analysis: the source must be an existing `source='ai_assisted'` troubleshooting note on the same case.
- Saving checks the original case fields and selected source note, including its updated timestamp and content; if either changes, the draft must be prepared again.
- Saving requires nontrivial staff-provided work and verification descriptions and explicit attestation.
- This phase makes **no external AI calls** and requires no AI provider for saving an existing evidence-backed note.
- Saving does **not** modify `tickets.state`, `resolution`, `resolution_details`, `resolved_datetime`, `resolved_by`, `closed_datetime`, SLA tasks, assignments, or customer messages.
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

In the browser, open a case with an existing reviewed troubleshooting note, select it, prepare a handoff, document a fix and an actual retest, attest, and save. The new internal note should appear in **Progress** while the case retains its current status. Check that viewer-only users cannot save. Changes remain on `feature/ai-agent-approvals-v1` in draft PR #29, not merged to `main`.

These tests were not executed in the user's local Laravel environment.
