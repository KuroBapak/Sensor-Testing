# Token-efficient Cline + Laravel + Graphify pack

This pack contains real starter rule contents, not an empty directory scaffold. Review and merge into an existing repository rather than blindly overwriting local instructions.

## Files

- `AGENTS.md`: short cross-agent workflow agreement.
- `.clinerules/10-command-efficiency.md`: output filtering, retries, and context handling.
- `.clinerules/20-laravel.md`: conditional Laravel rules.
- `.clinerules/30-frontend.md`: conditional frontend rules.
- `.clineignore`: reduces generated/dependency/secret-file noise for Cline features that honor it.
- `CLINE-SETUP-PROMPT.md`: prompt to audit/merge rules, verify Boost MCP, and inspect context metadata safely.

## Important caveats

1. `.clineignore` is not a security boundary. Cline has reported versions where `search_codebase` can return ignored content. Never place secrets in prompts or rely on ignore rules to protect `.env` or credentials.
2. Keep `graphify-out/GRAPH_REPORT.md` accessible, but avoid loading `graph.json`, `graph.html`, and `.graphify_analysis.json` wholesale. These are large generated artifacts.
3. Cline rules consume context tokens. The always-loaded rules here are deliberately concise; Laravel and frontend rules are conditional.
4. `smart-router` may route to different model tiers. Cline's compaction metadata should reflect a conservative input limit across all targets. Do not blindly advertise the largest context window. Verify the exact Cline provider ID/model ID and every tier's supported window before editing `models.json`.
5. Cline's auto-compaction behavior is provider/model/version dependent. Keep it enabled where supported, then verify that an actual summary/compaction call occurs. If not supported, manually use `/smol` or `/compact` before the context bar is high, and `/newtask` when changing goals or finishing a milestone.
6. Do not reduce context window blindly to save tokens. Too-aggressive compaction can lose useful context and cause expensive file rereads. Balance a conservative context cap with small tasks, concise tool output, and a clean handoff.

## Suggested context workflow

- Small isolated bug/edit: keep one focused task and inspect only relevant files.
- Multi-file feature: Plan Mode first, then Act Mode; maintain a brief list of decisions, changed files, tests and blockers.
- Same task and growing context: use `/smol` (or `/compact`) before approaching the model's limit.
- Milestone complete or new unrelated goal: use `/newtask` with a distilled handoff.
- After a command fails: read the first relevant error, do not resend the whole log; never repeat the exact failed command blindly. After two materially different failed attempts at the same root cause, stop and report the blocker.

## MCP configuration

Laravel documents the Boost server as `php` with args `['/absolute/path/to/project/artisan', 'boost:mcp']`. Add it through Cline's MCP Servers UI or merge it into the existing `cline_mcp_settings.json` under `mcpServers`; preserve all other entries. Verify `php artisan boost:mcp` actually exists before configuring it.
