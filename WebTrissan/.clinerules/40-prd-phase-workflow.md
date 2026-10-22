# PRD-Driven Phased Execution

## Purpose
Execute product and coding work from explicit requirements, PRDs, acceptance criteria, and repository evidence. Maintain a durable phase checklist so work does not drift, repeat, or skip verification.

## When This Rule Applies
- Always apply when a PRD, specification, feature request, or acceptance criteria drives the implementation.
- Apply to multi-step changes spanning multiple files, layers, or tests.
- Skip the full workflow for trivial questions or genuinely one-line edits. Do not create ceremony that adds no value.

## Phase 1 — Understand and Establish the Baseline
Before implementation:
1. Read the relevant PRD/specification and acceptance criteria.
2. Inspect relevant repository files, architecture, existing conventions, dependencies, and tests. Use available project tools when useful.
3. Identify constraints, dependencies, affected components, and risks.
4. Separate confirmed requirements from assumptions and open questions.
5. Do not invent product behavior absent from the requirements.
6. Ask targeted questions only when an ambiguity materially affects implementation, security, data integrity, or acceptance criteria.

Exit criteria:
- Relevant requirements and acceptance criteria are recorded.
- Scope, dependencies, and material open questions are explicit.
- Existing implementation and constraints have been inspected sufficiently.

Do not begin feature implementation before Phase 1 is complete.

## Phase 2 — Create the Execution Plan
Create a persistent plan at:
`docs/implementation-plans/<task-slug>.md`

Create the directory if needed. Before creating a new plan, check whether an existing plan already belongs to this task and resume it when appropriate. Do not overwrite an unrelated plan.

The plan must contain:
- Task objective and source PRD/specification.
- Scope and explicit non-goals.
- Acceptance criteria.
- Ordered phases, each with an objective, checklist, dependencies, and exit criteria.
- Relevant files/components and planned validation.
- Risks, assumptions, and unresolved questions.
- Overall progress status: `Not started`, `In progress`, `Blocked`, or `Complete`.
- Current active phase and its status.

Use concise, actionable checkboxes:
- `[ ]` Not done.
- `[x]` Done and verified; include evidence when it is not self-evident.
- `[!]` Blocked; record the reason and what is needed to unblock it.

Split work into sensible phases based on dependencies. Typical phases may include design/data model, backend, frontend, integration, and verification, but adapt the plan to the actual task. Do not create unnecessary phases.

Exit criteria:
- The plan is concrete enough to execute.
- Every phase has verifiable completion criteria.
- Dependencies establish a clear execution order.
- Exactly one phase is marked as the current active phase during execution, unless work is explicitly blocked.

## Required Phase Checkpoints and Progress Reporting
For every task covered by this workflow, phase tracking must be visible in both the persistent implementation plan and concise user-facing progress updates. Updating the Markdown plan alone is not sufficient for progress reporting.

### Before Execution
1. After completing Phase 2, report the plan path, the current active phase, its objective, and the main checklist items to be completed next.
2. Before starting implementation changes, ensure Phase 1 and Phase 2 exit criteria are satisfied.
3. If the plan is missing, incomplete, or inconsistent with the current repository state, reconcile it before continuing.

### During Execution
1. Work only on the current active phase.
2. After meaningful progress, update the persistent plan with completed checklist items, changed files, validation results, and remaining work.
3. Provide a concise progress update identifying the current phase, what has been completed or verified, and the next relevant step.
4. Mark a checklist item complete only after performing the work and checking the evidence required by that item.
5. Do not claim an item or phase is complete merely because code was generated, a command was started, or a test was invoked.

### Phase Transitions
Before starting the next phase:
1. Verify every required checklist item and exit criterion for the current phase.
2. Run the narrowest relevant phase-level checks and record their actual outcomes.
3. Review the relevant diff for unintended changes.
4. Mark the phase `Complete` only when its exit criteria are satisfied. Otherwise keep it `In progress` or mark it `Blocked` with the reason.
5. Update the plan to identify the next active phase.
6. Explicitly tell the user that the previous phase is complete, summarize the evidence briefly, and name the next phase before beginning work in it.

Phase reporting does not require user approval at every transition. Continue automatically when the phase is verified and no material decision or permission is needed. Pause and ask the user only when a decision, missing information, or permission is actually required.

## Phase 3 — Execute Sequentially
1. Work only on the current active phase.
2. Do not start implementation tasks from a later phase until the current phase's checklist and exit criteria are satisfied.
3. Mark an item complete only after performing the work and checking evidence. Do not mark work complete merely because code was generated.
4. After meaningful progress, update the persistent plan with completed items, changed files, validation results, and remaining work.
5. Follow existing project conventions and keep changes within scope.
6. If blocked, record the precise blocker, update the plan, and stop dependent work. Ask the user only when a decision, missing information, or permission is actually needed.
7. Once a phase passes its exit criteria, complete the required phase-transition checkpoint, report the transition, and proceed to the next phase automatically. Do not ask for approval after every phase unless the user requested phase-by-phase approval or a material decision is needed.

## Phase 4 — Verify Each Phase
Before closing a phase:
1. Check its acceptance criteria against the actual implementation.
2. Run the narrowest relevant tests, checks, or build commands.
3. Inspect errors and failures; do not report a failing check as passed.
4. Review the relevant diff for unintended changes.
5. Record commands run and their actual outcomes in the plan.

If a test cannot run, record why. Do not silently claim verification. A failed test or unmet acceptance criterion prevents the phase from being marked `Complete`, unless the user explicitly accepts the limitation.

## Phase 5 — Final Verification and Handoff
Only after all required phases are complete:
1. Review the final diff and acceptance criteria.
2. Run appropriate regression tests and checks.
3. Confirm no known requirement was silently dropped.
4. Update the plan with final results and any remaining limitations.
5. Summarize changes, completed phases, tests, failures, and outstanding issues concisely.
6. Do not report overall task status as `Complete` while required checklist items remain unverified or blocked.

## Context and Task Continuity
- The persistent implementation plan is the source of truth for task progress.
- After context compaction, a resumed task, or uncertainty about progress, reread the plan and inspect current files and the relevant Git diff before continuing.
- Report which phase is being resumed before continuing work.
- Never assume previous work survived unchanged; verify repository state.
- Do not repeat completed work unless verification reveals a real problem.
- If scope or requirements change, update the plan before proceeding.
- Prefer concise, actionable progress updates over dumping entire files or logs.
- If the plan conflicts with the current repository state, investigate and reconcile the discrepancy before continuing.

## Safety and Scope
- Do not run destructive database, filesystem, or deployment operations without appropriate verification and authorization.
- Do not silently change requirements, public contracts, security behavior, or data semantics to make tests pass.
- User instructions and the project's explicit requirements take precedence over assumptions in this workflow.
