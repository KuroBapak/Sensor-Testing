# MCP Tool Usage Policy



## Purpose



Use MCP tools only when they provide meaningful value to the current task.



- ****Codebase Memory:**** Understand indexed source-code structure, symbol references, call chains, and code dependencies.

- ****Graphify:**** Understand broader repository relationships, architecture, shared logic, and connections between implementation, PRDs, and documentation.

- ****Laravel Boost:**** Obtain Laravel-specific documentation, framework guidance, schema information, and development diagnostics.



Choose the smallest useful toolset. Do not invoke all MCP servers by default, and do not use graph analysis merely because a task touches multiple files.



For bounded static HTML/CSS/JS edits with known targets, use direct, targeted source inspection unless graph analysis can answer a specific question that affects the implementation.



## 1. Check MCP Availability



At the beginning of a relevant task:



1. Check which MCP tools are currently available.

2. Identify Codebase Memory, Graphify, and Laravel Boost tools from their actual server names, tool names, and descriptions.

3. Do not assume a server is connected just because it exists in the MCP configuration.

4. Use only tools relevant to the current task; checking availability does not mean invoking every server.

5. If a relevant tool is unavailable or fails, use a suitable fallback and record the limitation in the implementation plan for substantial tasks.

6. Never claim to have used a tool unless the call actually succeeded.



Do not repeatedly retry an identical failed tool call without investigating the cause.



## 2. Codebase Memory — Source-Code Understanding



Use Codebase Memory when understanding indexed source code materially helps with:



- Symbol definitions, references, and usage across files.

- Function or method call chains and execution paths.

- Code dependencies and relationships between implementation components.

- Unfamiliar code paths or the potential impact of changing existing logic.

- Finding relevant implementation areas before modifying shared code.



Workflow:



1. Check whether the current project has a usable Codebase Memory index.

2. Query the index with focused questions relevant to the task.

3. Identify relevant symbols, callers, dependencies, and candidate source files.

4. Read and verify important findings against the actual current source code before implementation.

5. Treat missing index data, incomplete results, and inferred relationships as unverified.

6. If the index is missing or materially stale, index or refresh the current project only when needed. Avoid unnecessary full re-indexing.

7. If indexed results conflict with the current source code, prioritize verified source code and investigate the discrepancy.



Do not index the project repeatedly for every coding task. Re-index only when the index is missing, materially stale, incomplete for the requested investigation, or when the user explicitly requests it.



For trivial or isolated changes, skip Codebase Memory if targeted file inspection is sufficient.



## 3. Graphify — Repository Relationships and Architecture



Use Graphify when repository relationships, dependencies, architecture, shared logic, or unfamiliar code paths materially affect the task.



Typical use cases:



- PRD-driven features requiring repository-level exploration.

- Architecture and dependency analysis.

- Relationships across modules, services, models, controllers, routes, and functions.

- Connections between implementation, requirements, documentation, and design decisions.

- Changes with potentially broad architectural impact.



Workflow:



1. Query the existing project graph with focused questions relevant to the current task.

2. Identify relevant components, dependencies, relationships, and candidate source files.

3. Read and verify important findings against the actual source code before implementing changes.

4. Investigate callers and dependencies before modifying shared components when relevant graph information is available.

5. Treat unverified nodes, missing evidence, and inferred relationships as clues rather than established facts.

6. If graph findings conflict with source code, prioritize verified current source code and investigate the discrepancy.



Do not rebuild or re-extract the graph for every coding task. Rebuild it only when it is missing, materially stale, incomplete for the requested task, or when the user explicitly requests it.



Do not use Graphify merely because multiple files need modification. For bounded static HTML/CSS/JS edits with known targets, use direct inspection unless graph analysis can answer a real question.



## 4. Laravel Boost — Laravel and Framework Knowledge



Use relevant Laravel Boost MCP tools when working on:



- Laravel framework or package APIs.

- Eloquent models, queries, relationships, and database schemas.

- Migrations, seeders, and database behavior.

- Routes, controllers, validation, authorization, and middleware.

- Queues, events, configuration, and application behavior.

- Laravel-specific errors, logs, runtime diagnostics, and testing.

- Version-sensitive implementation details.



Workflow:



1. Inspect relevant application and package information through Boost when available.

2. Use Boost documentation search for unfamiliar or version-sensitive APIs instead of guessing from memory.

3. Inspect the actual database schema when a task depends on tables, columns, constraints, or relationships.

4. Use relevant error, log, or runtime diagnostic tools when investigating application failures.

5. Follow applicable Laravel Boost guidelines and skills available in the project.

6. Verify the proposed implementation against the installed framework and package versions.



Prefer relevant Boost tools over manual alternatives when they provide the needed information efficiently.



Do not force Boost calls for unrelated frontend-only work or trivial changes that do not require Laravel knowledge.



Never perform destructive database operations or modify production data without appropriate authorization.



## 5. MCP Selection and Call Budget

Choose the smallest toolset capable of answering the current question reliably. MCP usage is not a target; unnecessary tool calls are a failure of task efficiency.

### Tool Selection

- **Codebase Memory:** Prefer for indexed source-code navigation, symbol references, callers/callees, and targeted call-chain analysis.
- **Graphify:** Prefer for repository architecture, cross-module relationships, and connections between source code, PRDs, and documentation.
- **Laravel Boost:** Prefer for Laravel-specific documentation, framework APIs, schema inspection, runtime diagnostics, and version-sensitive implementation details.
- **Direct source inspection:** Prefer for simple questions, known file paths, and bounded edits that do not require graph analysis.

Do not invoke another MCP merely to demonstrate that it is available or to balance tool usage.

### Default Call Budget

- **Simple, bounded task:** Zero MCP calls by default. Use at most one call only when a specific uncertainty justifies it.
- **Targeted cross-file investigation:** Start with one appropriate repository-analysis MCP. After three calls to that MCP, pause and review the results before continuing.
- **Complex PRD or architecture investigation:** Start with focused queries. After five calls to the initial MCP, summarize findings and reassess before continuing.
- These budgets are checkpoints, not reasons to sacrifice correctness. Additional calls require a concrete unanswered question, a material change in query scope, or a necessary verification step.

### Escalation Between MCPs

1. Start with the single MCP best suited to the primary question.
2. Verify its findings against current source code.
3. If a distinct question remains unresolved, identify which other MCP can answer it.
4. Invoke the second MCP only when it provides information not already obtained.
5. Do not use all three MCPs for the same investigation by default.

When a framework-specific question arises during source investigation, use Laravel Boost if framework documentation, schema details, or runtime evidence are actually needed. Do not invoke Boost merely because the repository uses Laravel.

## 6. PRD-Driven Task Integration



When a task is based on a PRD or specification:



1. Read the PRD and identify its acceptance criteria.

2. Use Codebase Memory to trace relevant symbols, callers, and implementation paths when indexed source analysis is useful.

3. Use Graphify to discover broader architectural relationships and connections between requirements, implementation, and documentation when needed.

4. Use Laravel Boost to confirm Laravel-specific APIs, schema details, or runtime behavior when relevant.

5. Record important findings, affected components, assumptions, and unresolved questions in the persistent implementation plan.

6. Map requirements to implementation phases and define verifiable exit criteria.

7. Keep research focused on decisions that materially affect the implementation.



Do not query all MCP servers simply because a task has a PRD. Choose the tools required by the actual scope and unresolved questions.



Do not dump complete graph results, index results, documentation pages, schemas, or logs into the conversation or implementation plan. Preserve only relevant findings and references.



## 7. Verification and Reliability



Before marking a task phase complete:



1. Verify implementation against its acceptance criteria.

2. Check important Codebase Memory and Graphify findings against the actual current source code.

3. Confirm framework behavior against relevant Boost documentation or project evidence when applicable.

4. Run appropriate tests and inspect the results.

5. Record tool failures, unverified assumptions, skipped checks, and unresolved issues explicitly.



Do not mark a failed or unverified requirement as complete.



Do not claim that indexing, a graph query, documentation lookup, or a diagnostic succeeded unless the corresponding operation actually succeeded.



## 8. Context and Task Continuity



The implementation plan is the persistent record of task progress for substantial implementation tasks.



After context compaction, task resumption, or uncertainty about previous work:



1. Read the implementation plan.

2. Inspect the current source files and relevant Git diff.

3. Recheck important assumptions against current project evidence.

4. Resume from the first incomplete checklist item.

5. Do not repeat completed work unless verification reveals a genuine problem.



Reuse findings already obtained in the current task when they remain valid. Query Codebase Memory, Graphify, or Laravel Boost again only when their information is needed to resolve uncertainty or make the next implementation decision.



Do not introduce a full implementation-plan workflow for trivial questions or genuinely isolated edits when the project workflow rules do not require it.



## 9. Token Efficiency and Loop Prevention

- Prefer focused queries over broad repository-wide searches.
- Reuse successful tool results in the current task.
- Reuse the active Codebase Memory project identifier when it is already known and applicable. Call `list_projects` only when the identifier is unknown, ambiguous, unavailable, or needs revalidation. Do not rediscover the same project for every query within a task.
- Do not repeat an identical search, trace, or snippet retrieval when the existing result already answers the question.
- Call `check_index_coverage` only when index coverage or freshness matters to the task; do not use it automatically for every simple question.
- Use one primary `trace_path` query for the relevant execution path. Run another trace only when the first result identifies a specific unresolved relationship.
- Avoid repeating `search_graph` with equivalent search patterns. Refine a query only when the previous result was insufficient and the refinement addresses a clear gap.
- Retrieve a source snippet again only when the required symbol, source range, or retrieval mode differs materially.
- After three calls to the same MCP during a targeted investigation, stop and reassess before making further calls. For complex PRD or architecture work, perform the five-call checkpoint defined in Section 5.
- If two calls fail for the same underlying reason, stop repeating them, investigate the cause, and choose a justified fallback.
- When index data is incomplete or inconsistent, inspect the actual source directly rather than repeatedly querying the same index.
- After context compaction, recover progress from the implementation plan and current source state instead of restarting completed research.
- Preserve correctness and required verification. Reduce redundant calls, not necessary evidence.

For substantial tasks, record important MCP findings, limitations, and unresolved questions in the implementation plan. Do not create a separate report for trivial tasks unless requested.

## 10. Reporting MCP Usage



For substantial tasks, briefly record in the implementation plan:



- Which relevant MCP tools were successfully used.

- The important findings that influenced the implementation.

- Any unavailable tools or failed calls.

- Any fallback method used and why.

- Any important verification that remains incomplete.



Do not produce a separate MCP report for trivial tasks unless the user asks for one.



The final response should distinguish verified findings from assumptions and disclose important checks that could not be completed.
