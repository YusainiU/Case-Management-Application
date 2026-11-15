# AI Case Assistant — Responsive timeline UI

This is a presentation-only redesign of the Case Assistant's existing Livewire page. It does **not** change the AI recommendation, human approval, Start Work, troubleshooting, resolution, SLA, notification, or customer-mail business logic.

## Layout

- **Newest-first Activity timeline:** The top of the page shows the most recent internal AI case progress events, not six separate lengthy histories. Entries are sourced from `task_logs` for the current case with `source` in `ai_agent`, `ai_assisted`, `ai_resolution_review`. Events outside these sources are deliberately excluded.
- Each entry shows a readable **day, month, year and 24-hour time with seconds**, as well as the configured application timezone (`config('app.timezone')`). A `<time datetime="...">` element supplies a machine-readable ISO date.
- An entry also identifies the staff member when recorded, or displays **System / AI Agent**, and distinguishes system audit events from staff-reviewed notes.
- Long descriptions are truncated initially, with an optional **Read full activity** disclosure.
- The first 20 events are loaded, newest first. **Show older activity** adds 20 events at a time, up to 100 events. The timeline refreshes automatically.
- **Timeline filters**: All activity, Analyses, Approvals, Reviewed notes, and Customer emails. Filtering is scoped to the selected case, uses stable internal event keys / source types (not free-text matching), and resets the 20-event page to avoid confusing gaps. All views retain newest-first ordering.
- **Open related workflow** is shown where an event can be mapped reliably to a corresponding action panel: analysis, category, engineer assignment, Start Work, troubleshooting or resolution. Clicking the link opens the panel without closing other sections; its URL fragment points to the appropriate heading. Unknown events remain readable in the timeline without an invented destination.
- **Actions and reviews** are grouped into six accessible, expandable sections: case analysis, category approvals, engineer assignment, Start Work, troubleshooting review, and resolution readiness. Each toggle is a real button with `aria-expanded` and `aria-controls`. Open state is stored in the parent Livewire component instead of native `<details>`, so the 8-second polling refresh no longer collapses a section mid-task. Several panels can remain open simultaneously. Existing Livewire components and their validation/permission gates are unchanged.
- The old decorative, non-functional **Use Draft** button is no longer shown. Customer response suggestions are clearly labeled **Not sent**.

## Case workflow overview (read-only)

A six-stage **Case workflow overview** is displayed before Needs attention and the historical timeline. It helps a reader see where the current case stands without opening every panel.

1. **Case analysis**: displays the latest Case Assistant analysis state (completed, pending/processing, failed or absent).
2. **Category review**: displays an unexpired pending category approval or a completed AI category execution; an already populated case category alone does not count as AI approval.
3. **Engineer assignment**: shows the case's current assigned engineer (which may have been assigned manually), otherwise a pending assignment proposal or unassigned state.
4. **Work started**: shows the actual case state if it has moved beyond Open, without claiming the transition was performed by AI. Pending Start Work proposals are shown when the case remains Open.
5. **Troubleshooting review**: shows whether a staff-reviewed internal troubleshooting note exists.
6. **Resolution**: shows actual resolved/completed state, a saved-but-not-resolved handoff, or no handoff.

The cards display their relevant recorded dates/times in the configured application timezone, use the existing light/dark colour tokens and responsive columns, and include **Open stage** links that expand the existing Livewire section without executing an action. These are *indicators of recorded facts*, not a strict mandatory sequence or a claim that AI independently accomplished the work.

The overview is derived from the current case, case-scoped AI analyses and proposals, and case-scoped internal notes. It changes on automatic refresh and never updates case data.

## Needs attention (live status)

A compact **Needs attention** panel now appears above the historical timeline. It is read-only and draws from current operational tables rather than old AI activity logs:

- Pending unexpired category, engineer assignment, and Start Work proposals awaiting human approval.
- Failed execution records for those actions.
- The latest Case Assistant analysis, but only if its current status is failed (an older failed analysis does not stay flagged after a successful re-analysis).
- An AI-assisted resolution email whose outbox status is `failed` or `skipped`. Pending/sending/sent email statuses remain in the Resolution Readiness panel rather than appearing as failures.

Each item shows its recorded date/time and a **Review workflow** link. The panel is scoped to the current case; it does not submit approvals, resolve cases, or resend email. It shows up to 12 recent matching items. The related workflow retains its normal permission gates, while the attention panel requires case-view permission.

## Appearance

- The layout reflows from phone to desktop using responsive width and grid classes.
- The shell has explicit light and dark palettes with CSS custom properties.
- `@media (prefers-color-scheme: dark)` follows the browser/device preference; the app's `.dark` class forces dark, while `.light` forces light.
- Form controls and nested approval panels receive contrast fixes. Focus outlines and text labels remain visible, and reduced-motion preferences are respected.

## History and scope

The activity timeline reads the same private Case Progress records used by the Phase 7–10 activity logger; it does not duplicate, mutate, or infer historical actions. For earlier AI actions, the **optional** `php artisan ai:backfill-case-progress` command can reconstruct events for which historical audit records exist. Run `--write` only after backing up the database.

Timeline queries require permission to **view** the selected case and only fetch records linked to its case ID. Only the last 100 entries are displayed in the embedded UI; the regular Case Progress tab contains the fuller history.

## Local verification

```bash
git pull --ff-only origin feature/ai-agent-approvals-v1
php artisan optimize:clear
php artisan test --filter=AIAssistantTimelineTest
php artisan test --filter=AITroubleshootingReviewTest
php artisan test --filter=AIResolutionReadinessTest
php artisan test --filter=AIWorkStartApprovalTest
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIProgressLoggingTest
```

Browser acceptance:

1. Open an existing case with several AI actions and confirm **latest event at the top** with correct date/time and timezone.
2. Expand each of the six workflows. Verify category approvals, engineer assignment, Start Work, troubleshooting review, and the optional resolution checkbox remain functional.
3. Test a narrow mobile viewport, browser/device automatic dark mode, explicit application dark mode, and light mode.
4. Verify the timeline updates after a new action and that **Show older activity** reveals history rather than navigating away.
5. Expand **Category approvals** or **Troubleshooting review** and keep it open for at least 20 seconds (several polling cycles). The panel must remain open and usable; only clicking its toggle should close it.
6. Select each activity filter, checking that only matching records are displayed and that the newest matching event remains first. Select **All activity** to restore the full view.
7. Click **Open related workflow** on a category, troubleshooting or notification event. The correct panel should expand and stay expanded across polling; no approval or resolution should run automatically.
8. For **Needs attention**, create pending and expired approvals, a failed execution, and a failed resolution email in test data. Confirm that only current relevant items appear, and that resolving/fixing the underlying record removes the alert after refresh.
9. For **Case workflow overview**, confirm that an unassigned Open case shows no engineer or started work, that a saved resolution handoff alone does not mark the case resolved, and that an actual case resolution updates the overview after refresh. Check **Open stage** links and small-screen layout.

These changes are on `feature/ai-agent-approvals-v1` / draft PR #29, **not merged into main**. New tests require execution in your local environment.
