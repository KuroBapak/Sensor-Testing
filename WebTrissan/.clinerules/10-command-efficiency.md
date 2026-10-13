# Command and context efficiency

- Prefer one targeted command over broad directory dumps. Check `command -v rg` first; if `rg` is unavailable, use `grep -RInE`, `find`, `sed -n`, and `awk`. Do not install a tool just to search.
- Before using flags, check the installed command's `--help` or project scripts. Prefer structured/narrow output (`--path`, `--filter`, `--format`, JSON plus `jq`) when supported.
- For source inspection, search identifiers first, then read only relevant line ranges. Never `cat` large files or print whole logs/lockfiles/`graphify-out/graph.json`/`graph.html`.
- For noisy commands, redirect output to a temporary log, preserve the real exit code, and print only matching errors/warnings plus a short tail. Do not hide failures behind a successful `tail`/`grep`; use `set -o pipefail` for pipelines.
- Run the narrowest relevant test first. Read the first failure before retrying; make the next attempt materially different. Maximum two attempts for one root cause, then stop blind retries and explain the blocker.
- Avoid long chains of mutating commands. Group read-only diagnostics when useful; execute edits/migrations/deletes separately and verify each.
- Do not reread unchanged files or repeat completed investigations after compaction. Keep a short handoff: goal, facts verified, files changed, test results, next step.
- Use Cline `/smol` or `/compact` to condense a still-active task; use `/newtask` when the goal changes or a milestone is complete and a clean context is better. Do not wait until the context bar is almost full if file reads/tool output are growing rapidly.
- Keep rules short. Do not paste this file or the entire repository into every prompt; Cline loads rules automatically.
