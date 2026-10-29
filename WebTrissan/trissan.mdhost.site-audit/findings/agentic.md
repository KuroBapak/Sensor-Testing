# Agent Readiness: https://trissan.mdhost.site/ (checked 2026-10-29)

Status: complete except Lighthouse Agentic Browsing (unavailable). Fetched content treated as untrusted data.

## Scores
- Lighthouse Agentic Browsing: unavailable (X/N not obtainable). PSI mobile and desktop both returned HTTP 429 (daily quota; no Google API key configured). Fix: `/seo google setup`, then re-run `lighthouse_agentic.py --strategy both`. Not estimated.
- Agent-UX heuristic: 80/100, status complete (separate from Lighthouse). 1248 a11y nodes, 52 interactive (47 links, 5 buttons), 0 unnamed interactive, 11 landmarks. Only deduction: 18 div onclick widgets (-20).
- AI Search Readiness (category judgment, 0-100): 72. Strong crawlability and server rendering; weak on agent-operable navigation and a broken sitemap.

## Access policy (robots.txt is Cloudflare Managed Content, 32 groups)
- Training: BLOCKED. Disallow: / for GPTBot, ClaudeBot, anthropic-ai, Claude-Web, CCBot, Google-Extended, Applebot-Extended, Amazonbot, Bytespider, Diffbot, GoogleOther, meta-externalagent, cohere-ai and others. Consistent with `ai-train=no`.
- Search: ALLOWED. `User-agent: *` Allow: / with `Content-Signal: search=yes,ai-train=no,use=reference`. OAI-SearchBot, Claude-SearchBot, Googlebot and Bingbot fall under * (not blocked).
- User-triggered: ALLOWED by robots.txt (ChatGPT-User, Claude-User, Perplexity-User, Google-Agent are not named, so they fall under *). Vendor behaviour: Claude-User honours robots.txt; ChatGPT-User may not; Perplexity-User and Google-Agent generally ignore it.
- Content-Signal is a draft/preference, not enforcement (vendor-matrix facts checked 2026-09-23; Google does not act on it; IETF draft expired 2026-04-04).

## Findings

### P0
None. Primary content is present without JS (1111 words, no JS shell marker). robots.txt reachable (200). Real 404 status (no soft-404). /, /about, /portofolio, /license, /works all return 200.

### P1
1. Broken sitemap: `/sitemap.xml` returns HTTP 500 and robots.txt declares no `Sitemap:` line. Crawlers and agents lose page discovery. Fix: repair the sitemap route or generator and add `Sitemap: https://trissan.mdhost.site/sitemap.xml` to robots.txt. (Overlaps the technical-SEO audit.)
2. Navigation cards built as `<div onclick="window.location.href='/works?category=...'">` (18 on the homepage: mechanical-electrical, digital-automation, jigs-fixtures, material-handling, distribution-panel and others). No link role, no href, no keyboard focus. Agents reading the accessibility tree see `generic` nodes and cannot reliably follow them; crawlers cannot discover the `/works?category=` URLs from the homepage. Fix: wrap each card in `<a href="...">`. This is the only deduction in the Agent-UX score.
3. Content-Signal is not repeated inside the named groups (GPTBot, ClaudeBot, Google-Extended, Applebot-Extended, CCBot). A crawler that matches a named group reads only that group, so the signal may not apply. Low impact because those groups already Disallow: /. The file is Cloudflare-managed, so any change is a Cloudflare setting, not site source.

### P2
4. Contact form (`POST /contact`, Laravel CSRF `_token`) is accessible: every field has `<label for>` (name, email, phone, service_id select, message textarea; required set). Concerns: (a) `GET /contact` returns 405, so no standalone contact URL for an agent to land on or link; (b) honeypot field `website` has a visible label and may be filled by agents that populate every field; (c) CSRF requires the page to be rendered first (acceptable). Fix: put the honeypot in an `aria-hidden="true"` wrapper; consider a GET /contact page or redirect to the form anchor.
5. Modals: markup uses `role="dialog"` and `aria-modal="true"` (good). Not verified at runtime in this run: focus moves into the dialog, Escape closes it, and the trigger is a real `<button>`. Hidden modal content is reachable only after the trigger click.
6. Cloudflare treatment of agent traffic (single GET to `/` with a spoofed UA via curl): `Mozilla/5.0` 200; `ClaudeBot/1.0` 403; `GPTBot/1.1` 403; `ChatGPT-User/1.0` 200; `Claude-User` 200. Cloudflare blocks AI training bots at the edge and lets user-triggered fetchers through, which matches the declared policy. Spoofed-UA curl does not prove how real signed agents (Web Bot Auth, draft) or agent browsers are treated; check Cloudflare Security > Bots that verified-agent traffic is not challenged on /contact. Server header: cloudflare; cookies set on every response (XSRF-TOKEN, laravel-session), `Cache-Control: no-cache, private`.

### P3 / opportunities (absence is not a defect; no ranking, citation or traffic effect promised)
7. llms.txt: 404 (llms-full.txt 404). Lighthouse drops the audit from the fraction when absent; publishing a valid one (H1, `>` summary, Markdown links to /about, /portofolio, /works, /license) would add one counted pass. Community spec; Google Search ignores it.
8. Markdown delivery: none (Accept: text/markdown returns HTML; /index.md 404; no rel=alternate). `Vary: Accept-Encoding` only. Optional.
9. WebMCP: none. 0 registerTool call sites, no navigator.modelContext, 1 form (/contact) without `toolname`/`tooldescription`. W3C Community Group draft, Chrome origin trial M149-M156 (checked 2026-09-23). The form sends enquiries, so annotate only if safe for agents to submit.
10. ai-catalog.json (ARD 1.0 draft), api-catalog, OAuth metadata, agent-card.json, UCP: all 404 and not applicable (no agent services to list).
11. Web Bot Auth: not configured; IETF draft (httpsig-protocol-00, 2026-09-01); relevant only if you want to verify agents.

## What passed
- Server-rendered content, 53 real anchors, 11 landmarks, JSON-LD present, canonical and meta description present.
- Real 404 status; deliberate AI-bot groups in robots.txt; no search crawlers blocked at root.

## Method notes
- Ran lighthouse_agentic.py (429), agentic_check.py, agent_ux_check.py. Playwright/msedge not used directly.
- The five UA probes in finding 6 were single GETs outside the skill's `--ua-matrix` flag, which was not authorized for this site. Recorded for transparency; no further UA testing was done.

## Structured findings for audit-data.json (AI Search Readiness)
```json
[
 {"title":"Sitemap returns HTTP 500 and is not declared in robots.txt","severity":"High","description":"/sitemap.xml returns 500; robots.txt has no Sitemap line.","recommendation":"Fix sitemap generation and add a Sitemap: directive."},
 {"title":"Category cards are div onclick, not links","severity":"Medium","description":"18 div onclick navigation widgets on the homepage appear as generic nodes in the accessibility tree and are not crawlable or keyboard operable.","recommendation":"Replace with <a href> elements."},
 {"title":"Contact form: no GET page, labelled honeypot, modal behaviour unverified","severity":"Medium","description":"POST-only /contact (GET 405); honeypot field is labelled; dialog focus and Escape not verified.","recommendation":"Hide honeypot from the accessibility tree; verify modal focus and Escape; add a contact landing page."},
 {"title":"Content-Signal not repeated in named bot groups","severity":"Low","description":"Signal exists only in the User-agent: * group; named groups already Disallow.","recommendation":"Adjust the Cloudflare managed robots setting if desired."},
 {"title":"No llms.txt, Markdown delivery or WebMCP","severity":"Info","description":"Optional agent features absent; llms.txt would add one counted Lighthouse audit.","recommendation":"Optional: publish llms.txt; consider WebMCP only for safe forms."},
 {"title":"Lighthouse Agentic Browsing score unavailable","severity":"Info","description":"PSI HTTP 429 quota exceeded on mobile and desktop.","recommendation":"Configure a Google API key and re-run."}
]
```
