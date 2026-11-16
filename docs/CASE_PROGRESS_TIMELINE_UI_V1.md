# Case Progress Log — Date-grouped timeline redesign

The existing **Progress** tab now uses the same navigation principles as the AI Assistant timeline while preserving all existing note-management controls.

## Visual behaviour

- **Newest first:** records are sorted by `created_at DESC, id DESC`, providing a stable order for entries written in the same second.
- **Date headings:** entries are grouped visually under one heading per calendar day (for example, **Wednesday, 11 November 2026**). Dates follow `config('app.timezone')`.
- **Exact timestamps:** each entry retains its author, type, reference number, source and a full `d M Y · H:i:s` timestamp. Machine-readable HTML `<time>` attributes are included.
- **Filters:** All activity, Internal updates, Customer activity, AI activity and Urgent attention. Filters operate on the existing **current-case task log relationship**, not global records.
- **Progressive history:** show the newest 20 entries; **Show older activity** reveals 20 more up to 100. Filtering resets this limit to 20 and preserves newest-first order. The full history remains in the database and is not deleted.
- **Light and dark:** the timeline uses the application's light/dark Tailwind palette, with additional `prefers-color-scheme` styling for the date headings and active filters. The layout remains responsive in the existing scrolling modal.

## Actions and safety

The existing create/edit/respond/delete controls and optional urgent flag, notify customer and visible-to-customer selections remain intact. Customer comment badges, internal response relationships, linked parent notes and AI audit labels remain visible.

AI system audit entries (`source=ai_agent`) **cannot be edited or deleted**. The existing authorization and server-side checks are unchanged. Timeline filter and pagination actions require case-view permission.

## Local verification

```bash
git pull --ff-only origin feature/ai-agent-approvals-v1
php artisan optimize:clear
php artisan test --filter=CaseProgressTimelineTest
php artisan test --filter=AIProgressLoggingTest
php artisan test --filter=AIAssistantTimelineTest
```

For browser checks: open a case's **Progress** tab, confirm newest-first grouping across several days, switch filters, use **Show older activity**, and verify creating/responding/editing/deleting a normal internal log still works. Confirm a customer-facing note and an AI audit entry retain their distinct labels; the AI audit should not display edit/delete controls. Test a narrow viewport and both light/dark modes.

Changes are committed on `feature/ai-agent-approvals-v1` in draft PR #29; nothing is merged to `main`.
