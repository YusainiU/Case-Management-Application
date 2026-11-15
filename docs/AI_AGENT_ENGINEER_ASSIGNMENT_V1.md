# Phase 4 — Engineer Assignment Recommendations (human approved)

This is a **read-only, deterministic recommendation engine** over application data, not an LLM or a verified staff-availability system. The first release avoids sending engineer personal data to an AI provider. It looks for exact whole-phrase matches between active skill names and the case title/description; it ranks matching engineers first, then engineers with fewer currently open cases. It displays *no confirmed skill match* when there is insufficient evidence. Skill match is not a guarantee of suitability.

## Workflow

1. In a case's Case Assistant (AI), find **Engineer Assignment Recommendations**.
2. Users with `ai.case_assistant.analyze` and `case.update` may select a recommended active engineer and prepare a pending `assign_case_engineer` proposal.
3. Approvals require `ai.proposals.approve` and `case.update` for the case, checked again at approval and execution. Reviewers can approve or reject in the case or at `/ai/approvals` (readers need `case.view` and `ai.proposals.view`).
4. Each proposal records the selected user ID, a hashed payload, the case-assignment snapshot, reason, 24-hour expiry and audit trail. Preparing or approving the proposal **does not directly reassign** a case.
5. Execution, if *separately and explicitly enabled*, revalidates proposal status and approval, case snapshot, reviewer authority and current engineer eligibility before updating `assigned_to` through `CaseRepository`; it records a single execution. Stale assignments cannot proceed.

## Safe defaults

Keep both switches disabled during initial validation:

```env
AI_AGENT_EXECUTION_ENABLED=false
AI_ENGINEER_EXECUTION_ENABLED=false
```

Engineer execution needs **both** flags enabled. Enabling the category execution flag alone cannot dispatch or execute engineer assignment jobs. The browser UI can still demonstrate recommendations, proposals, approval history and rejection while both switches are false.

Do not enable execution on production until assignment-specific tests and business workflow review are complete. Previously approved actions with execution disabled are not automatically queued later.

## Tests

Against an **isolated disposable test database** (the project uses a separate MySQL test database):

```bash
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIApprovalReviewQueueTest
```

The test suite covers eligible/ineligible engineers, matching/workload ordering, proposal and decision tracking, permissions, stale assignments, idempotency, and both execution flags. These tests have been committed but **not run remotely**.

## Known limitations

- This first version performs whole-phrase skill matching against case title and description; it does not call a language model for semantic matching.
- Workload is the number of unclosed cases, not current engineer availability, shift schedule or verified capacity. Engineer certifications, geographic routing, SLA urgency, and schedule capacity are not included in v1.
- Recommendations and available skills depend on the accuracy of the Engineer Management records.
- Manager selects a candidate explicitly. An engineer may have no text-matched skills; the UI warns that suitability requires manual verification.
- A rejected proposal does not change assignments. A new proposal requires a fresh review.
- All work remains on `feature/ai-agent-approvals-v1` and the existing draft PR, not on `main`.
