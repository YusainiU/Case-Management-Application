# Evidence-based semantic engineer skill matching

This optional enhancement supplements, rather than replaces, the exact skill and certification matching introduced in Phases 4 and 6. Nothing assigns a case without human approval.

## Enable on development

In a **development environment only**, with the configured AI provider and model working for Case Assistant:

```env
AI_ENGINEER_SEMANTIC_ENABLED=true
AI_AGENT_EXECUTION_ENABLED=false
AI_ENGINEER_EXECUTION_ENABLED=true
```

The last two flags remain independent of semantic analysis. You may leave engineer execution disabled to inspect suggestions without changing cases.

```bash
php artisan optimize:clear
php artisan queue:restart
php artisan queue:work --queue=default --tries=3
```

The queue name comes from `AI_CASE_ASSISTANT_QUEUE`; use that queue if you have customised it.

## Workflow

1. Open a case > **Case Assistant (AI)** > **Engineer Assignment Recommendations**.
2. Click **Analyse skill relevance with AI**. This is an **explicit request**, not an automatic per-page-load AI call. The prompt sends a shortened case short-description/description (maximum 4,000 characters) and up to 150 **active skill names**; **engineer names, engineer IDs and workloads are not sent**. The case description itself may contain customer data; use only if your provider/privacy policy permits sending it.
3. The existing queue worker calls the application's configured `AIService::generateStructured` provider. It requests a list of candidate registered skills, a verbatim short supporting quote from the case, a cautious rationale and high/medium/low confidence.
4. Each AI item is accepted only if its skill is in the provided active catalogue, its short evidence quote literally appears in the case, it has a nonempty bounded rationale, and confidence is high or medium. Low-confidence and unsupported responses are discarded. Evidence indicates **relevance**, not established diagnosis.
5. Validated semantic matches are joined locally to active engineers possessing the associated active skill. An engineer with an `Application Developer` skill could be shown against an `Application Portal login returns 500 Internal Server Error` case, with the exact `500 Internal Server Error` phrase quoted as evidence. This is a possible relevance signal, **not an assertion of root cause or guaranteed provider output**.
6. Candidates are sorted by total exact + semantic skill match count, then exact skill count (tie-break), matched valid certifications, and open-case count. Explanations identify which evidence is exact versus AI-suggested.
7. Selecting a candidate creates a pending human-reviewed proposal with a snapshot of the explanation. Approval, current permissions, staleness checks and the separate engineer execution switch still govern any mutation of `tickets.assigned_to`.

## Fallback and freshness

- Semantic matching is **disabled by default**. Without it, the exact skill/certification/workload ranking remains available and unchanged.
- If the AI provider fails, a bounded queue retry is attempted; any final failure is shown as a safe message, and exact matching remains available.
- Result cache is stored in existing `ai_analyses` table as `engineer_semantic_skills`. A hash of the analysed case text and skill catalogue prevents reusing stale evidence if the text or registered skill names change.
- No new migrations, permissions, or automatic assignments were introduced.
- The model is not a diagnostic authority. Reviewers must still evaluate whether a suggested engineer is genuinely suitable.

## Tests

Use the isolated test database:

```bash
php artisan test --filter=AIEngineerSemanticMatchingTest
php artisan test --filter=AIEngineerAssignmentTest
php artisan test --filter=AIActionApprovalTest
php artisan test --filter=AIApprovalReviewQueueTest
```

The semantic tests mock the provider and assert the Application Developer / HTTP 500 scenario, unsupported and low-confidence response filtering, snapshot invalidation, and authorised opt-in. They do **not** prove that a live provider will always generate the same semantic match. Run a browser acceptance test with the configured provider to verify real output.

This feature remains on `feature/ai-agent-approvals-v1` and is not merged into `main`.
