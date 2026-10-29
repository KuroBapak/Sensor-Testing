# Search Experience (SXO) — SXO Gap Score 42/100

Separate from the SEO Health Score. Target: https://trissan.mdhost.site/ (rendered with Playwright, 916 words, H1 "We Provide For User experience").
Category pages (`/works?category=material-handling` etc.) canonicalise to `/works` and share its title, so **the homepage is the only indexable page for all 12 services**.

**What works**
- Named industrial clients with project photos: PT Astra Honda Motor Plant 3 Cibitung, PT Mayora Indah Tbk, PT Indofood CBP Purwakarta, PT Nihon Plast
- Contact paths are in place: 2 `tel:` links, `mailto:`, POST form, contact modal, embedded Google Map, full Bekasi address
- Title and meta description use Indonesian service terms (kelistrikan, mekanikal, otomasi, material handling)

## SERP evidence (5 query clusters, about 45 results reviewed)

| Query | What ranks | Dominant type (taxonomy) | Consensus |
|---|---|---|---|
| kontraktor mekanikal elektrikal Bekasi | JobStreet / loker.id job listings (10/10) | none (job-seeker intent) | ambiguous |
| kontraktor MEP industri Bekasi Cikarang | "Jasa MEP Bekasi" (KGT), metalis-mep.com, manatek.co.id, buanaproject "Jasa MEP Bekasi 2026", kontraktorhub | Service Page (city + service) | 7/9, ~78% |
| jasa (fabrikasi/pembuatan) konveyor (Bekasi/Cikarang) | mitrautamateknik.com has 6 "[service] [city]" URLs, gedeprimapresisi "Fabrikasi di Cikarang", plus FB page | Service Page (city + service) | 8/9, ~89% (unlocalised query: academic PDFs) |
| instalasi fire hydrant pabrik / kontraktor fire hydrant Bekasi | Journals, ITS thesis, Bina Marga SNI doc; Bekasi variant: spam pages and Indotrading | Blog/informational (unlocalised); no clear vendor (localised) | ~80% informational |
| jasa pembuatan panel PLC HMI Bekasi/Cikarang | Job listings, theses, one distributor page | none; weak competition | gap |
| PT Trissan Bekasi (brand) | EMIS profile only (founded 2005, lists **trissan.co.id**); target site absent | n/a | n/a |

Signals seen: buyers say **"MEP"** and **"Jasa [service] [city]"**, and the city set is Bekasi + Cikarang + Karawang + industrial estates (Jababeka, MM2100, EJIP). Competitor pages are around 900-1,000 words **per service** and include conveyor types, a price table (Rp 8.5-18 jt), a 5-step order process, testimonials, a 4-question FAQ and a WhatsApp CTA. Fire-hydrant SERPs reward SNI 03-1745 / NFPA 14/20 compliance content.

## [Critical] Page-type mismatch: one multi-service homepage vs a SERP of single-service city pages

The SERP for every commercial query is made up of Service Pages built as "Jasa [layanan] [kota]". Trissan has a Landing/hub homepage with 12 one-line service cards (for example "Konveyor dan sistem logistik internal pabrik."). The cards link to filtered `/works` URLs that canonicalise away. As a result the site has no URL Google can match to "jasa pembuatan konveyor Bekasi", "panel PLC HMI Bekasi" or "instalasi fire hydrant pabrik". Severity by query: conveyor **CRITICAL**, panel PLC/HMI **CRITICAL** (low competition, so easy to win), fire hydrant **CRITICAL** (not even one of the 12 services, only 2 project mentions), kontraktor MEP Bekasi **HIGH** (homepage could rank but never says "kontraktor", "MEP", "Cikarang" or "Karawang": 0 occurrences each).
**Fix:** Create indexable `/layanan/{slug}` Service Pages, starting with conveyor/material handling, panel PLC/HMI, fire hydrant, and ME maintenance. Each page needs a self-canonical, the H1 "Jasa [Layanan] di Bekasi & Cikarang", about 800+ words, process, types/specs, related projects from /works, FAQ, and a CTA. Run `/seo page` per new page.

## [High] H1 and hero do not answer "who, what, where"

The H1 "We Provide For User experience" is English, generic, and grammatically broken. Within 10 seconds a plant buyer cannot tell that this is a Bekasi ME/automation contractor.
**Fix:** Use the H1 "Kontraktor Mekanikal, Elektrikal & Otomasi Industri di Bekasi". Add a subline: "Konveyor, panel PLC/HMI, fire hydrant & perawatan mesin untuk pabrik di Bekasi, Cikarang, Karawang". Put two CTAs above the fold: "Minta Penawaran" and "Chat WhatsApp Engineer".

## [High] Trust layer undermined by placeholder certifications

`/license` (linked from main nav) shows "Data pada halaman ini adalah contoh". It lists sample ISO 9001, **AS9100D aerospace** and ASME cards, each marked "(data contoh) / Ganti melalui CMS". Footer social links go to bare facebook.com / instagram.com. For procurement, fake-looking certificates rule a vendor out.
**Fix:** Replace the content with real documents (NIB, SBU/LPJK, ISO if held, K3/SMK3, Ahli K3 Listrik), or hide the page until they exist. Fix the social URLs. Run `/seo content` for E-E-A-T.

## [High] No WhatsApp or low-friction quote path

Every competitor in the SERP leads with WhatsApp. Trissan has 0 `wa.me` links, and no "survei lokasi", response time, or company-profile download.
**Fix:** Add a sticky WhatsApp button with a prefilled message for each service ("Halo Trissan, saya butuh penawaran [layanan] untuk pabrik di ..."). Add "Unduh Company Profile (PDF)" and "Jadwalkan Survei Lokasi".

## [Medium] Missing decision content buyers compare on

The page has 0 mentions of harga/estimasi, garansi, SNI/NFPA, PLC brands (Siemens/Omron/Mitsubishi), years in business (EMIS says 2005), or FAQ. Projects have no dates, scope, capacity, or duration.
**Fix:** On each service page add a "Faktor Biaya" section (no fixed prices needed), list the brands and standards handled, give project facts (client, lokasi, kapasitas, durasi, tahun), and add a 4-6 question FAQ.

## [Medium] Schema does not describe services or branches

The page has only Organization + WebSite markup, with the address as one string, no LocalBusiness, no Service, and no Malang/Timika locations.
**Fix:** Add `LocalBusiness` (or `GeneralContractor`) with structured PostalAddress and geo, `department` entries for the branches, and `Service` + `areaServed` on each new service page. Use `/seo schema` to generate it. Use `/seo local` for the GBP.

## [Low] Freshness signals absent

There are no project dates or "updated" stamps. htmldate guessed 2026-01-01.
**Fix:** Show the project year on each /works card, and add dateModified to the service pages.

## User stories (from SERP signals)

1. **Awareness, Plant Engineering Manager.** As a plant engineering manager in Cikarang, I want to find a vendor who designs and fabricates a custom conveyor because our line is expanding, but I'm blocked by **an information gap**: Trissan has one sentence on conveyors and no types or specs. *(Source: mitrautamateknik "Jenis Conveyor" + "Proses Pemesanan" sections; 6 "conveyor [city]" URLs ranking)*
2. **Consideration, Procurement Officer.** As a procurement officer, I want to check vendor legality and certification before an RFQ because audit requires it, but I'm blocked by **a trust gap**: the certificates are visibly placeholders. *(Source: SERP summaries telling buyers to "minta referensi proyek, sertifikasi, penawaran terperinci"; KGT "250+ proyek, 15+ tahun" claims)*
3. **Consideration, HSE/Facility Manager.** As an HSE manager, I want a hydrant installer who builds to SNI 03-1745 / NFPA 14 because a fire inspection is due, but I'm blocked by **technical confusion**: Trissan does not mention standards. *(Source: hydrant SERP is 80% journals and Bina Marga SNI documents)*
4. **Decision, Maintenance Supervisor.** As a maintenance supervisor with a conveyor down, I want to reach a technician today, but I'm blocked by **time pressure**: there is no WhatsApp and no stated response time. *(Source: competitor WA CTAs; "biaya maintenance fire hydrant system cikarang" query pattern)*
5. **Decision, Plant Engineering Manager (automation).** As an automation lead, I want a panel PLC/HMI builder near Bekasi, but I'm blocked by **an information gap**: the SERP has only job ads and theses, and Trissan offers one line. *(Source: panel PLC SERP with 0 vendor pages, a winnable gap)*

## Persona scores (homepage)

| Persona | Relevance | Clarity | Trust | Action | Total | Rating |
|---|---|---|---|---|---|---|
| HSE / Facility Manager (fire hydrant) | 7 | 8 | 8 | 12 | 35 | Critical Mismatch |
| Procurement Officer | 10 | 10 | 6 | 12 | 38 | Critical Mismatch |
| Maintenance Supervisor (urgent) | 12 | 12 | 13 | 11 | 48 | Needs Work |
| Plant Engineering Manager (conveyor/PLC) | 14 | 13 | 14 | 14 | 55 | Needs Work |

Weighted average (commercial personas weighted higher): **about 44/100**.
- **HSE (35):** Fire hydrant is not one of the 12 services and has no standards content. Fix: add the page `/layanan/instalasi-fire-hydrant` with an "Standar SNI/NFPA yang Kami Ikuti" section, the Mayora project facts, a pump/pillar/GWT scope list, and the CTA "Minta Survei Hydrant".
- **Procurement (38):** Placeholder certificates, no NIB/SBU, no company-profile PDF. Fix: add a "Legalitas & Sertifikasi" strip on the homepage (real docs only), the CTA "Unduh Company Profile", and "Berdiri sejak 2005".
- **Maintenance (48):** No WhatsApp, SLA, or emergency path. Fix: add a sticky WA button and the line "Respons tim teknisi < 24 jam area Bekasi-Cikarang-Karawang".
- **Engineering (55):** Proof is strong (AHM, Indofood) but there are no specs. Fix: give each service page a specs table (kapasitas, material, brand PLC) and link related projects.
- **Systemic issues:** Relevance and Clarity are lowest across all personas because no service-level URL exists and the H1 says nothing. Trust is hurt site-wide by the /license placeholders.

## SXO Gap Score breakdown

| Dimension | Score | Evidence |
|---|---|---|
| Page Type | 5/15 | Hub homepage vs single-service city pages in SERP; category URLs canonicalised away |
| Content Depth | 5/15 | 916 words for 12 services (about 1 line each) vs ~900-1,000 words per service at competitors |
| UX Signals | 9/15 | Tel, mail, form, modal and map present; broken English H1; no WhatsApp; no FAQ |
| Schema | 5/15 | Organization + WebSite only; no LocalBusiness/Service/branches |
| Media | 8/15 | 13 images, real project cards; hero images look like stock ("Robot industri..."); no video or diagrams |
| Authority | 6/15 | Strong client names, offset by placeholder certificates, dead social links, no years or SBU |
| Freshness | 4/10 | No project dates; no visible update stamp |
| **Total** | **42/100** | |

## Limitations

- WebSearch is not a live Google SERP. It gave no PAA, ads, local pack, AI Overview, or positions, so page-type consensus comes from result URLs and titles. Validate with real Google (id-ID, Bekasi location) or the DataForSEO extension.
- No search volumes, so persona weights are estimates. No GSC/GA4 data, so real queries, CTR, and on-site behaviour are not assessed.
- The site runs on a staging-like domain (mdhost.site), while EMIS lists trissan.co.id. Check which domain is meant to rank.
- Only 2 competitor pages were fetched in depth. The /works detail and /about pages were not persona-scored.

Generate a PDF report? Use `/seo google report`.
