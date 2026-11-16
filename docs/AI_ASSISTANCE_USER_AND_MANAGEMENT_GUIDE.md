# AI Assistance in Case Management
## A practical guide for case handlers, team leaders and business decision-makers

**Audience:** Service desk staff, engineers, case managers, operational managers, IT leadership and organisations evaluating the Case Management Application.

**Scope:** The AI-assisted features implemented on the `feature/ai-agent-approvals-v1` development branch. This guide describes the intended operational workflow; the application is in beta and requires organisation-specific validation before production deployment.

---

## 1. Executive overview

**AI Assistance is a decision-support layer inside the normal case management process.** It reads relevant case information, suggests possible actions and helps staff prepare consistent records. It does **not** take ownership of a case, determine whether a technician actually performed work, or independently decide to change case details or resolve a customer issue.

A company can continue managing cases manually using its usual case views, staff assignments, progress notes, SLA processes, customer communications and resolution controls. AI Assistance offers an additional route to selected parts of that process when an authorised member of staff chooses to use it.

The operating principle is simple:

> **The AI recommends; authorised people review and decide; the application's existing case-management rules govern any resulting change.**

Typical potential benefits include a quicker first understanding of a case, more consistent categorisation, support when choosing an engineer, structured troubleshooting notes, better visibility of outstanding decisions and a clearer handoff toward resolution. These are **potential operational benefits**, not measured performance claims; an organisation should evaluate them during a pilot.

## 2. AI Assistance compared with manual case handling

| Case management activity | Existing manual approach | AI-assisted option | What remains under human control |
| --- | --- | --- | --- |
| Register a case | Staff/customer records the issue through existing channels | AI reads the case after it exists | Case creation, accuracy of details and customer relationship |
| Understand the issue | Handler reads description and history | Case Assistant generates a summary, warnings and suggested next actions | Checking facts, diagnosing the issue and deciding what matters |
| Categorise | Staff chooses or changes category | AI suggests a category; staff can prepare an approval proposal | Approval and any resulting category change |
| Assign an engineer | Dispatcher reviews skills, capacity and availability | Rule-based recommendations consider recorded skills and open-case workload; optional AI-assisted skill matching can be separately enabled | Confirming suitability, permissions, availability and assignment |
| Start work | Authorised staff moves the case into the appropriate working state | A Start Work proposal can be prepared and reviewed | Confirming work really began and authorising the state change |
| Investigate and document | Engineer performs checks and writes progress notes | A completed analysis can be turned into an editable troubleshooting draft | Performing tests, correcting suggestions and signing off the note |
| Review resolution | Staff confirms the fix, verification and remaining risks | Resolution Readiness structures a handoff using a reviewed troubleshooting note | Whether and when to resolve the case |
| Notify the customer | Existing communication workflow | An explicitly authorised AI-assisted resolution path can queue a customer notification | Resolution decision, recipient/data review and follow-up on delivery problems |
| Review the case history | Staff reads the Progress tab | AI timeline, workflow overview and attention indicators provide extra navigation | Audit review, accountability and management decisions |

**Neither route requires staff to follow every AI stage.** A suitable manual workflow remains available, and a suggestion is not a command.

## 3. Who does what?

- **Case handler / service desk:** Records an accurate case, reviews summaries and suggestions, monitors progress, communicates with the customer, and escalates uncertain or urgent issues.
- **Engineer / technician:** Verifies the technical facts, performs troubleshooting, records work actually completed, identifies risks and supplies evidence for resolution.
- **Approver / team leader:** Reviews proposed changes, challenges unsuitable recommendations, approves or rejects within their role and confirms that actions are appropriate.
- **Case manager / operations manager:** Monitors outstanding approvals, failed actions, SLA performance, case ownership and the quality of documented resolutions.
- **System administrator:** Configures roles, the optional AI provider, background processing and execution switches; monitors provider costs, access, notifications and operational logs.
- **AI Assistant:** Supplies drafts, recommendations, status summaries and navigation from recorded data. It is **not** an engineer, approver, manager or legal/business decision-maker.

Available actions depend on the organisation's roles and permissions. Being able to view an AI recommendation does not itself give permission to approve or execute an action.

## 4. The case journey: from a new issue to completion

### Step 1 — Create or open a case normally

Use the normal case-management interface. Record the customer, issue summary, description and other relevant information as accurately as possible. Attachments, SLA handling and other existing functions continue to use their established process.

**Manual handling:** A staff member reads and categorises the case directly.

**With AI Assistance:** Open **Case Assistant (AI)** in the case. If an AI provider has been configured and the user is authorised, choose **Analyze Case**. Analysis is an explicit request and may run through a queue. Status changes from pending/processing to completed or failed; the interface updates automatically.

The analysis may include a summary, suggested category, possible next actions, warnings or uncertainty. These are generated interpretations of the available context, not verified facts.

**Good practice:** Review the original case description and available evidence before acting on the summary. Do not rely on AI-generated root-cause statements without technical validation.

### Step 2 — Review case category

**Manual handling:** An authorised person selects an appropriate category.

**With AI Assistance:** The completed analysis may suggest a category. The user can prepare a **category change proposal** where appropriate. An authorised reviewer then approves or rejects it. If the recommendation already matches the case category, a change proposal is unnecessary.

Approval and execution are distinct. Category execution only runs when the relevant execution capability has been deliberately enabled; otherwise the approval is recorded without changing the case. The application validates the proposal and case state before any enabled execution.

### Step 3 — Review engineer assignment

**Manual handling:** A dispatcher selects the engineer, taking account of expertise, workload, location, schedule and business priorities.

**With AI Assistance:** The assignment section provides a shortlist based on engineer records. The initial recommendation mechanism uses recorded skill matches and open-case workload, rather than a claim of real-time availability. Optional semantic matching is a separate opt-in capability. The selected candidate is submitted as a proposal for human review.

**Important:** A text-matched skill does not prove competence, availability, certification or suitability for a particular customer. Managers should still check staffing constraints and any contractual requirements.

As with category changes, actual AI-assisted assignment requires separate execution enablement and relevant permissions. Staff may continue assigning engineers manually.

### Step 4 — Start Work

**Manual handling:** An authorised handler or engineer records that work has started and follows the normal case-state procedure.

**With AI Assistance:** **Start Work** offers a proposal-and-approval route for moving an eligible case from **Open** into **In Progress**. The system records the decision and only performs an enabled execution after the relevant checks.

Do not approve Start Work simply to advance a dashboard stage. It should represent genuine operational activity.

### Step 5 — Troubleshooting Review

This is where the AI's original analysis becomes a practical working aid.

**Manual handling:** An engineer conducts diagnostic checks and writes their own progress note.

**With AI Assistance:**

1. Open **Troubleshooting Review**.
2. Select **Prepare editable troubleshooting note** based on the **latest completed Case Assistant analysis**.
3. Read the suggested checks and warnings.
4. Replace placeholders with verified findings, tests performed and actual next steps.
5. Confirm that unsupported, outdated or unperformed actions have not been presented as facts.
6. Save the reviewed note to **internal Case Progress**.

**You do not need to re-analyse solely because a category, assignment or Start Work action updated the case after the original analysis.** The application warns that the case has changed and requires the reviewer to check whether the advice remains relevant.

If the case changes **after the draft is prepared**, or a newer analysis replaces it, the save is blocked and the draft must be prepared again. If a newer analysis is still pending or failed, the troubleshooting tool does not silently use a superseded older result.

Saving this reviewed note **does not** resolve the case, change its assignee, update SLA tasks or send a customer email. It records a staff-attributed internal progress note.

#### Optional — Recommend Solution (separate, chargeable AI request)

Troubleshooting Review also offers **Recommend Solution** after the initial Case Assistant analysis has completed.

This button is **not** the same as **Re-analyze Case**. The ordinary analysis summarises and categorises the problem and offers general next actions; **Recommend Solution** asks the configured provider specifically for a potential repair approach, why it might help, diagnostic checks, verification steps and remaining uncertainties.

The application warns the user that this is an additional provider call that can consume AI tokens/credits and incur charges. The user must acknowledge that warning before submitting the request. A queue worker processes the request and the panel displays its status and results.

The recommendation is **advisory only**. It does not attempt a repair, write staff-verified findings, resolve the case or notify the customer. An engineer must still inspect the evidence, verify each proposed action and document what was genuinely done.

This request uses existing AI provider credentials and case-analysis permissions, plus case update authority; it does **not** depend on enabling AI execution switches for category changes, engineer assignment or Start Work. Before use, confirm company approval to send the case description and initial analysis data to the external provider.

### Step 6 — Resolution Readiness

**Manual handling:** Staff verifies the fix, documents the outcome and uses the normal resolution process when satisfied.

**With AI Assistance:** Resolution Readiness starts from an **existing staff-reviewed troubleshooting note**. The reviewer supplies:

- The fix **actually applied**.
- The verification **actually performed**.
- Any remaining risks or follow-up actions.
- An explicit confirmation that the details are verified.

By default, saving creates an **internal resolution handoff only**. The case remains unresolved.

Where permitted, the user can separately opt to **resolve the case** and supply the required resolution details. This uses the application's standard case-resolution service and its SLA checks—not an independent AI-generated status update. When that explicit resolution path succeeds, the associated customer notification is queued after the database transaction commits.

A saved handoff is not the same as a resolved case. A queued notification is not proof of delivery.

### Step 7 — Review customer communication and follow-up

The Resolution Readiness panel exposes the status of its resolution notification, which may be pending, processing/sending, sent, failed or skipped depending on the worker outcome. A failed or skipped record is an issue for staff to investigate; it is **not** an instruction to resend blindly.

Outside this optional resolution flow, the application's existing manual customer-notification and Progress Log communication controls remain available.

### Step 8 — Close out and retain the history

The **Progress** tab is the broader case history: staff notes, customer comments, operational updates and AI events. Its redesigned timeline groups entries by date, shows precise times, and offers filters for internal, customer, AI and urgent activity.

The **AI Assistant Activity timeline** focuses on AI-related events and reviewed notes. These are views of recorded case activity, not a substitute for the full case record.

## 5. Understanding the AI Assistant screen

| Screen section | Purpose | Does it change the case? |
| --- | --- | --- |
| **Suggested Next Step** | Points to a relevant workflow based on current case information and outstanding issues | No—navigation advice only |
| **Case Workflow Overview** | Shows six recorded stages: analysis, category, assignment, work started, troubleshooting and resolution | No—status display only |
| **Needs Attention** | Highlights current pending approvals and recorded failures | No—review prompt only |
| **Activity Timeline** | Shows AI-related events newest first, grouped by calendar date; supports filters and older history | No—history only |
| **Actions and Reviews** | Contains the actual analysis, approval, troubleshooting and resolution controls | Some actions can change data, but only after permission checks and relevant approvals |
| **Focus View / Full View** | Controls how much background detail is visible | No—presentation only |

The interface provides **Open stage**, **Open related workflow**, and **Open suggested workflow** links. These expand the corresponding panel; they do not initiate an approval or AI request. Full View shows more history; Focus View keeps the action-oriented information prominent. Neither changes the underlying case.

The application uses automatic refresh for the AI Assistant screen, while preserving expanded workflow panels and display preferences during polling.

## 6. Human approval and execution safeguards

**There are three separate concepts:**

1. **Recommendation:** AI or rules suggest a possible course of action.
2. **Human approval:** An authorised person accepts or rejects a specific proposal.
3. **Execution:** The application makes the approved change only if that action type is enabled and the current case still satisfies its checks.

The implementation includes checks for case-scoped access, reviewer permissions, proposal validity and expiry, and the relevant case snapshot before changes are applied. Approval records and execution outcomes are logged.

The following execution switches are independent and **disabled by default**:

| Capability | Configuration switch |
| --- | --- |
| AI-approved category changes | `AI_AGENT_EXECUTION_ENABLED` |
| AI-approved engineer assignment | `AI_ENGINEER_EXECUTION_ENABLED` |
| AI-approved Start Work | `AI_WORK_START_EXECUTION_ENABLED` |

The optional semantic matching feature has a separate `AI_ENGINEER_SEMANTIC_ENABLED` switch. A configured AI provider and appropriate queue worker are required for generated analyses and related asynchronous processing.

**Do not assume that approving a proposal immediately changes a case.** Check whether execution is enabled and verify the recorded outcome. The app's normal manual controls operate independently of these switches.

System-generated AI audit events are internal and protected from editing/deletion through the Progress Log. Staff-reviewed troubleshooting and handoff notes are separately attributed records and follow the existing internal note process.

## 7. Worked example: a customer cannot log in

**Customer report:** “The portal returns an HTTP 500 error when I sign in.”

**Manual route:** A handler records the issue, selects the category, finds an engineer and assigns it. The engineer examines logs, tests a fix, records the results, and staff resolve the case after verification.

**AI-assisted route:**

1. A handler records the same issue in a normal case.
2. An authorised staff member requests a Case Assistant analysis.
3. The analysis summarises the symptom and suggests checks such as reviewing application error logs. **It does not know the actual root cause.**
4. Staff review any category suggestion. If a change is appropriate, an approval proposal is prepared, reviewed and—only if separately enabled—executed.
5. The dispatcher compares the engineer recommendation with real staffing needs, then uses either the proposal process or normal manual assignment.
6. Once work has genuinely begun, staff record Start Work.
7. The engineer opens Troubleshooting Review using the initial completed analysis. The engineer actually checks the logs and documents what they found; unsupported suggested checks are removed.
8. Staff create a resolution handoff describing the actual fix and successful verification. They may keep the case open for further checks, or deliberately opt into the normal resolution process when its requirements are met.
9. Staff monitor the customer-notification outcome and the Progress Log.

**Result:** AI may reduce the work of assembling suggestions and drafting consistent notes. The engineer still diagnoses the issue; the case manager still owns the operational decision.

## 8. What AI Assistance does *not* do

- It does **not** independently decide the technical root cause or prove that a test was performed.
- It does **not** automatically replace a case handler, engineer or approver.
- It does **not** automatically approve all category, assignment or Start Work changes.
- It does **not** guarantee real-time engineer availability, certification, geography, skill proficiency or capacity.
- It does **not** guarantee generated summaries are complete, accurate, unbiased or suitable for a specific customer.
- It does **not** automatically resolve a case merely because a draft or handoff was saved.
- It does **not** guarantee an email is received merely because notification was queued.
- It does **not** replace the organisation's SLA, data-protection, customer-communication, escalation or incident-management policies.

The application's AI support should be evaluated as **assistance to employees**, not autonomous end-to-end case management.

## 9. What managers should evaluate before adopting it

### Operational suitability

- Are your case categories, engineer skills, priorities and escalation rules current and reliable?
- Who is allowed to request analyses, prepare proposals, approve changes and resolve cases?
- Which tasks genuinely benefit from generated suggestions? Which should remain purely manual?
- Can the team clearly distinguish **AI suggestion**, **staff-verified evidence**, **approved change** and **completed case**?
- Is there an agreed process for unavailable AI service, failed jobs, inaccurate suggestions and notification failures?

### Security, privacy and governance

- Determine what customer and case information may be sent to the configured external AI provider. The Case Assistant has limits for comments and output size, but those limits are **not a substitute for a data classification or privacy review**.
- Review supplier terms, data location, retention, access controls, confidentiality requirements and relevant contracts before enabling production AI processing.
- Protect API keys, configure role-based access and test that users cannot view or approve cases outside their authority.
- Establish audit retention, incident response and change-control procedures.
- Decide how staff will correct or discard inaccurate AI-generated material, and how supervisors will review its use.

### Technical and commercial readiness

- Confirm the organisation can operate the Laravel application, database, background queues and notification infrastructure reliably.
- Evaluate provider uptime, latency, API usage and cost under expected workloads.
- Confirm the configured execution switches match company policy. Begin with execution disabled and enable individual action types only after business acceptance.
- Test on representative cases (including ambiguous, sensitive, urgent, multi-step and failed cases).
- Validate performance, accessibility, mobile use, permissions, SLA effects and email behaviour in the organisation's own environment.

## Ubuntu deployment: background AI jobs

The AI Assistant's **Analyze Case**, **Re-analyze Case** and **Recommend Solution** requests are processed by Laravel background queue jobs. On an Ubuntu server using the database queue, an administrator should configure a continuously running queue worker under **Supervisor**. Without a worker, requests can remain pending.

For installation, correct `DB_QUEUE_RETRY_AFTER` configuration, a sample Supervisor program, deployment/restart commands and diagnosis of **`ERROR (spawn error)`**, see the **[Ubuntu Supervisor Queue Worker Guide](UBUNTU_SUPERVISOR_QUEUE_WORKER_GUIDE.md)**.

This is an infrastructure requirement, not an AI permission or approval bypass. Users still need the normal case permissions and any required AI provider credit consent.

## 10. Suggested pilot programme

A measured pilot is preferable to turning on every capability at once.

| Pilot stage | Recommended approach | Evidence to gather |
| --- | --- | --- |
| **1 — Baseline** | Record current manual performance on a representative sample | Handling time, first-response quality, routing accuracy, reassignments, reopened cases |
| **2 — Read-only AI** | Use case analysis and recommendations without automated execution | Staff usefulness ratings, correct/incorrect suggestions, provider cost, latency |
| **3 — Approved proposals** | Trial category, assignment and Start Work proposals under named reviewers | Approval/rejection rates, time to decision, routing errors, audit completeness |
| **4 — Reviewed documentation** | Trial troubleshooting drafts and resolution handoffs | Note completeness, corrections required, unsupported claims, quality of resolution evidence |
| **5 — Controlled execution** | Enable only individually approved action switches, initially on limited case types | Execution success/failure, stale proposals, permission checks, manual overrides |
| **6 — Go/no-go review** | Compare the pilot with the baseline | Measurable net benefit, cost, risk, staff feedback and customer outcomes |

Suggested metrics include **median time to categorise**, **time to assign**, **percentage of suggestions changed by staff**, **approval rejection rates**, **reopened cases**, **completeness of troubleshooting notes**, **notification failure rates**, **SLA breaches**, **cost per analysis** and **staff satisfaction**. No improvement targets or numerical ROI should be claimed without actual pilot measurements.

**A sensible launch decision:** Start with optional read-only assistance, teach staff to verify suggestions, keep the manual process available, and expand execution only after the company approves clear operating rules.

## 11. Quick-start checklist for a case handler

1. Open and read the case as you normally would.
2. Use **Case Assistant (AI)** if AI-generated insight would help; request analysis only when appropriate.
3. Compare suggestions with the customer report and existing evidence.
4. Review category and engineer recommendations; seek approval for any proposed changes rather than assuming they have happened.
5. Record Start Work only when work actually begins.
6. Prepare a troubleshooting note from the completed analysis, edit it to reflect **real** findings and confirm your review.
7. Document the fix, verification and remaining risks before preparing a resolution handoff.
8. Resolve only if authorised and the normal resolution requirements are met.
9. Check customer-notification status where relevant.
10. Use the full **Progress** history for case notes and customer activity, and the AI timeline for AI-specific decisions and audit events.

## 12. Frequently asked questions

**Must every case use AI Assistance?**  
No. Manual handling remains available. AI is an optional aid subject to configuration and permissions.

**Does AI automatically assign the engineer?**  
No. The initial recommendation is based on recorded information; an authorised user must select the candidate and follow the approval process. Execution requires its own enablement.

**Can the AI tell me the problem is fixed?**  
No. Only real checks and staff verification establish that. The resolution workflow explicitly asks for the actual fix and verification.

**Do I need to repeat an AI analysis after the category or assignment changes?**  
Not merely because those normal workflow updates occurred. Troubleshooting Review can reuse the latest completed analysis, with a warning to check it against the current case.

**Will saving a troubleshooting note or resolution handoff close the case?**  
No. Troubleshooting review saves an internal note; resolution handoff defaults to an internal record. Resolving is a separate, deliberate, authorised option.

**If we disable automated execution, is the AI Assistant still useful?**  
Yes. Analysis, recommendations, human approval records, reviewed notes and the timeline can still support decisions. Execution switches control specific business updates.

**Can we prove what AI suggested and what staff decided?**  
The implementation records AI lifecycle events, proposals, approvals, execution outcomes and staff-reviewed notes in its case history and related records. Management should still validate audit retention and reporting against its compliance needs.

**What if the AI service fails?**  
The original manual case workflow remains the operating fallback. Staff can work with existing case details, assignments and progress notes. A failed latest analysis is shown for review; staff should not treat it as completed.

**Is it ready to deploy company-wide?**  
That requires a separate business, technical, privacy and security acceptance process. The repository currently describes the overall application as **beta**, and these AI-assisted features are on a development feature branch, not merged to `main`.

## 13. Summary for decision-makers

This application combines conventional case management—ownership, permissions, task progress, SLAs, internal/customer communication and resolution—with **optional AI guidance**. Its defining safeguard is that AI-generated advice is separated from **human decisions** and **authorised execution**.

For an organisation considering adoption, the strongest initial use case is **helping staff understand, route and document cases more consistently while maintaining the existing manual workflow**. Advanced execution can be evaluated later, one controlled action type at a time.

**The software should support professional judgement, not substitute for it.**

---

### Documentation status

Prepared against the implementation on branch `feature/ai-agent-approvals-v1` (draft PR #29), November 2026. This is a user and management guide, not a guarantee of regulatory compliance, production readiness or measured business savings. No code or configuration changes are required to read it.
