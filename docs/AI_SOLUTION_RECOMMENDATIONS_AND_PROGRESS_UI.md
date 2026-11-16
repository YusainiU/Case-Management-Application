# AI Assistant — Engineer names, solution recommendations and analysis status

These improvements are on `feature/ai-agent-approvals-v1` / draft PR #29 and are not merged into `main`.

## 1. Human-readable engineer assignment audit

New AI proposal lifecycle logs (prepared/approved/queued/completed/rejected/failed) for `assign_case_engineer` show **Proposed engineer: [user's name]**, resolved from the selected engineer user ID. If the user account is gone, the log identifies it as a former user with an ID fallback.

The underlying proposal and assignment still use stable user IDs internally; only the audit description changes. **Existing historical text logs are not rewritten**. When the Progress Log or AI Assistant timeline displays an older AI audit with the text `Proposed engineer user ID: N`, it resolves the saved ID to a name for display only. A missing account is shown as `Former user (ID N)`.

## 2. Dedicated Recommend Solution in Troubleshooting Review

**Analyze Case / Re-analyze Case** performs the existing general analysis: case summary, suggested category, suggested next actions, uncertainty and other general content. It does not perform a dedicated solution-research request.

**Recommend Solution** performs a **separate** AI-provider request, with an explicit opt-in checkbox warning that it consumes provider tokens/credits and may incur a charge. It uses the same configured supported provider/model credentials as normal case analysis, but has a separate `troubleshooting_solution` analysis record and queue job.

Requirements:

- User must have the case's **update** permission and the existing **case-assistant.analyze** ability.
- The case must remain unresolved/unclosed and must have an existing **latest completed** initial Case Assistant analysis.
- The supported provider, model and API credentials must be configured.
- A pending/processing solution request for the same case prevents duplicate simultaneous requests.
- Each new request requires a fresh credit-consent confirmation.
- The job checks that case identity, core issue description, case state and latest initial analysis have not changed while generating. A changed case causes the recommendation to be rejected rather than silently presented as current.
- Requests and outcomes create internal, read-only AI lifecycle logs; no customer mail, case status update, engineer reassignment or approval occurs.

The provider is asked for a **possible solution**, rationale, checks to perform, potential repair steps, verification and uncertainties. The results are **unverified suggestions**, not evidence of performed work or guaranteed diagnoses. An engineer must verify before updating a troubleshooting note.

The new feature does **not** require enabling `AI_AGENT_EXECUTION_ENABLED`, `AI_ENGINEER_EXECUTION_ENABLED` or `AI_WORK_START_EXECUTION_ENABLED`. Those independently control approved case changes and do not change what the standard analysis prompt requests. No new environment variable is needed; the provider configuration and the user's explicit per-request consent control the feature.

**Privacy and costs:** The request sends a bounded subset of the issue title/description and initial analysis summary/next steps/warnings to the configured AI provider. These fields may contain customer-sensitive information. Confirm organisational approval to share them and check provider pricing before use. No credit charge is guaranteed until the provider processes a request; no precise cost is promised.

A failed request may be retried through the normal job retry mechanism, potentially creating multiple provider calls. Budget/usage monitoring should be configured with the provider.

## 3. Visible progress on Analyze Case

- When the user clicks **Analyze Case** or **Re-analyze Case**, the button shows an immediate animated spinner and `Queuing analysis…` state and is disabled during submission.
- Once an analysis is queued or processing, a pulsing **indeterminate** progress bar and explicit status text remain visible while the Livewire page polls.
- Status transitions to completed or failed based on the stored job result. This is **not** a percentage completion bar; no percentage can be inferred from queue/provider processing.

## Local verification

```bash
git pull --ff-only origin feature/ai-agent-approvals-v1
php artisan optimize:clear
php artisan test --filter=AITroubleshootingReviewTest
php artisan test --filter=AIProgressLoggingTest
php artisan test --filter=AIAssistantTimelineTest
```

For manual checks:

1. Select a named engineer, submit/approve an engineer-assignment proposal and inspect **Progress** and the AI timeline. Newly generated lifecycle entries should show the engineer's name, not only their ID.
2. Open a case with a completed initial analysis and expand **Troubleshooting Review**. The **Recommend Solution** button should clearly warn about provider credits and require the checkbox. Confirm the new request appears as pending/processing, completes with separate solution/checks/verification/risk content, and does **not** resolve the case or create a reviewed progress note automatically. Attempt a second request while the first is processing and confirm it is blocked.
3. Click **Analyze Case** on a test case and confirm the spinner appears during submission, then the persistent queued/processing indicator until the job finishes. Test a queue worker with an asynchronous queue connection; completion requires a functioning provider and worker.

Tests have been added, but require local execution. No merge into `main` has occurred.
