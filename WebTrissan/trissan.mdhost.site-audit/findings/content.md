# Content Quality: 36/100

Scope: 5 public pages (`/`, `/about`, `/portofolio`, `/license`, `/works`), fetched 2026-10-29 (server-rendered, not an SPA). Scoring uses trafilatura `extracted_text`. Language: Indonesian (lang=id). The site was judged against Indonesian B2B search habits, where queries are mostly in Indonesian with industry English mixed in (e.g. "jasa pembuatan conveyor Bekasi", "kontraktor fire hydrant Cikarang", "jasa panel listrik PLC").

| Score | Value |
|---|---|
| Content quality | **36 / 100** |
| E-E-A-T (weighted, internal model) | **34 / 100**: Experience 55, Expertise 35, Authoritativeness 25, Trust 25 |
| AI citation readiness | **38 / 100** |
| Metadata templating (`metadata_template.py`, 5 pairs) | site_risk **low**, templated_ratio **0.0**, shared_cta_phrases **none** |

**What works**
- Real client projects with named sites, e.g. "PT Astra Honda Motor Plant 3 MM2100 Cibitung", Mayora Indah, Indofood CBP Purwakarta, Nihon Plast, Mikuni and Yaskawa-AHM. Each has a dated scope in the modal `data-desc` (24-27 Nov 2023). These are good first-hand experience signals.
- The org chart on `/about` is in crawlable text and lists 24 named people with roles (Commissioner, Director, COO, engineers, R&D, Timika rep). Workshop machines (milling, cutting, CNC) are described with photos.
- The Bekasi head office NAP is complete (Ruko Metland Tambun Blok A2 No.24, 17510), with 2 landlines and Organization JSON-LD.
- The prose is clear Indonesian with about 12-14 words per sentence on the prose pages, which is easy to read. Titles and descriptions are unique per page with no bulk-template pattern.

## [Critical] License page publishes placeholder/fabricated certifications
Evidence: `/license` shows "Data contoh" and "Ganti melalui CMS" visibly, plus the sentence "Data pada halaman ini adalah contoh dan perlu diganti dengan dokumen resmi." It also lists invented credentials that don't fit the business: AS9100D Aerospace "SAE International & Nadcap Approved CERT-AS-9100D-019", ASME "Stamp U ASME-STAMP-U-4412", ISO 9001 by LRQA "CERT-ISO-9001-2024", "Turbine Shaft Hardening Nadcap Audit Pass ... Inconel 625", "IEC 61439-2 ... 65 kA", "Zeiss CMM ... 0.1 µm". Two of them show as already **expired**: ISO 45001 valid until 10 Feb 2026 and SMK3 until 14 Jan 2026. The downloadable PDFs are under `/storage/seed/certificates/`.
Why it matters: under the QRG, unverifiable or false credentials are a direct Trust failure and can pull a whole site toward a Lowest rating. A procurement officer who checks one certificate number loses trust in every other claim on the site.
Fix: take all sample rows and product certificates down now (unpublish in the CMS or hide the section). Republish only real documents. For an Indonesian M/E contractor the usual ones are NIB (OSS-RBA) with KBLI codes, SBU from LPJK, SKK/SKA for engineers, K3 Listrik/K3 Umum (Kemnaker), IUJPTL if applicable, and ISO only if actually held. Show the issuer, number, validity and a scan for each.

## [Critical] Fabricated "Matriks Direktori 5 Personil Terdaftar" on /portofolio
Evidence: anonymous entries such as "REG: ENG-CNC-001 Lead 5-Axis CNC Machinist, Fokus Teknis: Inconel & Titanium Aerospace Blisks", "High-Tolerance Subsea Valve Shafts", "Chief Metrologist ... AS9100 First Article Inspection" and "ASME Sec IX Clad Overlay & Pressure Vessels". These are labelled "Terverifikasi" (verified) but belong to a different industry (aerospace/subsea) and don't match the real 24-person org chart on `/about`.
Fix: delete the registry. Replace it with real team members from the org chart: name, role, years of experience, and real certificates (e.g. Ahli K3 Listrik, SKK Teknik Mekanikal, PLC vendor training) linked to the projects they led.

## [High] Trust claims not backed by evidence on the site
Evidence: the home "DIPERCAYA PERUSAHAAN TERKEMUKA" strip shows YAMAHA, YASKAWA, DAIKIN and FREEPORT, but only Yaskawa appears in any project. `/portofolio` claims "20+ Klien Industri ... Pertambangan" while `/works` says "6 Klien Proyek" and shows no mining project. "145 Karyawan" isn't explained anywhere. There's no founding year anywhere, and the "Milestone" timeline has a single entry (2023).
Fix: show only logos of clients you have work for (add a Freeport/Timika project if one exists). Use one consistent client count. Add the founding year, the legal entity details (NIB/NPWP) and a real multi-year milestone timeline.

## [High] Service offering is thin: 12 services as one-line blurbs, no service URLs
Evidence: each service gets one sentence, e.g. "Sistem lubrikasi otomatis untuk alat berat." Services link to `/works?category=...` filters, which show 0-2 projects. Home `extracted_text` is 451 words, below the 500-word homepage floor, and none of the 12 services clears the 800-word service-page floor because none has its own page.
Fix: build Indonesian service pages for the 4-6 core money terms: "Jasa Pembuatan Konveyor", "Instalasi Fire Hydrant", "Panel Listrik, PLC & HMI", "Maintenance Mesin & Konveyor", "Auto Grease System", "Jigs & Fixtures". Each should have 800+ words covering scope, process, materials/brands, related projects, areas served (Bekasi, Cikarang, MM2100, Jababeka, Karawang, Purwakarta, Malang, Timika), an FAQ and a WhatsApp CTA.

## [Medium] Project entries are 1-2 sentences, and the modal spec fields are empty
Evidence: e.g. Nihon Plast: "Proyek drying conveyor untuk PT. Nihon Plast." `data-tolerance` and `data-batch` are empty on all 7 cards. The modal headings "Spesifikasi Utama" and "Sertifikasi & Standar Mutu" render empty, and the sort options "Toleransi Tertinggi (Micron)" and "Skala Batch Terbesar" sort on no data.
Fix: write 150-300-word case studies (problem, solution, specs such as belt width/length/motor/PLC brand, duration, result, photos). Remove the sort options and modal fields that have no data.

## [Medium] Freshness: no new work since November 2023
Evidence: all 7 projects are dated 24-27 Nov 2023, the milestone list stops at 2023, and the footer says © 2026. Nothing on the site signals an update in about 3 years.
Fix: add recent projects (2024-2026) with dates, extend the milestone timeline, and show "Diperbarui" dates on project and service pages.

## [Medium] English-first headings on an Indonesian site
Evidence: H3/H4 service names ("Workforce & Labor Solutions", "Other Special Construction"), "Contact Us", "EXPLORE WORKS", "Deliverables" and the `/works` category labels ("Selection, Placement And Provision Activities domestic Part-time Labor") are English or KBLI-style.
Fix: use Indonesian primary wording and keep established English terms in parentheses, e.g. "Penyediaan Tenaga Kerja (Outsourcing)", "Sistem Konveyor (Material Handling)", "Hubungi Kami".

## [Medium] Icon ligature text pollutes headings and extracted content
Evidence: Material Symbols names end up in the text: "precision_manufacturing", "smart_toy", "analytics", "zoom_in", and the H4 "tuneSpesifikasi Utama" / "verifiedSertifikasi & Standar Mutu". The HTML also has empty H2/H3 elements (in the `/works` and `/portofolio` modals).
Fix: render icons as SVG, or as ligature spans with `aria-hidden="true"` outside heading elements. Give modal headings static text or make them non-heading elements.

## [Medium] Branch offices have no addresses
Evidence: Malang and Timika only appear in "Bekasi • Malang • Timika" and as "kantor perwakilan Timika". There's no address, phone or map for either.
Fix: add full NAP for each office (one short location section or page per office) and match it to Google Business Profiles.

## [Medium] Weak AI citation readiness
Evidence: no FAQ, no definition-style answer paragraphs, and no founding year, legal name format, service area list or capacity figures in plain text. The email is hidden behind Cloudflare email obfuscation in the HTML; it's only plain in the JSON-LD.
Fix: add a short "Sekilas PT. Trissan" fact block (founded, legal entity, NIB, offices, industries, headcount, key clients) and a 6-8 question Indonesian FAQ on the home and service pages (biaya, durasi, area layanan, garansi/purna jual). Show the email as plain text.

## [Low] Boilerplate repeated on every page
Evidence: the paragraph "PT. Trissan didirikan dengan tujuan memberi solusi..." appears in the home hero/about section, on `/about`, and in the footer of all 5 pages. It is generic mission copy with no specifics.
Fix: keep one specific version on `/about` and replace the footer copy with a one-line factual summary.

## [Low] Non-functional Privacy Policy / Terms links
Evidence: the footer shows `<span>Privacy Policy</span> <span>Terms of Service</span>` as plain text with no links or pages. The contact form collects name, email and phone.
Fix: publish a "Kebijakan Privasi" page (UU PDP No. 27/2022 applies to form data) and link it from the form and footer.

## [Low] WhatsApp contact missing
Evidence: the footer label says "TELEPON HOTLINE & WHATSAPP" but lists only (021) landlines, and there's no `wa.me` link. Indonesian B2B buyers expect a WhatsApp contact.
Fix: add a `wa.me/62...` mobile link next to the phone numbers and on every service CTA.

## [Low] Metadata (secondary)
Evidence: 3 descriptions (`/about`, `/portofolio`, `/license`) repeat the brand "PT. Trissan" that's already in the title (`brand_suffix_in_description`). There are no templated, duplicated or shared-CTA patterns.
Fix: optional. Use the description space for location and service terms instead, e.g. "...kontraktor konveyor & fire hydrant di Bekasi/Cikarang".

## [Info] AI-generated content assessment
The prose itself reads human and specific where it describes real projects. The quality problem is placeholder template data left from a theme/seed (aerospace, subsea and Nadcap content) that contradicts the real business, not AI phrasing. Removing it is the single biggest content-quality gain available.

## Word counts (extracted / visible incl. chrome)
`/` 451 / 1113 · `/about` 223 / 691 · `/portofolio` 264 / 477 · `/license` 238 / 673 · `/works` 137 / 628

**Final Content Quality score: 36 / 100.** It is pulled down mainly by the publicly visible sample certifications and personnel registry (Trust). It would recover to about 60-65 once those are removed and real NIB/SBU/K3 credentials are added, and to 75+ with Indonesian service pages and expanded case studies.
