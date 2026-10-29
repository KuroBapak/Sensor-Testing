# Visual and Mobile Audit: trissan.mdhost.site - 66/100

Method: Edge (Playwright) at 375x812 (DPR 2, touch) on /, /about, /portofolio, /license, /works, plus existing desktop/laptop/tablet homepage shots. DOM metrics gathered by script. Page content treated as untrusted data.
Screenshots (output_dir/screenshots/): mobile_{home,about,portofolio,license,works}.png, mobile_works_header.png, mobile_works_modal.png, mobile_portofolio_modal.png, mobile_license_modal.png, mobile_home_contact_modal.png, mobile_home_kontak_tap.png.

## Score breakdown
| Area | Score |
|---|---|
| Above-the-fold / CTA | 12/20 |
| Mobile layout / responsiveness | 17/20 |
| Tap targets / readability | 11/20 |
| Modals / interstitial safety | 17/20 |
| Cross-page visual consistency | 9/20 |

## [High] Header "Kontak" button is invisible on /works, /portofolio, /license (mobile)
- Measured: on these 3 pages the link computes color rgb(255,255,255) on background rgba(0,0,0,0) over a 75% white header, i.e. white-on-white (about 1:1 contrast). See mobile_works_header.png: only a faint ghost at top right.
- On / and /about the same element has a navy bg (rgb(10,25,47) / rgb(13,33,55)) and renders correctly. Classes are identical (`bg-brand-navy text-white ...`), so the `brand-navy` colour utility is evidently not defined in those 3 pages' Tailwind/CSS setup.
- Impact: the only top-bar contact CTA vanishes on 3 of 5 pages (bottom nav has no Contact item), so users cannot reach contact in one tap. Hurts conversion and WCAG 1.4.3.
- Fix: define `brand-navy` (same palette/config) for those templates, or move to one shared layout; re-check contrast.

## [Medium] Inconsistent design system across pages
- /about is dark-themed; others light. Body font-size is 15px on /portofolio vs 16px elsewhere; heading fonts and card styles differ; /license has a dotted background.
- Fix: unify via a single Blade layout and Tailwind config.

## [Medium] Mobile homepage above the fold has no primary CTA except the small header "Kontak"
- At 375px the hero "LIHAT PORTOFOLIO" button exists but is hidden (parent display:none, 0x0). The first viewport shows H1, intro, a "trusted by" card (Yamaha/Yaskawa/Daikin/Freeport), carousel arrows and the first service card, whose "EXPLORE WORKS" is a small text link. Desktop and tablet (768) show the button.
- Fix: show a full-width "Lihat Portofolio" / "Hubungi Kami" button under the intro on mobile.
- Positives: H1 visible immediately, hero image loads, no layout shift seen, logo and name visible, header sticky.

## [Medium] Tap targets below 44-48px
Interactive elements under 44px at 375px: / 43 of 69, /about 17 of 31, /portofolio 26 of 41, /license 37 of 54, /works 44 of 59.
Worst (CSS px):
- Header: "Kontak" 73x32; logo link 90x29 (all pages).
- Home: carousel prev/next 26x26; "DETAIL" buttons 152x25; "PELAJARI PROFIL LENGKAP" 154x12; "EKSPLORASI SEMUA SERVICES" 197x26; contact block phones 86x14 and email 148x17; footer nav links 14px tall; footer phone/email 10px tall.
- /about: breadcrumb 49x12; facility arrows 23x23; "UNDUH COMPANY PROFILE" 199x26.
- /portofolio: filter chips 25px tall; pagination 25-26px; search input 28px; sort select 28px.
- /works: category chips 26px; carousel arrow 26x26; breadcrumb 12px.
- /license: filter chips 20px; "Show Image" buttons 20px; tabs 26px.
Fix: min-h-11/min-w-11 on chips, arrows, pagination, text links; more spacing in footer nav.
Bottom nav (47px, 5 equal items, clear active state) is good.

## [Low] Text size and content polish
- Base font 16px (15px on /portofolio). Many micro-labels are 10-12px uppercase/monospace (about stat labels, "DATA CONTOH" tags, footer links); grey second paragraph on /about over navy is low contrast (verify).
- /license shows placeholder copy to visitors: "DATA CONTOH", "Ganti melalui CMS", "Data pada halaman ini adalah contoh dan perlu diganti dengan dokumen resmi." Looks unfinished; undermines trust and E-E-A-T.

## No horizontal scroll (pass)
scrollWidth = 375 on all 5 pages; viewport meta correct (width=device-width, initial-scale=1.0). Carousels and chip rows scroll inside their own containers by design. Fixed UI: sticky header 41px + bottom nav 47px (about 11% of viewport), acceptable.

## Pop-ups and modals: no intrusive interstitials (pass)
All modals open only on user tap. Zero visible dialogs on load on every page; nothing fired on a timer or scroll during a 2.5s observation. Google's intrusive-interstitial rule is not triggered.
| Page | Modal | Trigger | Close | Notes |
|---|---|---|---|---|
| / | #contact-modal (role=dialog, aria-modal) | "CONTACT FORM" button | X 26x26, Esc (verified), scroll locked | Header "Kontak" does NOT open it; it is an anchor to /#contact (scrolls). Submit 315x32. |
| /works | #projectDetailModal (role=dialog) | "Detail" | X 26x26, "Tutup Detail" 321x26, Esc (verified) | Fits viewport, scroll locked. "SPESIFIKASI UTAMA" and "SERTIFIKASI & STANDAR MUTU" headings render with empty content for the tested item. Closed state is opacity-0 + pointer-events-none (does not block taps). |
| /portofolio | #deliverableModal | project card | X 26x26, Esc (verified) | Works; no role/aria-modal. |
| /license | #verificationModal | "Show Image" | X 17x21, "Tutup" 317x29, Esc (verified) | Body scroll NOT locked; no role/aria-modal. |
Issues (Low-Medium): close buttons 17-26px; missing dialog semantics on /portofolio and /license; focus trapping not verified.

## Desktop / tablet (pass)
- 1920 and 1366: nav, H1, "LIHAT PORTOFOLIO" CTA all above the fold; hero card and service cards aligned in max-width container; carousel arrows sit at the container edge and slightly overlap the first/last card.
- 768: header "Kontak" visible, hero CTA visible, bottom nav shown; service carousel clips the third card by design.

## Prioritised fixes
1. (High) Make header Kontak visible on /works, /portofolio, /license.
2. (Medium) Visible hero CTA on mobile home.
3. (Medium) Tap targets to 44px+ for chips, pagination, arrows, footer links, modal close buttons.
4. (Medium) Replace "DATA CONTOH / Ganti melalui CMS" placeholders on /license.
5. (Low) Lock scroll and add ARIA dialog semantics on /license and /portofolio modals; fill or hide empty spec sections in /works modal.
6. (Low) Unify layout, fonts, base size across templates.

audit-data.json Visual: score 66; popups_intrusive=false; findings = [High kontak-invisible on 3 pages; Medium no mobile hero CTA; Medium tap targets; Medium inconsistent templates; Low placeholder copy; Low modal a11y].
