# Backlink Profile - trissan.mdhost.site

Date: 2026-10-29 | Tier 0 (Common Crawl + verification crawler only; no Moz/Bing/DataForSEO/Keywords Everywhere keys)

## Score
- Weighted Backlink Health Score: **INSUFFICIENT DATA** (0 of 7 scoring factors have a data source; Tier 0 needs at least 4). No numeric health score is produced on purpose.
- Backlink readiness (analyst judgment, NOT the weighted score, low confidence): **12/100**. Basis: no observable inbound links, no external citations, shared-host subdomain, brand-new site.

## Data collected
| Source | Result | Confidence |
|---|---|---|
| Common Crawl web graph, release cc-main-2026-jan-feb-mar, host `trissan.mdhost.site` | Not in crawl, not in rankings. PageRank and harmonic centrality: null | 0.50 (domain-level; quarterly graph) |
| Common Crawl, parent `mdhost.site` | Not in crawl, not in rankings | 0.50 |
| Moz (DA/PA/spam, referring domains, anchors) | Not available, no API key | n/a |
| Bing Webmaster inbound links | Not available, no API key and property not verified | n/a |
| Own-site HTML (render.json, parsed) | Homepage links only to its own pages, Google Fonts, and one maps.google.com address link. No social profile links, no `sameAs` found in rendered HTML | Parsed 0.95 |
| Verification crawler | Not run: no known backlink list was supplied | n/a |

Interpretation: "not in Common Crawl" means the host is absent from a Jan-Mar 2026 snapshot. That is consistent with a new or very low-authority site, but it does NOT prove zero backlinks. Links created after the snapshot, or on pages Common Crawl skipped, would not appear. Referring domains: **unknown** (Tier 0 cannot enumerate them). Anchor text, toxicity, link velocity, follow ratio and geo distribution are all unscored (no data).

## Factors scored vs skipped
Scored: none. Skipped: referring domain count, domain quality distribution, anchor naturalness, toxic ratio, velocity, follow/nofollow, geographic relevance.

## Findings

### 1. [High] No measurable backlink profile (Common Crawl: absent)
Neither the host nor `mdhost.site` has any graph presence. For a B2B contractor this means no visible third-party endorsement signal at present. Treat the baseline as approximately zero until Google Search Console (Links report) or Bing Webmaster data shows otherwise.

### 2. [High] Shared-hosting subdomain limits link equity and trust
Observed: the site lives at `trissan.mdhost.site`, a subdomain of a hosting provider's domain. Impact below is general SEO reasoning, not measured here:
- Authority accrues to the registrable domain `mdhost.site`, which PT. Trissan does not control. That domain is also absent from Common Crawl, so there is no inherited authority to borrow.
- Every link, citation and directory listing built toward this URL must be redirected or rebuilt if the company later moves to its own domain. Redirects carry most but not all value, and migration risk grows as links accumulate.
- Many directories, associations and vendor-registration portals expect a company domain and corporate email. A hosting subdomain reads as a trial or hobby site and may be rejected or discounted.
- Brand signals (branded search, email on own domain, knowledge panel eligibility) are weaker on a subdomain.
- Not verified: whether `mdhost.site` is on the Public Suffix List. If it is, the subdomain is treated as its own site; if not, it may share reputation with other tenants, including spammy ones. Worth checking.

Recommendation: register a company-owned domain (a .co.id requires Indonesian company documents; .com is the alternative), 301 the subdomain to it, and only then invest heavily in link building. If the move is planned within months, do citation work using the final domain only.

### 3. [Medium] No social or entity profile links from the site
No `sameAs` or social links were found on the pages crawled. Linking the company LinkedIn page, Google Business Profile and any Facebook/Instagram pages (and getting reciprocal links back) establishes entity ties that make early link building easier to verify. See the schema and local findings for related items.

### 4. [Medium] Technical blockers reduce the value of any earned links
Other findings in this audit (sitemap.xml returning 500 with a debug page, robots.txt without a Sitemap line, APP_DEBUG exposure) undermine crawling of earned links. Fix before outreach. `/seo technical <url>` covers crawlability; `/seo content <url>` covers E-E-A-T depth.

### 5. [Low] Toxic-link risk: unknown, presumed low
No spam data available. A new site with no links has little toxic exposure. Avoid bulk directories and paid link packages. Re-check with Moz Spam Score or GSC once links exist.

## Link-building priorities (B2B industrial contractor, Indonesia)
Linkable assets already on the site: /works and /portofolio (project case studies such as chiller duct bypass, fire hydrant system, conveyor systems, distribution panels) and /license (credentials).

1. **Client and partner case-study links [High value, hardest]**: ask completed-project clients, principal contractors and the equipment brands Trissan installs for a testimonial, logo or project mention linking to the matching /works entry. Offer a ready-written project summary with photos.
2. **Indonesian industry associations [High value]**: check membership eligibility and member-directory listings, for example KADIN Indonesia and regional chapters (Bekasi, Malang, Timika/Papua), GAPENSI (construction), electrical contractor/installer associations such as AKLI, automation/instrumentation bodies, and tenant directories of Bekasi industrial estates. These names and eligibility rules come from general knowledge and were not verified; confirm each before applying.
3. **Supplier and vendor directories [Medium]**: moderated Indonesian B2B and industry directories (for example Indonetwork, Kompass, Yellow Pages Indonesia) and vendor-registration portals of large manufacturers in the Bekasi corridor. Keep NAP identical to the Google Business Profile for the Bekasi office. Prefer relevant, moderated directories over bulk submissions.
4. **Brand partner pages [Medium]**: authorized-installer or reseller pages from brands Trissan works with.
5. **Local and regional citations [Medium]**: Google Business Profile for Bekasi, Malang and Timika offices (entity value more than link equity), regional chamber or government business listings.
6. **Technical content and trade media [Medium-Low]**: articles on fire hydrant system design, conveyor maintenance and automation retrofits for Indonesian engineering and industry publications, plus LinkedIn posts as supporting entity signals.

Sequencing: (a) move to an own domain, (b) fix sitemap, robots and debug issues, (c) verify GSC and Bing Webmaster (free; gives real link data), (d) pursue items 1-3 first.

## Next data steps
- Verify the property (or the new domain) in Google Search Console and Bing Webmaster Tools and export the Links report.
- Add a free Moz API key (2,500 rows/month) to reach Tier 1 (confidence 0.85), then re-run `/seo backlinks`.
- Re-check Common Crawl after the next quarterly release.
- Run `verify_backlinks.py` once placed links exist.

## Severity summary
| Severity | Item |
|---|---|
| High | No measurable backlink profile |
| High | Shared-hosting subdomain limits equity and trust; migration risk |
| Medium | No social/entity profile links |
| Medium | Technical blockers reduce link value |
| Low | Toxic risk unknown (presumed low) |
