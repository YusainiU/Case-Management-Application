# AI Case Assistant — SLA Pause / Resume Workflow

The AI Case Assistant now includes a seventh **SLA pause / resume** stage between **Start Work** and **Troubleshooting Review**. It is a **human-operated interface to the existing Case Management SLA service**, not a generative AI action or an automated SLA decision.

## Why this stage exists

A case may need to wait for parts, customer confirmation, access or other operational reasons. When company policy allows it, an authorised staff member can pause the applicable SLA. The system records the remaining working-time allowance, marks the case/SLA as paused, and resumes the SLA later with deadlines recalculated using that saved allowance.

**Pausing a timer should only be done when the organisation's SLA contract permits it.** AI advice must never be taken as automatic permission to stop contractual time.

## How to use it

1. Open a case and select **Case Assistant (AI)**.
2. Expand **05 · Pause / Resume SLA (staff controlled)**. This is available in both Focus View and Full View.
3. Check the current pause state and stored response/fix deadlines.
4. If the case is eligible, tick the acknowledgement and select **Pause SLA**.
5. When appropriate, select **Resume SLA** to recalculate deadlines from the frozen remaining allowance.
6. If a pause was created in error, **Cancel Pause** (with an extra confirmation) discards that pause and retains the *original* deadlines instead of granting extra time.
7. Use the standard **Progress** tab for the existing system-generated pause/resume/cancellation audit events.

The AI Assistant's read-only **Case Workflow Overview** shows the seventh stage's status. When an SLA is actively paused, **Suggested Next Step** directs staff to inspect the pause rather than continue blindly into troubleshooting or resolution. These navigation elements do not execute any action.

**No new AI provider call, credit charge, proposal or AI execution switch is required.** The controls reuse `CaseTicketServices::pauseSla`, `resumeSla` and `cancelPauseSla`. All changes are explicitly initiated by the authorised human user.

## Eligibility and safeguards

The new controls follow the rules already applied in the manual **Manage SLA** screen:

- User must have permission to view the case, and **case update** permission for actions.
- A real SLA task must exist.
- Resolved, closed and inactive cases cannot pause or resume the SLA.
- Breached SLAs cannot be newly paused through this workflow.
- The action must be performed on an eligible SLA working day and within its configured service hours.
- Only an active pause can be resumed or cancelled; a second simultaneous pause is rejected.
- Pause/resume/cancellation operate through the same case-management services and standard system logs.

The shared service has been hardened for both manual and AI Assistant users:

- The case, SLA-task, pause record and progress logs are written within a single database transaction with row locks.
- A pause now correctly updates **both** the case state and SLA-task state/`paused_at`.
- Resume retrieves the active pause directly, rather than relying on equality between separately stored timestamps.
- Resume clears the active pause flags, changes the case and SLA state to **In Progress**, recalculates response and fix deadlines, and writes the existing system logs.
- Cancel clears the active pause and returns both states to **In Progress** without extending the existing deadlines.
- Repeat pauses calculate remaining SLA allowance from the **current response/fix deadlines**, rather than from the original case creation time, so prior paused intervals are not charged twice.
- The normal Manage SLA dialog now refreshes the pause state and displays failure messages when a service operation cannot proceed.

**Terminology:** Pause freezes the remaining *SLA working-time allowance*, not the real-world clock. Stored deadlines are updated on resume. A failed or forbidden action does not silently change the case.

## Limitations

- This is not an AI recommendation of *whether* to pause: it is the existing manual SLA control embedded in the assistant.
- Outside the SLA's configured operating hours, the same manual-system restrictions apply.
- Organisation-specific reasons, pause approvals, contractual exceptions, notifications and pause-duration limits are **not** added in this iteration. If your company requires them, they need explicit policy rules before production use.
- An existing SLA task must be present; use the normal Manage SLA screen to create or repair missing tasks.
- A paused case can still be viewed and investigated; the pause stage does not block all other workflow panels.

## Local verification

```bash
git pull --ff-only origin feature/ai-agent-approvals-v1
php artisan optimize:clear
php artisan test --filter=AISlaPauseWorkflowTest
php artisan test --filter=AIAssistantTimelineTest
php artisan test --filter=AIWorkStartApprovalTest
php artisan test --filter=AIResolutionReadinessTest
```

Also run the complete suite in your **isolated test database** before production deployment. These tests are committed but cannot be executed remotely through the GitHub connector.

In a disposable manual test case with a valid, unbreached SLA during working hours, record the current deadlines, pause the SLA, confirm that both the case and SLA are paused, then resume it and verify that the original remaining allowance was preserved. Repeat the cycle to check that earlier pause periods are not counted twice. Test **Cancel Pause** separately, and verify that a view-only user cannot perform state changes.

## Related guides

- [AI Assistance User and Management Guide](AI_ASSISTANCE_USER_AND_MANAGEMENT_GUIDE.md)
- [AI Assistant Timeline and Workflow Overview](AI_ASSISTANT_TIMELINE_UI_V1.md)
- [Ubuntu Queue Worker Guide](UBUNTU_SUPERVISOR_QUEUE_WORKER_GUIDE.md)

Changes are on `feature/ai-agent-approvals-v1` / draft PR #29 only; **not merged into main**.
