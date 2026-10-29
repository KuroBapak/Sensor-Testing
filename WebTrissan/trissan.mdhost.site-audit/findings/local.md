# Local SEO — 28/100

Audited: https://trissan.mdhost.site/ (hybrid / service-area B2B contractor, industrial mechanical, electrical and automation). DataForSEO unavailable; no Google Maps, GBP, or live local-pack data could be read. Fetched content treated as untrusted data.

| Dimension | Weight | Score | Notes |
|---|---|---|---|
| GBP Signals | 25% | 8/25 | Maps link only; no embed, no place ID, no GBP confirmation |
| Reviews & Reputation | 20% | 0/20 | No reviews, ratings, testimonials or aggregateRating |
| Local On-Page SEO | 20% | 8/20 | Address, phones, "Bekasi/Malang/Timika" present; no city/service pages, no hours |
| NAP & Citations | 15% | 7/15 | On-site NAP consistent; citations unverifiable; staging domain differs from legacy domain |
| Local Schema | 10% | 4/10 | Organization only, no LocalBusiness subtype, geo, hours |
| Local Links & Authority | 10% | 1/10 | Social links are generic homepages; no sameAs; no local link evidence |
| **Total** | | **28/100** | |

## Business type and vertical
- Type: hybrid. Visible street address (HQ Bekasi) plus national service area (3 offices). Operates B2B at client sites, so most "near me" demand is low. Branded and service-plus-region queries matter more than the map pack.
- Vertical: no standard match. Closest fit is Home Services/Contractor signals (installation, maintenance, licenses), but it is industrial B2B (fire hydrant, chiller, conveyor, PLC/HMI panels, jigs and fixtures, robots). Best GBP primary category candidates: "Industrial equipment supplier" or "Electrical installation service" or "Mechanical contractor" (verify the category list in GBP; the primary category is the top ranking factor, so choose by testing).
- Proximity (about 55% of local ranking variance) is outside our control. Bekasi Kabupaten (Tambun Selatan) is the only proximity anchor.

## NAP audit
| Source | Name | Address | Phone |
|---|---|---|---|
| Visible HTML (contact section) | PT. Trissan | Ruko Metland Tambun Blok A2 No.24, Tambun Selatan, Bekasi, Jawa Barat 17510, Indonesia | (021) 88373107 / (021) 88321576 |
| JSON-LD Organization | PT. Trissan | same (single streetAddress string) | +622188373107 (primary), both in ContactPoint |
| tel: links | n/a | n/a | +622188373107, +622188321576 |
| Maps link query | n/a | "Ruko Metland Tambun Blok A2 No.24 Tambun Selatan Bekasi" | n/a |
| Legacy site trissan.co.id (live) | PT. TRISSAN | "Blok A2 No.24. Tambun Selatan, Bekasi" (period after 24) | (+6221) 88373107 / (+6221) 88321576 |

Result: consistent on content, with formatting variance only. Gaps:
- [Medium] Schema `streetAddress` holds the whole address; `addressLocality`, `addressRegion`, `postalCode` are missing.
- [Medium] Name variants: "PT. Trissan" vs "PT. TRISSAN" vs the Maps embed title "PT. Trissan - Kantor Pusat". Pick one and match the GBP name exactly.
- [Medium] Phone shown as "(021) 88373107" on-page and "(+6221) …" on the legacy site. Use one format everywhere (+62 21 8837 3107).
- [High] Two domains: the legacy production site trissan.co.id (email marketing@trissan.co.id) and this staging-style domain trissan.mdhost.site. Business email and the canonical identity sit on a different domain than the audited site. If mdhost.site is launched as-is, the NAP's web entity splits. Move the final site to trissan.co.id (or confirm the intended canonical) before building citations.
- Branch addresses and phones for Malang and Timika appear nowhere (not on-page, not in schema), so branch NAP cannot be audited or cited.

## GBP signals
Detected: link to maps.google.com/?q=... (text search, not a place link); "Kantor Pusat" label; headquarters section with the address; two tel: links.
Missing or unverified:
- [Critical] No evidence a Google Business Profile exists. Bing searches for the company name and address returned no company profile, Maps card or directory listing in the top results (weak evidence, not proof; Google Maps could not be queried). Owner must check and claim or verify.
- [High] No Maps iframe embed, no `hasMap`/place URL (`https://maps.google.com/?cid=...`), no "write a review" link, no review widget.
- [High] No opening hours anywhere on the site (visible or schema).
- [Medium] No GBP posts, photos or Q&A evidence. Project photos exist in /works and /portofolio but are not tied to a GBP.
- [Medium] Malang and Timika offices need their own GBPs only if they are staffed, signed, and serve visitors (Google rules). Otherwise list them as service areas of the HQ profile or as a department on-site.
- Recommended GBP setup: name exactly "PT. Trissan", primary category chosen as above, secondary categories for the other services, service areas (Bekasi, Jakarta, Karawang, Malang, Papua/Timika), website pointing to the final domain, phone +62 21 8837 3107, attributes, 10+ project photos, and a UTM-tagged site URL.

## Reviews
- [High] No visible rating, review count, testimonials, client quotes or case-study quotes. No aggregateRating (correctly absent, since nothing to mark up). A client logo strip exists (Yamaha, Yaskawa, Daikin, Freeport), which adds trust, but names are not linked to case studies or permission statements.
- Velocity and response rate cannot be assessed (no GBP found). For B2B, collect 10 to 15 reviews from PIC contacts at client factories, plus testimonials on-page with named client, project and date.

## Citations (Tier 1)
- Not verifiable: Yelp returned 403 to scripted fetch, Google and DuckDuckGo blocked automated queries, and Bing showed no company listings. No BBB equivalent applies in Indonesia.
- Recommended Indonesia-relevant sources to check and create: Google Business Profile, Bing Places, Apple Business Connect, Facebook Page, Instagram, LinkedIn Company page, Yellow Pages Indonesia, Kompass, Indonetwork, Indotrading, Panduan/Pages (Pagesinfo), Bizapedia-style registries, KADIN Indonesia, GAPENSI or Gabungan Kontraktor Mekanikal Elektrikal, Dun and Bradstreet, and the Freeport/Timika vendor directories if listed.
- [High] Facebook and Instagram links point to https://www.facebook.com/ and https://www.instagram.com/ (generic). Outbound link to profiles is currently missing or broken; no sameAs. Treat the legacy site's identical placeholder links as the same bug, copied forward.

## Local schema
Present: Organization + WebSite (@graph, @id), name, url, logo, email, telephone, address (PostalAddress with only streetAddress and country), two ContactPoints (areaServed ID).
Issues:
- [High] No LocalBusiness subtype. Use `["Organization","ProfessionalService"]` or `GeneralContractor` (Schema.org has `GeneralContractor`, `ElectricalContractor`, `HVACBusiness`); choose the closest and keep Organization semantics. Do not use deprecated or invalid types.
- [High] Missing geo (5-decimal latitude/longitude), openingHoursSpecification, hasMap, areaServed (Bekasi, Malang, Timika, Indonesia), image, priceRange (optional; skip for B2B), and sameAs.
- [Medium] Missing addressLocality, addressRegion, postalCode as separate fields.
- [Medium] Branches: model Malang and Timika as `department` or separate `LocalBusiness` nodes with `@id` and `parentOrganization`/`branchOf` pointing to `https://<final-domain>/#organization`, each with its own PostalAddress, once real addresses exist.
- [Low] No Service schema for the 12 services (also noted in schema.md).

## Location pages
- [High] No dedicated pages for Bekasi, Malang or Timika. "3 Cabang Nasional / Bekasi · Malang · Timika" and "tiga kantor di Bekasi, Malang, dan Timika" are the only mentions, plus a counter "3 Kantor Cabang". No addresses, phones, hours, staff, map or photos for Malang and Timika.
- Doorway risk is currently nil (no location pages). When adding them, each page needs a real address, local phone, local project proof (for example Freeport/Timika projects), team, and unique copy (target at least 60 percent unique). Do not clone with a city swap.
- [High] Dedicated service pages are the top local organic factor and no service pages exist (services are an overview block with in-page anchors to /works#...). Build 12 service pages, then service-plus-region pages where real coverage exists (for example "instalasi chiller Bekasi", "panel kontrol Malang").
- Internal linking: footer and nav have no office or service-area links.

## Industry-specific factors
- Industrial B2B buyers verify credentials. /license exists, but without the legal identifiers (NIB, SBU Konstruksi, SBUJK, K3 and SKT numbers) visibly in structured text, local trust signals are limited. Add certification numbers, client lists, and project proof near the contact block.
- Indonesian signals: add `inLanguage` id plus English alternates if targeting multinationals (Freeport, Daikin). Add WhatsApp contact (no wa.me link detected; form opens WhatsApp via script only).
- [Low] Email is cloaked on page ([email protected]) and fine for spam, but schema exposes it. The email domain (trissan.co.id) differs from the site domain.

## Prioritized actions
1. [Critical] Search Google Maps for "PT Trissan" and claim, verify, or create the GBP (HQ Bekasi). Confirm category, hours, service areas, and website.
2. [Critical] Decide the production domain (trissan.co.id vs mdhost.site) before citations and GBP are tied to a URL. The legacy trissan.co.id currently serves around 4,000 spam outbound links (a compromise indicator), which would undermine any local link-trust work. Clean it before reusing it or redirecting from it.
3. [High] Replace generic facebook.com/instagram.com links with real profile URLs, add sameAs (Facebook, Instagram, LinkedIn, GBP).
4. [High] Add LocalBusiness-type schema with full PostalAddress, geo, hours, areaServed, hasMap.
5. [High] Embed Google Maps (iframe) and a place-based link on the contact section; add opening hours as visible text.
6. [High] Publish Malang and Timika branch details (address, phone, hours) and branch pages with unique content; add branch nodes to schema.
7. [High] Build dedicated service pages for the 12 services, then region variants where genuine.
8. [High] Start a review program: ask 10 to 15 clients for GBP reviews and add named testimonials on-page (then add Review/aggregateRating only if marked up honestly).
9. [Medium] Standardize NAP format and name ("PT. Trissan") across site, schema, GBP, and directories; add split address fields.
10. [Low] Build citations: Bing Places, Apple Business Connect, Indonetwork, Indotrading, Kompass, Yellow Pages Indonesia, KADIN, industry associations.

## Limitations
No Google Maps/GBP, Yelp, local-pack, or review data was accessible; citation checks relied on blocked or low-signal search results. No geocoding of the address, no branch addresses to compare, and no backlink/local link data. Proximity effects not assessable. Rating, review velocity and response rate are therefore "not assessable" rather than scored on evidence.
