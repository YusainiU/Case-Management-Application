# AI Case Assistant — Responsive timeline UI

This is a presentation-only redesign of the Case Assistant's existing Livewire page. It does **not** change the AI recommendation, human approval, Start Work, troubleshooting, resolution, SLA, notification, or customer-mail business logic.

## Layout

- **Newest-first Activity timeline:** The top of the page shows the most recent internal AI case progress events, not six separate lengthy histories. Entries are sourced from `task_logs` for the current case with `source` in `ai_agent`, `ai_assisted`, `ai_resolution_review`. Events outside these sources are deliberately excluded.
- Each entry shows a readable **day, month, year and 24-hour time with seconds**, as well as the configured application timezone (`config('app.timezone')`). A `<time datetime="...">` element supplies a machine-readable ISO date.
- An entry also identifies the staff member when recorded, or displays **System / AI Agent**, and distinguishes system audit events from staff-reviewed notes.
- Long descriptions are truncated initially, with an optional **Read full activity** disclosure.
- The first 20 events are loaded, newest first. **Show older activity** adds 20 events at a time, up to 100 events. The timeline refreshes automatically.
- **Actions and reviews** are grouped into six accessible, expandable sections: case analysis, category approvals, engineer assignment, Start Work, troubleshooting review, and resolution readiness. Existing Livewire components and their validation/permission gates are unchanged.
- The old decorative, non-functional **Use Draft** button is no longer shown. Customer response suggestions are clearly labeled **Not sent**.

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

These changes are on `feature/ai-agent-approvals-v1` / draft PR #29, **not merged into main**. New tests require execution in your local environment.
