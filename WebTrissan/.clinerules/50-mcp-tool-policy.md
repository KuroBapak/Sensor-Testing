# MCP Tool Usage Policy

## Purpose

Use Graphify when repository relationships, dependencies, architecture,
shared logic, or unfamiliar code paths materially affect the task.

Do not use Graphify merely because a task touches multiple files.
For bounded static HTML/CSS/JS edits with known targets, use direct,
targeted source inspection unless graph analysis can answer a real question.

## 1. Check MCP Availability

At the beginning of a relevant task:

1. Check which MCP tools are currently available.
2. Identify Graphify and Laravel Boost tools from their actual server names, tool names, and descriptions.
3. Do not assume a server is connected just because it exists in the MCP configuration.
4. If a relevant tool is unavailable or fails, use a suitable fallback and record the limitation in the implementation plan.
5. Never claim to have used a tool unless the call actually succeeded.

Do not repeatedly retry an identical failed tool call without investigating the cause.

## 2. Graphify — Repository Understanding

Use Graphify when working on:

- PRD-driven features.
- Multi-file changes.
- Architecture or dependency analysis.
- Shared services, models, controllers, routes, or functions.
- Unfamiliar code paths or changes with potentially broad impact.

Workflow:

1. Query the existing project graph with focused questions relevant to the current task.
2. Identify relevant components, dependencies, relationships, and candidate source files.
3. Read and verify important findings against the actual source code before implementing changes.
4. Investigate callers and dependencies before modifying shared components when relevant graph information is available.
5. Treat unverified nodes, missing evidence, and inferred relationships as clues rather than established facts.
6. If graph findings conflict with source code, prioritize verified current source code and investigate the discrepancy.

Do not rebuild or re-extract the graph for every coding task. Rebuild it only when it is missing, materially stale, incomplete for the requested task, or when the user explicitly requests it.

For trivial, isolated changes, skip Graphify when using it would add no meaningful value.

## 3. Laravel Boost — Laravel and Framework Knowledge

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

## 4. PRD-Driven Task Integration

When a task is based on a PRD or specification:

1. Read the PRD and identify its acceptance criteria.
2. Use Graphify to discover relevant implementation areas and dependencies when the task warrants repository analysis.
3. Use Laravel Boost to confirm Laravel-specific APIs, schema details, or runtime behavior when relevant.
4. Record important findings, affected components, assumptions, and unresolved questions in the persistent implementation plan.
5. Map requirements to implementation phases and define verifiable exit criteria.
6. Keep research focused on decisions that materially affect the implementation.

Do not dump complete graph results, documentation pages, schemas, or logs into the conversation or implementation plan. Preserve only relevant findings and references.

## 5. Verification and Reliability

Before marking a task phase complete:

1. Verify implementation against its acceptance criteria.
2. Check important Graphify findings against the actual source code.
3. Confirm framework behavior against relevant Boost documentation or project evidence when applicable.
4. Run appropriate tests and inspect the results.
5. Record tool failures, unverified assumptions, skipped checks, and unresolved issues explicitly.

Do not mark a failed or unverified requirement as complete.

## 6. Context and Task Continuity

The implementation plan is the persistent record of task progress.

After context compaction, task resumption, or uncertainty about previous work:

1. Read the implementation plan.
2. Inspect the current source files and relevant Git diff.
3. Recheck important assumptions against current project evidence.
4. Resume from the first incomplete checklist item.
5. Do not repeat completed work unless verification reveals a genuine problem.

Use Graphify and Laravel Boost again only when their information is needed to resolve uncertainty or make the next implementation decision. Do not repeat successful research unnecessarily.

## 7. Token Efficiency

- Prefer specific Graphify queries over broad repository-wide analysis.
- Prefer relevant documentation excerpts over entire documentation pages.
- Request only the schema, logs, or source information needed for the current decision.
- Avoid repeated calls that return the same information.
- Avoid reading large files or directories when targeted searches are sufficient.
- Never trade away correctness or required verification merely to reduce token usage.

## 8. Reporting MCP Usage

For substantial tasks, briefly record in the implementation plan:

- Which relevant MCP tools were successfully used.
- The important findings that influenced the implementation.
- Any unavailable tools or failed calls.
- Any fallback method used and why.

Do not produce a separate MCP report for trivial tasks unless the user asks for one.

The final response should distinguish verified findings from assumptions and disclose important checks that could not be completed.