# SLA Calculation Service — Audit, Fixes and Test Plan

**Scope:** `packages/Simplydigital/CaseManagement/src/Services/SLACalculationService.php`, its existing public-holiday helper and existing pause/resume callers. Applies to both normal Case Management and the staff-controlled SLA stage in AI Case Assistant.

**Status:** Source changes and regression tests committed to `feature/ai-agent-approvals-v1`; tests must still be run against the local application/test database. No production data has been changed.

## Findings and corrections

| Finding | Consequence | Correction |
| --- | --- | --- |
| `calcRespondByTime()` stopped after 20 calendar iterations, then returned a fabricated date | Large SLAs or long nonworking periods could receive an incorrect deadline | Use a bounded 10-year daily traversal and **throw** if a valid deadline cannot be reached |
| Working-time calculations accumulated whole minutes, truncating seconds per segment | Repeated pauses could lose up to 59 seconds per segment and create drift | Calculate **working seconds** first; `workingMinutesBetween` is retained as a whole-minute compatibility wrapper |
| `calcCustomRespondByTime()` overwrote configured SLA `startDay` with the day of resumption | Resuming on Tuesday could change a Monday-only SLA into a Tuesday-through-Monday schedule | Keep the original SLA contract's working-day range unchanged |
| Holiday list was preloaded only around the current year | Cases dated farther back/ahead could miss public holidays | Compute/cache holidays for the calendar year of each date being evaluated |
| May Day, spring and summer bank-holiday switches used incorrect weekday mappings | Some dates were not Mondays | Use first Monday of May, last Monday of May and last Monday of August |
| Deadline logic mixed PHP timestamps and calendar days with possible rollover effects | End-of-day, weekend and DST transitions were fragile | Use Carbon local-day iteration and local wall-clock service start/end boundaries |
| No validation of start/end hours, and the iteration fallback hid impossible configurations | A wrong same-day/overnight window could silently calculate a wrong SLA | Reject empty/inverted/overnight service windows with a descriptive exception (a zero end-time retains its historical end-of-day meaning) |
| Missing case SLA agreement was accessed without guarding | Null property errors obscured the setup problem | Raise a clear domain exception |
| Pause allowance could fall back to recalculating against ticket creation date | Repeated pause cycles could charge previously paused intervals again | Require saved response/fix deadlines, then calculate remaining allowance **from the current deadlines** |
| Existing `CaseBuildCalcTest` depended on live case ID 11 and debugging dumps | The test could not run reproducibly in an isolated test database | Replace it with a deterministic calculation test |

## Algorithm and semantics

The calculator uses the policy's configured **working weekdays** and **daily start/end hours** to consume the SLA response/fix allowance in **seconds**. If a deadline extends beyond today's service hours, its remaining allowance is carried forward across eligible future working days. A deadline landing exactly on closing time is valid.

- A duration of zero returns the supplied starting instant (an immediate deadline).
- A value of zero for `service_end_time` retains the existing full-day interpretation (`24:00`); zero `service_start_time` is midnight.
- The same-day window must be valid and non-empty. **Overnight cover**, such as 18:00–06:00, is **not supported by this change** and is now rejected instead of silently miscalculated. Such SLAs need a separate reviewed overnight-window algorithm.
- `includePublicHoliday = true` currently means **exclude public/bank holidays from working time** in the existing service. This naming is potentially confusing and should be confirmed with SLA owners before changing its meaning.
- The holiday helper currently implements a simplified **England/Wales-style calendar**, not configurable regional or contractual calendars. It does not cover all exceptional bank-holiday changes (for example, ad hoc national holidays) or other UK jurisdictions/countries.
- Existing deadlines already stored in `sla_tasks` and historical SLA pause rows are **not automatically updated**. Manually audit representative cases before considering any repair or migration.

## Verification

```bash
git pull --ff-only origin feature/ai-agent-approvals-v1
php artisan optimize:clear

php artisan test --filter=SLACalculationRegressionTest
php artisan test --filter=CaseBuildCalcTest
php artisan test --filter=AISlaPauseWorkflowTest
php artisan test --filter=CaseStatusAggregationTest

# After focused tests pass:
php artisan test
```

The regression suite covers:

1. Exact service closing time, weekend carryover and after-hours starts.
2. SLA durations beyond 20 calendar days.
3. Second-level time precision, including fragmented time across a weekend.
4. May bank holidays, holiday checks outside the present calendar year, and policies that include/exclude holidays.
5. Daylight-saving spring transition (keeping a 09:00 local service start at 09:00).
6. Resuming on a day outside the contractual working week without changing that week.
7. Invalid/overnight service windows, negative allowance and absent SLA agreement.
8. Existing staff-controlled pause/resume/cancel and dashboard status regression tests.

**Operational precautions:** The code can affect future case SLA due dates and resume calculations. Review any production SLA agreement with nonstandard service hours (particularly overnight shifts and regional holidays), run the full test suite, and validate test cases against expected contractual deadlines before deployment. Do not mass-recompute existing SLA due dates without a specific approved data-reconciliation plan.

See also [AI SLA Pause/Resume Workflow](AI_SLA_PAUSE_RESUME_WORKFLOW.md) and [Case Status Calculation Review](CASE_STATUS_CALCULATION_REVIEW.md).
