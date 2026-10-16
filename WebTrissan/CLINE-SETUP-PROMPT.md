# Cline setup/verification prompt

Paste this prompt into Cline with the desired Laravel project opened at its root.

---

You are configuring this repository for token-efficient, reliable development. Audit before modifying anything.

1. Inspect Git status and existing `AGENTS.md`, `.clinerules/`, `.clineignore`, `.gitignore`, Laravel Boost files, and `graphify-out/`. Preserve user edits. Do not dump secrets, `.env`, full lockfiles, whole logs, or generated graph files.
2. Merge the supplied `AGENTS.md` and `.clinerules/` into existing files only where needed. Do not overwrite existing useful rules. Keep all always-loaded rules short and avoid duplicate instructions.
3. Verify the ignore file format supported by this installed Cline version. `.clineignore` has known compatibility/search-path issues in some Cline versions; treat it as a token-noise filter, never as a security boundary. Do not hide source, migrations, tests, or documentation needed for coding.
4. Verify Laravel Boost is installed (`php artisan list --raw` / `php artisan boost:install --help`) and whether `laravel-boost` is actually connected in Cline's MCP server UI/settings. If not connected, give the exact MCP entry using the real absolute project path. Do not overwrite the user's MCP JSON: back it up, merge only the Boost entry, and validate JSON. If installation/configuration needs UI interaction, state the manual step instead of claiming success.
5. Inspect Cline version, current provider name, and exact model ID for the `smart-router` alias. Do not change the 9Router/LiteLLM config.
6. Investigate context metadata carefully. Because `smart-router` can route to multiple tiers, determine the smallest real max-input/context limit among all routed models. If Cline's supported model metadata override is available, back up and merge (never replace) `~/.cline/data/settings/models.json` or the actual settings path beside Cline's providers config. Use the exact provider key and exact selected model ID. Set `contextWindow` and `maxInputTokens` to a conservative value no larger than the smallest route tier's supported input window; do not assume a 1M window from a model card. If provider/model identification or tier context limits cannot be verified, do not edit the override; report the missing information and the exact command/UI inspection needed.
7. Leave Auto Compact enabled where supported. Check whether this provider actually performs Cline's summarization call or falls back to rule-based truncation. Do not claim Auto Compact is working without evidence in UI/logs. Explain that setting context metadata controls Cline's estimate/trigger, not the backend's actual hard limit.
8. Apply token-saving workflow rules: inspect narrow files/line ranges; use Graphify report/query as an index, then verify source; command output should be filtered/summarized; after an error read the first useful error and change approach; after two failed attempts at one root cause stop blind retries. Use `/smol` to continue the same task with compressed history and `/newtask` at milestones or when switching goals.
9. Verify Markdown/frontmatter/JSON syntax, show a concise `git diff --stat` and `git diff --check`, and report exactly what changed, which checks passed/failed, and remaining manual steps.

Do not implement application features, reformat unrelated files, install new packages, upgrade services, or run a forced Graphify full extraction during setup.
