# Phase 6 — Explainable engineer recommendation quality

The recommendation engine remains deterministic and read-only. It does **not** call an LLM, verify staff schedules, or make assignments without an approval. No new database tables or permissions are required.

## Ranking order

For each active engineer with a linked user account:

1. Count exact whole-phrase matches between active skill names and the case short description / description (highest first).
2. Break ties by the number of matching **active, unexpired certification records** (highest first).
3. Break remaining ties by the current number of unclosed assigned tickets (lowest first).
4. Break any further tie by the engineer's user ID for stable ordering.

Certification evidence is counted **only** when the engineer certification is active, explicitly not marked expired, not soft deleted, has a future expiry date, and references an active, non-deleted certificate. The certificate name must appear as a whole phrase in the case text. Records with no expiry date are **not used as positive ranking evidence** because their validity cannot be confirmed from an expiry date.

The Case Assistant UI now shows matched skill names, matched current certification names, and open-case counts. An assignment proposal stores a point-in-time explanation of the selected candidate's matching evidence.

## Important limitations

- This is literal text matching, not semantic or multilingual similarity. A valid skill/certificate can be missed if the case uses different words.
- Certificate validity here is based on database flags and expiry, not external issuer validation.
- Workload is an open-ticket count, not engineer availability, travel time, schedule, capacity, or SLA fitness.
- A certification or skill match is advisory evidence for the reviewer; the system does not require a particular match to approve a case.
- Engineer selection, independent execution switches, current reviewer permissions, staleness checks, and queue audit logic from Phases 4 and 5 remain unchanged.

## Verification

Use the isolated MySQL test database:

```bash
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIApprovalReviewQueueTest
```

Browser acceptance: open a test case containing a skill or certificate name in its description, then open Case Assistant (AI) and inspect the candidate explanations. Preparing a proposal should include the matched certification (when applicable) in the audit reason. Approving the proposal still follows the existing human-reviewed assignment process.

These tests were added on `feature/ai-agent-approvals-v1` and still require execution in the local environment.
