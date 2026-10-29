# AI Search Readiness (GEO): 45/100

Pages audited: /, /about, /portofolio, /license, /works (raw SSR HTML, is_spa=false). Checked 2026-10-29.

| Dimension | Weight | Score | Notes |
|---|---|---|---|
| Citability | 25% | 40 | Facts exist (named clients, 3 offices, 145 staff) but every passage is 1-2 sentences; no answer blocks, no question headings |
| Structural Readability | 20% | 55 | Clean H1-H3 hierarchy, but all-caps H1s, empty H2/H3 nodes on /works and /portofolio, EN/ID mixed headings |
| Multi-Modal | 15% | 50 | 13 images on / with alt text, workshop photos, org chart, company-profile PDF; no video, no captioned project galleries |
| Authority & Brand | 20% | 20 | Placeholder certificates and template staff (see Critical), no named people, no sameAs, entity split with trissan.co.id |
| Technical Accessibility | 20% | 60 | SSR, search/user bots return 200; sitemap 500, no llms.txt, debug mode on |

**What works**
- Server-rendered HTML; all content (including modals) is in the initial response, so AI fetchers need no JS.
- Search and user-triggered AI bots are allowed (robots `User-agent: *` Allow) and got HTTP 200 in UA tests: OAI-SearchBot, ChatGPT-User, Claude-SearchBot, Claude-User, PerplexityBot, Perplexity-User, Googlebot, bingbot.
- Verifiable client names (Astra Honda Motor Plant 3 Cibitung, Mayora Indah, Indofood CBP Purwakarta, Nihon Plast, Mikuni, Yaskawa) are strong, citable entity co-occurrences.
- Organization + WebSite JSON-LD on every page, BreadcrumbList on inner pages.

## AI crawler access (live Cloudflare managed robots.txt)

| Crawler | Governs | robots.txt | UA test |
|---|---|---|---|
| OAI-SearchBot | ChatGPT Search citations | Allowed (*) | 200 |
| ChatGPT-User | User-triggered ChatGPT fetches | Allowed (*) | 200 |
| Claude-SearchBot | Claude search citations | Allowed (*) | 200 |
| Claude-User | User-triggered Claude fetches | Allowed (*) | 200 |
| PerplexityBot / Perplexity-User | Perplexity index / live fetch | Allowed (*) | 200 / 200 |
| Googlebot | Google Search + AI Overviews/AI Mode | Allowed (*) | 200 |
| bingbot | Bing + Copilot grounding (and ChatGPT via Bing) | Allowed (*) | 200 |
| Applebot | Siri/Spotlight/Safari | Allowed (*) | n/t |
| GPTBot | OpenAI model training only | Disallowed | 403 at edge |
| ClaudeBot | Anthropic model training only | Disallowed | 403 at edge |
| Google-Extended | Gemini/Vertex training + Gemini grounding (not Search/AIO) | Disallowed | n/t |
| Applebot-Extended | Apple Intelligence training only | Disallowed | n/t |
| CCBot, cohere-ai, meta-externalagent, MistralAI-Training, Bytespider, Amazonbot | Training / datasets | Disallowed | n/t |
| GoogleOther, Google-CloudVertexBot | Google R&D crawl; Vertex AI agent crawls | Disallowed | n/t |

Content-Signal: `search=yes, ai-train=no, use=reference`. `ai-input` (RAG/grounding) is not declared; `use=reference` is not a Cloudflare-defined key and will likely be ignored. UA tests used spoofed UAs; verified-bot handling may differ.

## [Critical] /license publishes placeholder certifications as fact

/license states "Data pada halaman ini adalah contoh dan perlu diganti" yet lists ISO 9001 (LRQA), AS9100D/Nadcap, ASME Stamp U, ISO 14001/45001 (SGS) and SMK3 Emas with fake IDs (CERT-ISO-9001-2024, ASME-STAMP-U-4412). Two show expired dates (ISO 45001 10 Feb 2026, SMK3 14 Jan 2026). Product certs (turbine shaft Nadcap, IEC 61439-2 arc test) do not match the business. AI systems extract these as claims about PT. Trissan; if a buyer verifies them, trust collapses, and the page signals fabricated E-E-A-T.

**Fix:** Remove all sample rows today (or noindex /license until real). Publish only certificates the company actually holds (e.g. SIUJK/SBU, K3 Listrik, ISO if held) with real issuer, number, validity and scan.

## [Critical] /portofolio team directory is template filler

"Matriks Direktori 5 Personil Terdaftar" lists unnamed roles with aerospace/subsea specialties (Inconel blisks, subsea valve shafts, AS9100 FAI, KUKA/ABB robotic cells) and IDs like ENG-CNC-001. This conflicts with the real business (conveyors, hydrants, chillers, panels) and with "145 karyawan".

**Fix:** Replace with real named engineers/managers, real certifications (K3, BNSP), years of experience and project links; otherwise remove the section.

## [High] Entity split: trissan.co.id is live with the same content

https://trissan.co.id/ returns 200 (own robots.txt + sitemaps, Nov 2023 project posts) while the new site lives on trissan.mdhost.site and its Organization `url` points to mdhost.site; the email is marketing@trissan.co.id. Engines and LLMs see two sites for one company, with the older domain holding history.

**Fix:** Launch the new site on trissan.co.id (or www) and 301 every legacy URL; set Organization `url`/`@id` to the official domain. If mdhost.site is staging, add noindex until cutover.

## [High] No citable answer passages; projects are one sentence

Service blurbs are 5-10 words; each project is one sentence with no scope, dates, specs or outcome. AI answers to "kontraktor konveyor Bekasi" or "instalasi fire hydrant pabrik" have nothing extractable to quote.

**Fix:** Per service and per top project, add a 130-170 word self-contained block that answers directly in the first 1-2 sentences (what, where, for whom, specs, duration, result). Use question H2s in Indonesian ("Berapa lama pemasangan konveyor?", "Area layanan PT. Trissan di mana?").

## [High] Weak off-site brand footprint

Quick checks: no Wikipedia (id/en) or Wikidata entity; Bing results for "PT Trissan" Bekasi returned no brand pages; Reddit not reachable (blocked); footer Facebook/Instagram links point to generic homepages; no LinkedIn, YouTube or Google Business Profile linked. YouTube and Reddit mentions correlate most with AI citations.

**Fix:** Create/claim LinkedIn company page, Google Business Profile (Bekasi, Malang, Timika), YouTube channel with short project videos (conveyor commissioning, hydrant test); link them and add to `sameAs`. Get listed in Indonesian B2B directories (Indonetwork, Indotrading) and client/vendor pages.

## [Medium] Sitemap 500 + debug mode limit AI discovery

/sitemap.xml returns 500 with the Laravel debug page (see technical.md). Bing (feeds Copilot and ChatGPT Search) relies heavily on sitemaps/IndexNow for a new domain.

**Fix:** Fix sitemap, set APP_DEBUG=false, submit to GSC and Bing Webmaster Tools, enable IndexNow.

## [Medium] Training opt-out is fine, but declare grounding intent

Blocking GPTBot/ClaudeBot/CCBot only affects model training, not ChatGPT/Claude/Perplexity search citations (those bots are allowed). Blocking Google-Extended does remove content from Gemini app/Vertex grounding, though not from AI Overviews.

**Fix:** Keep training blocks if desired. Add `ai-input=yes` to Content-Signal. Consider unblocking Google-Extended if Gemini answers matter; optionally allow GPTBot/ClaudeBot for long-term model awareness of a low-profile brand.

## [Low] Structural noise

All-caps H1s ("DAFTAR PROYEK & HASIL PENGERJAAN (WORKS)"), empty H2/H3 elements, and filter empty-state text ("Tidak ada proyek yang sesuai") are in the extracted text of /works and /portofolio.

**Fix:** Sentence-case headings, remove empty headings, hide empty-state copy with `hidden` until needed.

## [Low] No llms.txt / RSL

/llms.txt, /llms-full.txt, /.well-known/llms.txt and RSL license.xml all 404. Not used by Google; low impact elsewhere.

**Fix:** Optional: add a short /llms.txt (company summary, services, offices, key URLs) after the official domain is settled.

## Platform scores

| Platform | Score | Driver |
|---|---|---|
| Google AI Overviews / AI Mode | 40 | Googlebot allowed, SSR; held back by sitemap 500, duplicate domain, thin passages |
| ChatGPT Search | 35 | OAI-SearchBot allowed; depends on Bing index where brand is barely visible |
| Perplexity | 40 | PerplexityBot allowed; favours specific passages and third-party mentions, both lacking |
| Bing Copilot | 35 | bingbot allowed; no sitemap, no Bing Webmaster/IndexNow, weak entity signals |
| Gemini app | 20 | Google-Extended blocked removes grounding use |

## Top 5 changes (impact / effort)

1. Remove placeholder certificates and template staff; publish real ones: Critical / 2-4 h
2. Consolidate onto trissan.co.id with 301s and updated Organization url: High / 1-2 days
3. Add 130-170 word answer blocks + question H2s per service and top 6 projects: High / 2-3 days
4. Fix sitemap, disable debug, submit to GSC + Bing, enable IndexNow: High / 1-2 h
5. Build entity footprint (GBP x3, LinkedIn, YouTube project clips, real sameAs, B2B directories): High / 1-2 weeks
