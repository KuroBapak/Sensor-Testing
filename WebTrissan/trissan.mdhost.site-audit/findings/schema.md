# Schema / Structured Data Audit - https://trissan.mdhost.site/

Pages checked: `/`, `/about`, `/portofolio`, `/license`, `/works` (raw HTML fetch, 2026-10-29).
Score: **62/100**

## 1. Detection

| Page | Format | Blocks | Types found |
|---|---|---|---|
| `/` | JSON-LD (single `@graph`) | 1 | Organization, PostalAddress, ContactPoint x2, WebSite |
| `/about`, `/portofolio`, `/license`, `/works` | JSON-LD (single `@graph`) | 1 each | Same as `/` plus BreadcrumbList (Beranda > page) |

- No Microdata or RDFa. JSON-LD is in the raw server HTML (not client-injected).
- No FAQPage, HowTo, SpecialAnnouncement or other deprecated types.

## 2. Validation

| Check | Result |
|---|---|
| JSON parses, `@context` = `https://schema.org` | PASS (all 5) |
| `@id` cross-reference (`WebSite.publisher` -> `#organization`) | PASS |
| Absolute URLs (`url`, `logo`, breadcrumb `item`) | PASS |
| Logo URL resolves | PASS (HTTP 200, `/storage/seed/logo.png`) |
| Placeholder text in JSON-LD | PASS (none) |
| Organization required fields | PASS (Organization has no hard requirements) |
| BreadcrumbList position/name/item | PASS |
| Breadcrumb/`url` match canonical (no trailing slash) | PASS |

No blocking errors. Issues below are completeness and quality gaps.

## 3. Findings

### [High] No `sameAs`; social links are generic homepages
Footer links go to `https://www.facebook.com/` and `https://www.instagram.com/`, not company profiles. Organization has no `sameAs`, so there is no entity corroboration.
**Fix:** Find or create the real profiles (Facebook, Instagram, LinkedIn, Google Business Profile), fix the footer links, add them to `sameAs` (block A).

### [Medium] Address is one unstructured string
`streetAddress` holds the whole address. `addressLocality`, `addressRegion`, `postalCode` are missing. **Fix:** split the fields (block A).

### [Medium] Branches (Malang, Timika) absent from schema
The site says "tiga kantor di Bekasi, Malang, dan Timika", but only Bekasi is in the schema. Branch addresses are not visible on the pages checked, so the client must supply them.

### [Medium] No physical-business signals
No `geo`, `hasMap`, `areaServed` or `openingHoursSpecification`. The page already links Google Maps for the HQ address (reusable as `hasMap`). Opening hours are not on the site; confirm with the client. Do not invent coordinates.

### [Medium] Staging domain in every `@id`, `url` and breadcrumb
All identifiers use `trissan.mdhost.site`, while the email is `@trissan.co.id`. At go-live the production domain must replace it everywhere.
**Fix:** build schema URLs from `config('app.url')` and confirm staging is `noindex` (see technical findings).

### [Medium] Services not in structured data
The homepage lists 12 services. **Fix:** `OfferCatalog` / `Service` nodes with `provider` = `#organization` (block B). No Google rich result exists for Service; the value is entity clarity.

### [Low] No page-level `WebPage` nodes
BreadcrumbList has no `@id` and is not tied to a page node. **Fix:** block C.

### [Low] Organization lacks descriptive properties
Missing `description`, `legalName`, `foundingDate`, `numberOfEmployees`. `/portofolio` shows 145 employees, but that page also holds sample data, so confirm with the client before marking it up. No confirmed founding year was found.

### [Low] Project pages have no list markup
`/works` and `/portofolio` show 7 documented projects (Astra Honda Motor, Mayora Indah, Nihon Plast, Indofood CBP, Mikuni, Yaskawa-AHM). An `ItemList` helps entity understanding (block D). Do NOT use Product, Review or AggregateRating: the site has no ratings or reviews.

### [Low] Two ContactPoints with the same `contactType`
Both are "customer service". Label the roles (e.g. sales vs. general) or keep one.

### [Info] `/license` must stay unmarked until real data is in
The page states its data is sample ("data contoh ... perlu diganti dengan dokumen resmi"). It lists ISO 9001, AS9100D, ASME U-Stamp and ISO 45001/14001 with fake-looking IDs. Do not emit `hasCredential` from it: wrong credentials in schema is a trust and compliance risk. Expiry dates for ISO 45001 (10 Feb 2026) and the ASME stamp (1 Sep 2026) are already past as of today. Aerospace/ASME claims also do not match the stated business, so these look like template leftovers. Block E is the template for later.

### [Info] `/portofolio` personnel directory is sample content
Entries such as "Lead 5-Axis CNC Machinist ... Inconel & Titanium Aerospace Blisks" do not fit the business. Do not add `Person`/`employee` schema for them.

### [Info] FAQPage / HowTo
None present, none recommended (FAQ rich results retired; HowTo deprecated).

### [Info] Strengths
Single clean `@graph`, stable `@id`s across pages, `inLanguage: "id"` consistent with `og:locale id_ID`, E.164 phone numbers, valid logo, no obsolete `SearchAction`.

## 4. Ready-to-paste JSON-LD

Replace every `REPLACE_*` value with confirmed facts, or delete the property. Use the production domain at launch.

### A. Organization (replaces the existing Organization node; site-wide)
```json
{
  "@type": ["Organization", "ProfessionalService"],
  "@id": "https://trissan.mdhost.site/#organization",
  "name": "PT. Trissan",
  "legalName": "REPLACE_LEGAL_NAME",
  "slogan": "Always Present Better",
  "description": "PT. Trissan menyediakan produk dan layanan kelistrikan serta mekanikal industri: material handling, otomasi, panel distribusi, hingga perawatan mesin.",
  "url": "https://trissan.mdhost.site",
  "logo": "https://trissan.mdhost.site/storage/seed/logo.png",
  "email": "marketing@trissan.co.id",
  "telephone": "+622188373107",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Ruko Metland Tambun Blok A2 No.24",
    "addressLocality": "Tambun Selatan, Bekasi",
    "addressRegion": "Jawa Barat",
    "postalCode": "17510",
    "addressCountry": "ID"
  },
  "hasMap": "https://maps.google.com/?q=Ruko+Metland+Tambun+Blok+A2+No.24+Tambun+Selatan+Bekasi",
  "areaServed": { "@type": "Country", "name": "Indonesia" },
  "hasOfferCatalog": { "@id": "https://trissan.mdhost.site/#services" },
  "sameAs": [
    "REPLACE_FACEBOOK_PAGE_URL",
    "REPLACE_INSTAGRAM_PROFILE_URL",
    "REPLACE_LINKEDIN_COMPANY_URL",
    "REPLACE_GOOGLE_BUSINESS_PROFILE_URL"
  ],
  "contactPoint": [
    { "@type": "ContactPoint", "telephone": "+622188373107", "email": "marketing@trissan.co.id", "contactType": "customer service", "areaServed": "ID", "availableLanguage": "id" },
    { "@type": "ContactPoint", "telephone": "+622188321576", "contactType": "REPLACE_ROLE", "areaServed": "ID", "availableLanguage": "id" }
  ],
  "department": [
    {
      "@type": "Organization",
      "@id": "https://trissan.mdhost.site/#branch-malang",
      "name": "PT. Trissan - Kantor Malang",
      "address": { "@type": "PostalAddress", "streetAddress": "REPLACE_MALANG_STREET", "addressLocality": "Malang", "addressRegion": "Jawa Timur", "postalCode": "REPLACE", "addressCountry": "ID" }
    },
    {
      "@type": "Organization",
      "@id": "https://trissan.mdhost.site/#branch-timika",
      "name": "PT. Trissan - Kantor Timika",
      "address": { "@type": "PostalAddress", "streetAddress": "REPLACE_TIMIKA_STREET", "addressLocality": "Timika", "addressRegion": "REPLACE_PROVINCE", "postalCode": "REPLACE", "addressCountry": "ID" }
    }
  ]
}
```
Optional once confirmed: `"foundingDate": "REPLACE_YYYY"`, `"numberOfEmployees": {"@type":"QuantitativeValue","value":145}`, `"geo"` (GeoCoordinates), `"openingHoursSpecification"`.

### B. Service catalog (add to the `@graph` on `/`)
```json
{
  "@type": "OfferCatalog",
  "@id": "https://trissan.mdhost.site/#services",
  "name": "Layanan PT. Trissan",
  "itemListElement": [
    { "@type": "Service", "name": "Mechanical & Electrical", "description": "Rekayasa mekanikal presisi dan instalasi kelistrikan industri terpadu.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Digital Automation & Controlling", "description": "Sistem kendali terotomasi modern dengan PLC/SCADA.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Jigs & Fixtures", "description": "Fabrikasi jig presisi dan clamping fixture.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Unload Robot Chucking", "description": "Sistem chucking otomatis untuk robot industri.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Material Handling", "description": "Konveyor dan sistem logistik internal pabrik.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Distribution Panel, PLC, HMI", "description": "Perakitan panel distribusi dan pemrograman HMI.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Electrical & Machine Maintenance", "description": "Perawatan preventif peralatan listrik dan mesin.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Electrical Equipment Supplier", "description": "Pengadaan komponen kelistrikan industri.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Auto Grease System (Loader)", "description": "Sistem lubrikasi otomatis untuk alat berat.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Industrial Machinery & Parts", "description": "Pemasok peralatan industri dan suku cadang.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Other Special Construction", "description": "Pekerjaan konstruksi spesialis industri.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" },
    { "@type": "Service", "name": "Workforce & Labor Solutions", "description": "Penyediaan tenaga kerja terampil manufaktur.", "provider": { "@id": "https://trissan.mdhost.site/#organization" }, "areaServed": "ID" }
  ]
}
```
No prices appear on the site, so no `Offer` is included.

### C. Page node (example `/about`; repeat per page)
Use `AboutPage` for `/about`, `CollectionPage` for `/works` and `/portofolio`, `WebPage` for `/` and `/license`.
```json
{
  "@type": "AboutPage",
  "@id": "https://trissan.mdhost.site/about#webpage",
  "url": "https://trissan.mdhost.site/about",
  "name": "REPLACE_WITH_PAGE_TITLE",
  "inLanguage": "id",
  "isPartOf": { "@id": "https://trissan.mdhost.site/#website" },
  "about": { "@id": "https://trissan.mdhost.site/#organization" },
  "breadcrumb": { "@id": "https://trissan.mdhost.site/about#breadcrumb" }
}
```
Add `"@id": "https://trissan.mdhost.site/about#breadcrumb"` to the existing BreadcrumbList on that page.

### D. Project list (add to `/works`; mirror on `/portofolio`)
```json
{
  "@type": "ItemList",
  "@id": "https://trissan.mdhost.site/works#projects",
  "name": "Portofolio Proyek PT. Trissan",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Fasum OP Mainline Factory Water Meter & Bypass Chiller Installation" },
    { "@type": "ListItem", "position": 2, "name": "Fire Hydrant System" },
    { "@type": "ListItem", "position": 3, "name": "Chiller Duct Bypass" },
    { "@type": "ListItem", "position": 4, "name": "Drying Conveyor" },
    { "@type": "ListItem", "position": 5, "name": "Conveyor Maintenance" },
    { "@type": "ListItem", "position": 6, "name": "Material Handling Conveyor for Automation" },
    { "@type": "ListItem", "position": 7, "name": "Tending Conveyor Auto Cutting Automotive Parts" }
  ]
}
```
Generate from the database in the Blade view; add per-item `url` if project detail pages are created.

### E. Certifications (DO NOT publish until `/license` holds real data)
```json
{
  "@type": "EducationalOccupationalCredential",
  "name": "REPLACE_CERT_NAME",
  "credentialCategory": "certification",
  "recognizedBy": { "@type": "Organization", "name": "REPLACE_ISSUER" }
}
```
Attach via `"hasCredential": [ ... ]` on the Organization node.

## 5. Implementation notes (Laravel)
- Build the `@graph` once in a shared Blade partial from `config('app.url')` and DB-backed settings (address, phones, branches, social URLs), so the staging-to-production switch needs no schema edits.
- Emit with `json_encode(..., JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)` in `<script type="application/ld+json">`.
- Re-validate with Google Rich Results Test and validator.schema.org after changes.

## 6. Score: 62/100
Strong base (valid, server-rendered, consistent IDs, correct breadcrumbs) offset by missing `sameAs`, unstructured address, no branches/geo/hours, no Service or page-level nodes, and staging-domain IDs. Completing blocks A to D with real data should reach roughly 85 to 90.
