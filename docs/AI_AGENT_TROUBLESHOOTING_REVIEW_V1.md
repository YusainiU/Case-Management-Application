# Phase 8 — Human-reviewed AI troubleshooting notes

This phase makes the existing Case Assistant's next-step suggestions useful in the case workflow **without** creating an automatic resolution action.

## Workflow

1. Open an unresolved, non-closed case in **Case Assistant (AI)**.
2. Run a Case Assistant analysis using the previously configured provider. The troubleshooting panel only uses an **existing completed analysis**; it does not call the AI provider itself.
3. In **Troubleshooting Review**, select **Prepare editable troubleshooting note**.
4. The editable draft includes the case number, AI summary, suggested checks, uncertainties and blank placeholders for verified engineer findings and next steps.
5. Staff must check and correct the text, distinguishing *suggestions* from work actually performed, then explicitly attest to review.
6. Select **Save reviewed internal progress note**. This writes one new, staff-attributed `task_logs` entry with `source=ai_assisted`, `type=Internal Update`, `visible_to_external=false`, and `require_attention=false`. No customer email is sent.

## Safety and permissions

- Both preparing and saving a draft require the existing **case update** permission, in addition to case view access.
- The case must be unresolved and open to updates. If it is resolved or closed, the save is blocked.
- The draft is tied to a completed Case Assistant analysis. A newer analysis or changed case snapshot invalidates the draft before save.
- Input must be nonempty and at most 10,000 characters. Review checkbox acceptance is mandatory.
- The new note can be reviewed and edited through the existing internal Progress workflow. This is **not** an immutable system audit event; automatic AI lifecycle events are separately logged as read-only `source=ai_agent`.
- The component **never updates** the case state, resolution, resolved timestamps, assignee, SLA tasks or customer-facing comments. It does not imply that diagnostic work has been done.
- If no AI provider has been configured, no new Case Assistant analysis will be available; this panel remains unavailable for drafting until an analysis is present. Existing engineer recommendations and other rule-assisted workflows remain operational.
- This phase introduces no new migrations, permissions, provider switches or automatic AI execution.

## Verification

Run on the isolated MySQL test database:

```bash
php artisan test --filter=AITroubleshootingReviewTest
php artisan test --filter=AIProgressLoggingTest
php artisan test --filter=AIWorkStartApprovalTest
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIEngineerSemanticMatchingTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIApprovalReviewQueueTest
```

For browser acceptance, choose an **unresolved** test case with a **current completed** Case Assistant analysis. Prepare a draft, verify it is editable, check the attestation and save. Open the **Progress** tab and verify the entry is internal and attributed to the reviewing staff user. Verify the case state and resolution have not changed.

GitHub changes remain on `feature/ai-agent-approvals-v1` in draft PR #29, not merged to `main`. These tests require execution in the user's Laravel environment.
