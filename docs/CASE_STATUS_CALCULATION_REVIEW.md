# Case Status Calculation — Review and Corrections

**Scope:** `CaseTicketUtilityServices::retrieveTicketStatuses()` → `CaseRetrieveTicketStatus` → `CaseStatus::buildStatus()` and `Dashboard::buildStatus()`, with the Case Information Panel's status indicators.

This review checks the actual implementation of the Case Management package and corrects logic that could produce wrong numbers on the dashboard or misleading SLA/case status labels.

## Issues found and corrected

| Original behaviour | Why it was wrong | Correction |
| --- | --- | --- |
| `getTotalOpenCases()` used a sequence of `orWhere` conditions | Almost any row satisfied at least one condition, including closed/cancelled records | Count only states in `CaseState::openStates()`, excluding cases with resolved/closed timestamps |
| `retrieveTicketStatuses()` only excluded Closed and Cancelled | Completed cases could remain in the active status aggregates | Include open states and Resolved for its separate dashboard card; exclude Completed, Cancelled and Closed |
| `noUpdate` called `$ticket::doesntHave('taskLogs')->count()` | This queried the *entire tickets table*, not the current case, and treated system logs as staff updates | Compute from the current case's staff internal update logs (including reviewed `ai_assisted` progress notes) |
| `CaseStatus` and `Dashboard` computed `currentlyWorking` from `resolved` | Resolved is not synonymous with actively working | Sum the actual `currently_working` case flag for still-active cases |
| Status service loaded selective SLA columns but not active pause relation | Paused cases could be treated as running, and SLA accessors could not reliably use missing timestamps | Load `slaTasks.activePause` and compute pause state from the actual active pause record |
| Breach flags mixed aggregate scopes with `fixBreached()` | Its `orWhere` logic could classify the wrong SLA and didn't always align with response breach flags | Compute the response/fix breach against the current case's own SLA, excluding frozen/closed/resolved cases |
| `closed` compared an enum-valued state to a raw string for Cancelled | Could mark cancelled or closed cases incorrectly | Use `CaseState` enums and close timestamp |
| Small Case Information Panel assumed every case had an SLA task and respond-by deadline | Missing SLA data could throw a null-property error | Guard optional SLA-task and deadline rendering |
| `getHasInternalUpdateAttribute` and `latestInternalUpdate` used inconsistent log type strings | The Case Information Panel could show No Update while dashboard reported otherwise | Align both to configured `internal_update_log`, legacy `internal_update`, and reviewed `ai_assisted` notes |
| Per-case status built many extra queries for logs and visits | Dashboard and status panels repeated database queries | Eager-load case-scoped relationships once for batch retrieval and calculate aggregates from the loaded records |

## Metric definitions after the changes

- **Open cases**: case state belongs to `CaseState::openStates()` and both resolved and closed timestamps are absent.
- **Resolved**: state is Resolved or a resolution timestamp is present, without a closed/completed/cancelled state or closed timestamp.
- **Unassigned**: an active case without `assigned_to`.
- **No Update**: an active case without a staff internal update, including staff-reviewed AI troubleshooting notes; system-generated lifecycle logs do **not** count as staff updates.
- **SLA Breached**: an active case whose current (unpaused) SLA has an overdue, unsatisfied response or fix obligation, or recorded breach timestamp.
- **Near Breach**: the existing warning flag on an unpaused, active and not-yet-breached SLA.
- **Paused**: an active case with an `activePause` record.
- **Currently Working**: an active case's `currently_working` flag.
- **Site Visits**: visits without an `offsite_at` timestamp. Completed/offsite visits are not counted as outstanding.
- **Urgent Logs**: current case's task logs whose `require_attention` flag is true.

**Note:** The dashboard aggregates are *status counts*. Some indicators can overlap (for example, a paused case can also be unassigned). A Resolved case appears in the separate Resolved card, not the Open count.

## Performance and operational considerations

Bulk status retrieval now loads the case's SLA, task logs and visits in batches, instead of querying each case's log and visit tables separately. For very large organisations with long case histories, further optimisation using database `withCount`/`withMax` aggregates can be considered in a later iteration.

The code changes do not modify stored SLA records, historic case data or AI approval actions. They change how status indicators are **derived and displayed**.

## Local verification

```bash
git pull --ff-only origin feature/ai-agent-approvals-v1
php artisan optimize:clear
php artisan test --filter=CaseStatusAggregationTest
php artisan test --filter=AISlaPauseWorkflowTest
php artisan test
```

On a disposable case, compare the status panel with the dashboard before/after a staff progress note, assignment, pause, resume, resolution, and closure. Check that a case with no SLA task opens without an error.

**Tests have been committed but require local execution.** The branch remains `feature/ai-agent-approvals-v1`, draft PR #29; nothing is merged into `main`.
