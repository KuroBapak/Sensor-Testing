# Implementation Plan: CMS ↔ Frontend Full Integration & Backend Finalization

## Task Objective
Menyambungkan seluruh konten 5 halaman publik (Landing, Works, License, Portfolio, About) ke CMS Filament sampai tidak ada lagi konten bisnis yang hardcoded di Blade, sekaligus memperbaiki bug backend yang membuat data CMS tidak muncul / tidak konsisten.

## Source PRD / Context
- `prd.md` v2.1 (skema CMS, peta halaman → sumber data §11, temuan HTML §12, keputusan terbuka §13).
- Plan lama (semua tetap sebagai riwayat, **diganti oleh dokumen ini** untuk pekerjaan yang tersisa):
  `prd-v2-schema-alignment.md` (schema, seeder: selesai), `filament-admin-panel.md` (admin: selesai),
  `cms-pagesettings-audit-fix.md` (hero/stats/SEO: selesai), `landing-about-teaser-single-input.md` (selesai),
  `category-redirect-from-landing.md` & `featured-works-modal-redirect.md` (selesai, tapi bergantung pada data hardcoded, lihat B3),
  `frontend-integration.md` (**basi**: Phase 3–8 masih tidak dicentang padahal sebagian sudah dikerjakan; jangan dilanjutkan).

## Status
- Overall: **Complete** (kecuali butir di bagian "Sisa / Keputusan" di bawah)
- Active phase: **Tidak ada (semua phase selesai)**
- Dasar temuan: analisis statis kode (grep/baca source). **Test belum dijalankan**: `vendor/`, `.env`, `node_modules/` tidak ada di working copy ini, dan `phpunit.xml` memakai MySQL `laravel_test` (lihat Phase 0).

---

## 1. Hasil Scan: Status Integrasi per Halaman

Kategori: ✅ sudah dari CMS · ⚠️ sebagian · ❌ hardcoded penuh.

| Halaman (view) | Sudah dari CMS | Masih hardcoded |
|---|---|---|
| **Landing** `home.blade.php` (830 baris) | SEO, hero (slides, title, CTA), trust card, About Teaser, slider services (12), featured works (6), directory cluster, navbar/footer inline (logo, nama, deskripsi, alamat, phones, email, legal links, copyright) | ⚠️ Section **#contact** (telepon `tel:+62215508899` salah digit, alamat, email, kartu "Mission/Values", Google Maps, overlay lokasi, social media); tombol **CONTACT FORM → `#contact-modal` tidak ada**; stat About Teaser memakai `page_settings.stats` bukan `global_settings.company_stats` (PRD §4.1); link nav/footer statis; `$corporateAccreditations` dikirim controller tapi tidak dipakai |
| **Works** `works.blade.php` (548 baris) | SEO, hero title/subtitle, 4 stat card | ❌ **9 `<article class="project-card">` + 13 tab filter + semua `data-*` modal** (title, serial, sector, material, tolerance, batch, specs, standards, img, desc, kategori) tertulis statis. `$services`, `$works`, `$featuredWorks` dikirim controller tapi **tidak dipakai** di Blade |
| **License** `license.blade.php` (496 baris) | SEO, hero, hero badge card, 4 stat, 4 corporate highlight cards | ❌ **tabel akreditasi korporat** (6 baris, argumen `openVerificationModal('ISO 9001…','QMS-ID-980144',…)` statis), **tab kategori** (`btn-cat-*`), **4 kartu sertifikasi produk** (`cert-turbine-gearbox`, `cert-rpm-buzzer`, …), modal verifikasi tanpa `<img>` sertifikat, tombol "Check SHA"/"Download" hanya simulasi |
| **Portfolio** `portofolio.blade.php` (71 baris panjang) | SEO, hero, 5 stat, registry revision tag | ❌ **tab kategori** ("Semua Hasil Kerja (40)" dll.), **grid deliverables** (13 panggilan `openDeliverableModal`), **direktori personil** (`#personnelGrid`, `REG: MP-xx` hardcoded). `PortfolioController` **tidak mengirim `personnel`/`specialtyClusters`/`skills`** sama sekali |
| **About** `abtus.blade.php` (325 baris) | hanya `<title>` + meta description | ❌ **hampir semuanya**: hero + overlay + stat, Vision/Mission/Values, 3 facilities (`#fasilitas`, `#facility-carousel`), 5 milestones, struktur organisasi (CEO, GM, 4 division heads, `REV-2024.10`), partner image, tombol unduh profil PDF |
| **Layout** `layouts/navbar` + `layouts/footer` (dipakai 4 halaman non-landing) | nama, tagline, copyright | ❌ **Bug nama kolom** (lihat B1): logo, deskripsi footer, alamat, telepon, email selalu jatuh ke fallback. Gambar partner footer, legal links, social media tidak dirender dari CMS |

Kesimpulan: dari 5 view, **hanya Landing yang benar-benar terintegrasi (±75%)**. Works/License/Portfolio baru bagian hero; About belum tersentuh. Plan lama `cms-pagesettings-audit-fix.md` menandai "Complete" karena cakupannya memang hanya PageSettings (hero/SEO/stats), bukan koleksi.

## 2. Hasil Scan: Masalah Backend

| ID | Severity | Temuan | Bukti |
|---|---|---|---|
| **B1** | Tinggi | Navbar/footer layout membaca kolom yang **tidak ada** di tabel `global_settings`: `company_logo`, `footer_about_text`, `contact_address`, `contact_phone`, `contact_email`. Kolom asli: `navbar_logo`, `footer_logo`, `footer_description`, `factory_address`, `phones` (JSON), `primary_email`. Akibat: edit Global Settings di Filament **tidak berpengaruh** di Works/License/Portfolio/About | `layouts/navbar.blade.php:7`, `layouts/footer.blade.php:10,18,48,57,66` vs migration `…064852_create_global_settings_table.php` |
| **B2** | Tinggi | Seeder mengisi logo `'/images/logo.png'` & `'/images/logo-footer.png'`, tetapi `public/images/` **tidak ada** → logo landing 404 | `GlobalSettingSeeder.php:20-21`, `ls public` |
| **B3** | Tinggi | Filter Works tidak kompatibel dengan link Landing: controller memfilter `?service={id}`, sedangkan Landing/JS memakai `?category={slug}` dan hash `#work_slug`. Begitu card dirender dari DB, deep-link harus resolve slug → service dan `work_slug` → modal | `WorksController.php`, `category-redirect-from-landing.md`, `featured-works-modal-redirect.md` (slug saat ini dari `data-title`, bukan `work_slug`) |
| **B4** | Sedang | Featured Works Landing diurutkan `sort_order`, padahal PRD punya `featured_sort_order` (field ada di migration & Filament) | `HomeController.php`, `WorkResource.php:54` |
| **B5** | Sedang | `PortfolioController` memfilter `portfolio_category_id` + `paginate(12)` server-side, sementara UI/JS dan PRD §3 menetapkan search/sort/paginasi di frontend. Personil tidak dikirim. `AboutController` justru mengirim `specialtyClusters`/`personnel` yang tidak dipakai About (milik Portfolio per PRD §11) | `PortfolioController.php`, `AboutController.php` |
| **B6** | Sedang | `ContactController@store` hanya `back()->with('success')`; tidak ada form/modal di view, tidak ada tampilan `success`/`errors`, tidak ada throttle/honeypot, `notification_email` tidak dipakai | `ContactController.php`, `home.blade.php:509` |
| **B7** | Sedang (security) | `User::canAccessPanel()` selalu `true` + seeder membuat `admin@trissan.com` / `test@example.com` dengan password `password` → siapa pun user terdaftar bisa masuk panel; kredensial default lemah di seeder | `User.php`, `DatabaseSeeder.php` |
| **B8** | Rendah | `PageSetting::forPage('about')` ada di seeder tetapi tab `about` tidak ada di `ManagePageSettings` (4 tab saja); hero About dikelola di `AboutCompany` — bisa membingungkan admin (dua sumber SEO untuk About) | `ManagePageSettings.php`, `PageSettingSeeder.php:132` |
| **B9** | Rendah | Query tidak efisien: Works/Home memuat semua `Service`/`Work` berulang; `HomeController` memuat `CorporateAccreditation` yang tidak dipakai; belum ada eager-loading untuk relasi baru (skills, product certs) | controllers |
| **B10** | Rendah | Banyak gambar masih hotlink `lh3.googleusercontent.com` (home 8, about 3, works 2, portfolio 2) sebagai fallback; rapuh untuk produksi | grep per view |
| **B11** | Info | Test suite memakai MySQL (`phpunit.xml`: `DB_CONNECTION=mysql`, `laravel_test`) sementara `.env.example` = sqlite; `pdo_sqlite` dilaporkan tidak terpasang. Test tidak jalan di mesin tanpa MySQL + `vendor/` | `phpunit.xml`, plan `prd-v2-schema-alignment.md` |

PRD §12 yang **terverifikasi masih ada di kode**: #1 `tel:+62215508899` vs teks `+62 (21) 5508-8990` (home), #2 fallback brand "PT. Trissan" (home, navbar, footer), #3 footer "Portofolio" menuju `/works`, #5 `#contact-modal` tidak ada & `handleInquirySubmit` yatim (license), #14 legal links footer `href="#"`. Sisanya (#4, #6–#13, #15) belum diverifikasi ulang terhadap kode saat ini dan dicek di phase terkait.

## 3. Scope & Non-Goals

**Scope**
- Memperbaiki B1–B7 (B8–B10 sesuai kebutuhan phase).
- Mengubah Works, License, Portfolio, About, dan sisa Landing agar seluruh konten berasal dari model/CMS (koleksi + singleton), dengan fallback aman saat data kosong.
- Mempertahankan UX/JS yang ada (filter, search, sort, paginasi, modal) dengan mengganti sumber datanya, bukan mendesain ulang.
- Test Pest untuk perilaku baru.

**Non-goals**
- Tidak ada perubahan skema DB destruktif; migrasi tambahan hanya jika benar-benar perlu (mis. field slug/kolom yang kurang) dan non-destruktif.
- Tidak menambah dependency tanpa persetujuan.
- Tidak mendesain ulang tampilan; tidak memindah paginasi/search/sort ke server (PRD §3).
- Tidak mengedit plan lama.

## 4. Acceptance Criteria
1. Mengubah satu record di Filament (Global Settings, Page Settings, About Company, setiap koleksi) langsung tampil di halaman publik yang sesuai peta PRD §11.
2. Tidak ada teks/gambar bisnis hardcoded tersisa di 5 view selain fallback eksplisit ketika data kosong (grep daftar ruas hardcoded di §6 kosong).
3. Navbar/footer di semua 5 halaman memakai kolom `global_settings` yang valid dan satu implementasi (tanpa blok inline duplikat di Landing).
4. `/works?category={slug}` dan `/works#{work_slug}` membuka tab/modal yang benar; `/` Featured Works memakai `featured_sort_order`.
5. Portfolio menampilkan kategori, 40 item, dan direktori personil dari DB; modal menampilkan deskripsi; filter tab benar-benar bekerja.
6. License menampilkan tabel korporat + tab kategori + modal (dengan gambar sertifikat) dan 4 kartu produk dari DB, termasuk deep link `cert-*`.
7. About menampilkan semua section dari `AboutCompany` + `company_stats`.
8. Contact form berfungsi (modal Landing → `contact.store` → tersimpan, validasi + flash tampil, ada throttle).
9. Panel admin hanya bisa diakses user yang berhak; tidak ada kredensial default lemah untuk produksi.
10. `vendor/bin/pint --dirty --format agent` bersih, `php artisan test --compact` hijau, `git diff --check` bersih.

---

## 5. Phases

> Aturan status: `[ ]` belum · `[x]` selesai + bukti · `[!]` blocked + alasan. Tepat satu phase aktif.

### Phase 0 — Baseline & Environment *(Complete)*
Objective: punya baseline test yang bisa dipercaya sebelum mengubah apa pun.
- [x] `composer install` (sesuai `composer.lock`, tanpa mengubah dependency), `.env` dari `.env.example`, `php artisan key:generate`. Exit 0. *(`npm install` ditunda: belum ada perubahan aset; dijalankan di Phase 9 bila perlu.)*
- [x] DB test: MySQL `laravel_test` (keputusan user, Q1 selesai). Kredensial diisi user di `.env`.
- [x] `php artisan storage:link` OK (di Windows membuat *junction*, bukan symlink; lihat catatan baseline).
- [x] Baseline `php artisan test --compact`: **27 passed, 1 failed, 239 assertions** (lihat Log).
- [x] B1 terkonfirmasi terhadap skema yang termigrasi: kolom `company_logo`, `footer_about_text`, `contact_address`, `contact_phone`, `contact_email` **tidak ada** di `global_settings`; `navbar_logo`, `footer_description`, `factory_address`, `phones`, `primary_email` ada.
- [ ] B2–B4 belum dicek runtime (dikerjakan bersama Phase 1).
Exit: baseline test tercatat (terpenuhi); B1 terkonfirmasi.

Catatan baseline:
- 1 test gagal = `PageSettingsRenderingTest › public storage symlink exists…` karena `is_link()` PHP mengembalikan `false` untuk *junction* Windows. Masalah environment, bukan kode aplikasi. Usulan: longgarkan assertion jadi `file_exists` + `is_dir` (diputuskan di Phase 1).
- Run pertama sempat 2 gagal (`ExampleTest` + 1 lagi) dengan `Unknown database 'laravel_test'`; tidak muncul lagi di 3 run berikutnya (kemungkinan DB baru dibuat/terhubung saat itu). Dicatat sebagai transien.

### Phase 1 — Backend & Data Foundations *(Complete)*
Objective: perbaiki akar masalah sebelum menyentuh view.
Depends on: Phase 0.
- [x] **B1/B2**: `App\Support\MediaUrl::resolve()` (URL absolut / file di `public/` hanya bila ada / path disk `public`) + accessor `navbar_logo_url`, `footer_logo_url`, `footer_partner_image_url` di `GlobalSetting`. `layouts/navbar` & `layouts/footer` memakai kolom yang benar (`footer_description`, `factory_address`, `phones[]` dengan `tel_url`, `primary_email`, `corporate_email`, logo & partner banner dari CMS). `home.blade.php`: 3 blok `str_starts_with` diganti accessor. `GlobalSettingSeeder`: logo/partner = `null` (file `/images/*` tidak ada) sehingga fallback dipakai. Bukti: `SharedLayoutGlobalSettingsTest` (5 halaman × field global) + `MediaUrlTest`.
- [x] **B3**: `WorksController` mengirim semua `Work` (`with('service')`, tanpa paginasi/filter server, `featuredWorks` yang tidak dipakai dihapus). Filter `?category={slug}` dan hash `#work_slug` ditangani frontend (Phase 3). *Penyimpangan dari rencana awal*: `?service=` tidak dipertahankan karena tidak dipakai siapa pun. Bukti: `PublicPageDataTest`.
- [x] **B4**: featured works urut `featured_sort_order` lalu `sort_order`, `take(6)`. Bukti: `PublicPageDataTest`. *(Batas maks 6 di Filament belum ditambahkan, ditunda ke Phase 3.)*
- [x] **B5**: `PortfolioController` mengirim semua item, kategori + `portfolio_items_count`, `specialtyClusters`, personil aktif (`specialtyCluster`, `skills`); `AboutController` tidak lagi mengirim data personil. Bukti: `PublicPageDataTest`, `FrontendRoutesTest`.
- [x] **B6**: honeypot `website` (diam-diam tidak disimpan) + `throttle:5,1` pada `POST /contact`. Bukti: 2 test baru di `FrontendRoutesTest`. *(Notifikasi ke `notification_email` ditunda, butuh keputusan Q6; tampilan flash/error ada di Phase 7.)*
- [x] **B7**: `config/cms.php` (`CMS_ADMIN_EMAILS`, `CMS_ADMIN_PASSWORD`); `User::canAccessPanel()` memakai allowlist, tanpa allowlist hanya `local`/`testing`; `DatabaseSeeder` tidak membuat admin tanpa password di non-lokal dan tidak membuat `test@example.com` di non-lokal. Bukti: `AdminPanelAccessTest`.
- [x] **B8**: tab "About Page" (SEO) ditambahkan di `ManagePageSettings` (mount + save). Bukti: `AdminPanelAccessTest`.
- [x] Test symlink dilonggarkan: bandingkan `realpath(public/storage)` dengan `realpath(storage/app/public)` (lolos untuk symlink maupun junction Windows).
- [x] Akar penyebab test flaky: `ExampleTest` tanpa `RefreshDatabase` menulis `global_settings` ke DB permanen dan membuat `SchemaAlignmentTest` melihat 2 baris. Ditambah `uses(RefreshDatabase::class)`.
Exit: `php artisan test --compact` → **57 passed, 0 failed, 346 assertions** (2 run berturut-turut); `pint` passed.

Catatan temuan baru:
- Seeder lain juga memakai path `/images/*` yang tidak ada di `public/` (`AboutCompanySeeder`: hero, partners, facilities, CEO; `CertificationSeeder`: `certs/*`; kemungkinan juga Work/Portfolio). `MediaUrl` mengembalikan `null` untuk file tak ada, jadi view di Phase 3–6 wajib punya fallback gambar.
- `composer install` mengubah `boost.json` lewat post-script; sudah di-revert (`git checkout -- boost.json`).
- Admin lokal: `.env` perlu `CMS_ADMIN_EMAILS` hanya jika ingin allowlist; kosong = semua user terbuka di local/testing. Produksi wajib mengisi `CMS_ADMIN_EMAILS` dan `CMS_ADMIN_PASSWORD`.

### Phase 2 — Shared Layout (Navbar & Footer) *(Complete)*
Depends on: Phase 1.
- [x] Footer bersama = footer lengkap dari `home.blade.php` (logo, deskripsi, banner partner, alamat, `phones[]` ber-`tel_url`, `primary_email` + `corporate_email`, `footer_legal_links[]` dari CMS, copyright), semua link pakai `url()` (`/#landing`, `/#services`, `/#works`, `/#contact`) sehingga berfungsi di semua halaman. Navbar bersama: Landing → `/#landing`, null-safe `?->`, fallback brand netral "PT. Apex Industrial Engineering".
- [x] Blok inline header (±84 baris) dan footer (±100 baris) di `home.blade.php` diganti `@include('layouts.navbar')` / `@include('layouts.footer')`. Scrollspy home disesuaikan: selector `a.nav-link[href$="#…"]` (href kini absolut dan dropdown "Services" tidak boleh ikut tersorot).
- [x] PRD §12: #2 brand fallback, #3 link "Portofolio Manpower" → `/portofolio` (navbar dropdown dan footer), #6 sublabel "12 Klaster" → "{N} Layanan dalam {M} Klaster" dihitung lewat view composer `layouts.navbar` di `AppServiceProvider`. #14 legal links dari CMS; `#` hanya fallback saat CMS kosong.
- [x] Bukti: `SharedLayoutGlobalSettingsTest` (3 test baru: link portofolio + legal links CMS, ringkasan layanan dinamis, landing memakai layout bersama tanpa "Trissan"). `php artisan test --compact` → **60/60, 358 assertions**; pint passed; `git diff --check` bersih. Cek browser (artisan serve + data seed): navbar/footer landing tampil benar, scrollspy menandai "Contact" saat section contact terlihat, tidak ada error JS.
Exit: terpenuhi.

Catatan / temuan untuk phase berikutnya:
- Link `social_media[]` **tidak** dimasukkan ke footer (footer tidak punya slot untuk itu); dikerjakan di section Contact Landing (Phase 7).
- Seed legal links menunjuk `/privacy-policy`, `/terms-of-service`, `/quality-safety-policy`, `/compliance`, tetapi **route/halaman itu tidak ada** (404). Perlu keputusan (Q8).
- Landing masih meminta gambar seed yang tidak ada: hero `GET /storage//images/hero-{1..3}.jpg` → **403** (path `/images/...` diberi prefix `storage/` di logika hero, ada `//`), `/images/about-teaser.jpg` dan `/images/work-{1..6}.jpg` → 404. Hero/about teaser/featured works harus dipindah ke `MediaUrl` dengan fallback (Phase 3 & 7).
- Navbar belum punya menu mobile (`hidden md:flex` tanpa hamburger); sudah begitu sebelumnya, di luar scope.

### Phase 3 — Works Page *(Complete)*
Depends on: Phase 1 (B3).
- [x] Tab filter dari `Service` (urut `sort_order`; `service_icon`, `service_title`, `slug`); tab "Semua" memakai jumlah work dari DB. Bug lama diperbaiki: label kategori aktif sebelumnya terisi nama ikon.
- [x] Card dari `Work`: badge atas/bawah, serial, sektor, judul, deskripsi singkat, hingga 3 `highlight_tags`; gambar dari `Work::gallery_urls` (accessor baru, `MediaUrl`) dengan placeholder ikon bila gambar tidak ada. Atribut `data-*` untuk JS: `slug`, `category`, `category-label`, `serial`, `sector`, `desc` (teks polos dari rich text), `material`, `tolerance`, `tolerance-value`, `batch`, `scale`, `date`, `date-label`, `gallery` (JSON), `standards` (JSON), `specs` (blob pencarian). Semua hotlink `lh3.googleusercontent.com` di view ini dihapus (0 tersisa).
- [x] Modal: tanggal produksi dari `production_date` ("Oktober 2024" dalam format Indonesia), galeri dirender dari `gallery_images` (maks 3, thumbnail klik untuk ganti gambar besar, disembunyikan bila hanya 1 gambar), semua `quality_standards` dirender sebagai chip (menggantikan 2 chip statis `AS9100D Audited` dan `100% CMM Inspected`, sesuai PRD §6 min 1 maks 3), blok yang kosong disembunyikan, teks sampel statis dikosongkan.
- [x] JS ditulis ulang (ID elemen dipertahankan): paginasi nyata di browser (9 / 18 / Semua, tombol halaman dinamis; sebelumnya hanya dekorasi), sort numerik (`data-tolerance-value` naik, `data-scale` turun, `data-date` terbaru; nilai kosong selalu di akhir; sebelumnya `parseFloat` pada teks menghasilkan NaN), search, reset filter, deep link `?category={service slug}` dan `#{work_slug}` (fallback ke slug dari judul untuk link lama).
- [x] `WorkResource`: validasi maksimal 6 work Featured (`MAX_FEATURED_WORKS`), work yang sudah featured tetap bisa disimpan.
- [x] Bukti: `WorksPageRenderingTest` (11 test: tab per service, card per work, hitungan, atribut data-*, gambar/galeri/placeholder, escaping XSS, state kosong, deskripsi polos, tanggal Indonesia, validasi featured ×3). `php artisan test --compact` → **71/71, 446 assertions**; pint passed; `git diff --check` bersih. Cek browser (data seed + 6 work tambahan): 15 card, paginasi 9+6 dan "Semua", sort skala/toleransi benar, `?category=digital-automation#robotic-gantries` memilih tab, menyaring 1 card dan membuka modal dengan data benar, galeri 3 gambar berfungsi.
Exit: terpenuhi.

Catatan:
- `data-inspection` dan `data-application` tidak lagi dirender (tidak ada di CMS, PRD §6 "tidak dimasukkan"); modal tidak menampilkannya sebelumnya.
- Landing (`home.blade.php`) belum memakai `Work::image_url`; masih memakai logika gambar sendiri (Phase 7).
- JS tidak punya test otomatis (proyek tidak memasang plugin browser); diverifikasi manual di browser.
- Data seed menunjuk `/images/work-*.jpg` yang tidak ada, jadi di lingkungan seed semua card memakai placeholder sampai gambar diunggah lewat Filament.

### Phase 4 — License Page *(Complete)*
Depends on: Phase 1.
- [x] Tab kategori dari `CertificationCategory`; tabel dari `CorporateAccreditation` (6/halaman, filter kategori + pencarian di browser; selektor `.cert-card[data-category]` yang salah diganti `.cert-row`); validitas "24 Mar 2027" (format Indonesia).
- [x] Modal verifikasi dari `data-*` baris/kartu: judul (`modal_title`, fallback nama standar + deskripsi), ID, lembaga, validitas, `scope_detail`, dan **gambar sertifikat dirender** (sebelumnya tidak ada `<img>`). Tombol simulasi "Check SHA" dihapus (Q5), diganti tombol Download nyata hanya bila PDF ada.
- [x] Kartu produk dari `ProductCertification`: tag/ikon kategori, "Unggulan", "Valid s/d {tahun}", nama, subtitle, model, ikon kartu, hingga 4 `spec_rows`, lingkup, anchor `cert-{slug}` (tidak digandakan bila slug sudah berawalan `cert-`), link "Lihat Produk" ke `/works#{work_slug}`, Download hanya bila `certificate_pdf` ada.
- [x] Handler yatim (`handleInquirySubmit`, `simulateDownload`) dan script `lucide`/`filterCategory` sisa Landing dihapus. Judul fallback `<title>` diperbaiki (PRD §12 #12). Seeder: ikon kartu sertifikasi diganti nama Material Symbol (sebelumnya nama Lucide seperti `plane`, `flame`).
- [x] Bukti: `LicensePageRenderingTest` (11 test). Cek browser: 6 baris, paginasi, filter kategori (6 → 1), pencarian, modal baris dan kartu berfungsi.

### Phase 5 — Portfolio Page *(Complete)*
Depends on: Phase 1 (B5).
- [x] Tab kategori dari `PortfolioCategory`, "Semua Hasil Kerja (N)" dihitung otomatis, dan **tab kini benar-benar menyaring grid** (PRD §12 #10).
- [x] Grid deliverables dari `PortfolioItem` (12/halaman, paginasi di browser) dengan placeholder bila gambar tidak ada; modal menampilkan gambar, judul, dan **deskripsi** (`#modalDesc`; bug `modalSubtitle` hilang).
- [x] Direktori personil dari `Personnel` + `SpecialtyCluster` + `Skill` (maks 3 skill, label Aktif/Non-Aktif, `registration_id`, `technical_focus`), pencarian, sort (ID registrasi / kualifikasi / klaster), 9 per halaman; jumlah di judul dan badge dihitung dari DB. `PortfolioItem::image_url` accessor.
- [x] Bukti: `PortfolioPageRenderingTest` (8 test). Cek browser: tab menyaring (2 item), modal, pencarian personil, sort klaster.

### Phase 6 — About Page *(Complete)*
Depends on: Phase 2.
- [x] Hero (badge, judul, deskripsi rich text → paragraf polos, gambar + placeholder, overlay pabrik, tombol unduh profil hanya bila file ada, banner partner) dari `AboutCompany`; 4 stat dari `GlobalSetting::company_stats` (`label_about`).
- [x] Kartu Visi/Misi/Nilai dari `vision_mission_values` per `card_type` (ikon SVG dipertahankan per tipe), fasilitas (urut `sort_order`, gambar + placeholder, 2 spec tag), milestone (urut, `is_current` → badge biru + node hijau, maks 2 highlight, selang-seling kiri/kanan), struktur organisasi (CEO, GM, maks 4 divisi, maks 3 tim, tag revisi) dari CMS.
- [x] Bukti: `AboutPageRenderingTest` (9 test, termasuk record kosong dan XSS). Cek browser: semua section tampil dari DB.
- Catatan teknis: `@php(...)` inline di Blade menelan blok `@php ... @endphp` berikutnya jika ada banyak `@php` pada file yang sama; gunakan bentuk blok.

### Phase 7 — Landing Remaining + Contact Form *(Complete)*
Depends on: Phase 2.
- [x] Section #contact dari Global Settings: telepon (`tel_url` dari CMS, selisih digit hotline PRD §12 #1 hilang), alamat, email utama + korporat, zona fasilitas, tombol Maps dan iframe (**hanya `https://`**, selain itu memakai fallback default), overlay judul/koordinat, kartu social media (urut `sort_order`, ikon LinkedIn/Instagram/YouTube, URL non-http(s) diganti `#`).
- [x] About Teaser: stat dari `company_stats` (`label_landing`), fallback `page_settings.stats` lalu default; eyebrow fallback turunan nama perusahaan, CTA fallback `/about` (sebelumnya `/abtus`).
- [x] Gambar landing: hero slide, About Teaser, dan featured works memakai `MediaUrl` (bug `/storage//images/...` 403 hilang; file yang tidak ada jatuh ke gambar fallback).
- [x] **Contact form modal**: tombol "CONTACT FORM" kini membuka `#contact-modal` sungguhan (form `POST contact.store`, `@csrf`, honeypot, dropdown layanan, pesan error per field, old input, pesan sukses; modal terbuka otomatis setelah submit gagal/sukses dan via `#contact-modal`). Controller tidak lagi mengirim `$corporateAccreditations` yang tidak dipakai.
- [x] Bukti: `HomePageRenderingTest` (11 test). Cek browser: modal buka/tutup, field lengkap, 2 telepon, 3 kartu sosial, iframe dari CMS.

### Phase 8 — Cleanup, Performance, Hardening *(Complete)*
- [x] Query: tes `PageQueryEfficiencyTest` memastikan jumlah query tiap 5 halaman **tidak bertambah** saat data bertambah (tidak ada N+1). `LicensesController` tidak lagi eager-load akreditasi per kategori (tidak dipakai).
- [x] PRD §12: #1, #2, #3, #4, #5, #6, #9, #10, #11, #12, #13 (satu sumber `factory_address`), #14 (link legal footer tanpa URL kini teks biasa, bukan `href="#"`; seeder tidak lagi menunjuk halaman 404) terselesaikan. JS mati `selectCategory`/`filterCategory` di Landing dihapus.
- [ ] Belum / diputuskan tidak dikerjakan: #7 (bucket filter Landing: UI filter tidak ada lagi di Featured Works, kartu service sudah menaut ke `/works?category={slug}`), #8 (versi canonical Works vs Landing: satu sumber `works` tabel), #15 (kategori seed Work yang janggal: data, bukan kode; koreksi lewat Filament).
- [!] Hotlink `lh3.googleusercontent.com` masih ada hanya sebagai **gambar fallback** saat CMS kosong (home 5, footer 2, navbar 1; 0 di Works/License/Portfolio/About). Risiko diterima: ganti dengan aset lokal bila klien menyediakan.

### Phase 9 — Verification & Closeout *(Complete)*
- [x] `vendor/bin/pint --dirty --format agent` → passed.
- [x] `php artisan test --compact` → **116/116 lulus, 711 assertions**.
- [x] `git diff --check` bersih.
- [x] Cek browser (data seed) untuk 5 halaman: filter, sort, paginasi, modal, deep link, scrollspy, form kontak.
- [ ] `npm run build`: tidak diperlukan (halaman publik memakai Tailwind CDN, tidak ada `@vite` selain `welcome`); tidak dijalankan.
- [x] Dokumen ini diperbarui; `frontend-integration.md` dinyatakan **superseded** oleh dokumen ini (tidak diedit).

## 6. Daftar Hardcoded untuk Verifikasi Akhir (grep gate)
Setelah tiap phase, pencarian berikut harus kosong di view terkait (kecuali fallback eksplisit):
- Works: `Turbine Gearbox`, `data-serial=`, `data-category="`, `Oktober 2024`, `lh3.googleusercontent`
- License: `QMS-ID-980144`, `cert-turbine-gearbox`, `btn-cat-`, `openVerificationModal('`
- Portfolio: `REG: MP-`, `Semua Hasil Kerja (40)`
- About: `Pendirian PT Apex`, `PLANT 01`, `REV-2024.10`
- Landing/Layout: `+62 (21) 5508-8990`, `tel:+62215508899`, `PT. Trissan`, `company_logo`, `contact_address`

## 7. File yang Akan Terdampak
- Controller: `HomeController`, `WorksController`, `PortfolioController`, `AboutController`, `LicensesController`, `ContactController`
- View: `home`, `works`, `portofolio`, `license`, `abtus`, `layouts/navbar`, `layouts/footer` (+ partial/komponen baru bila perlu)
- Model: `Work`, `Service`, `GlobalSetting` (accessor/helper gambar), `User`
- Seeder: `GlobalSettingSeeder`, `DatabaseSeeder` (+ seeder gambar/aset)
- Filament: `ManageGlobalSettings`, `ManagePageSettings` (B8), `WorkResource` (max featured)
- Routes: `routes/web.php` (throttle contact)
- Test: `FrontendRoutesTest`, `PageSettingsRenderingTest`, baru: `WorksPageRenderingTest`, `LicensePageRenderingTest`, `PortfolioPageRenderingTest`, `AboutPageRenderingTest`, `LayoutGlobalSettingsTest`, `ContactFormTest`

## 8. Risks
- **Test tidak bisa jalan** tanpa `vendor/`, `.env`, dan MySQL (B11): seluruh klaim "selesai" tertahan sampai Phase 0 beres.
- View sangat besar dengan baris panjang (minified) → edit rawan salah; pecah ke partial/komponen kecil per section dan commit per halaman.
- JS filter/sort/modal bergantung pada atribut `data-*`; render DB harus mempertahankan nama atribut atau JS ikut disesuaikan (uji manual di browser).
- Data seed ≠ data produksi: Works menunjukkan 9 vs Landing 6 versi berbeda (PRD §12 #8, Q2).
- Upload Filament di `storage/app/public` butuh `storage:link` di tiap environment.

## 9. Pertanyaan Terbuka (dibutuhkan keputusan sebelum fase terkait)
| # | Pertanyaan | Rekomendasi | Phase |
|---|---|---|---|
| Q1 | DB test: MySQL (`laravel_test`) atau sqlite in-memory? | sqlite in-memory bila `pdo_sqlite` bisa diaktifkan; tes lebih cepat & portabel | 0 |
| Q2 | Versi canonical data Works vs Landing (PRD §13.4)? | Satu sumber (Works), Landing hanya filter `is_featured` | 3 |
| Q3 | Label Company Stats Landing vs About (PRD §13.3)? | Pakai dua label (`label_landing`, `label_about`) yang sudah ada di Filament | 6–7 |
| Q4 | Sumber SEO About: `page_settings(about)` atau `about_company`? | Tambah tab `about` di `ManagePageSettings` khusus SEO | 1 |
| Q5 | Tombol "Check SHA" & "Download" License: dihapus atau diganti aksi nyata? | Download = `certificate_pdf`; hapus Check SHA | 4 |
| Q6 | Contact form dibuat sekarang (PRD §13.5)? | Ya, route & koleksi sudah ada | 7 |
| Q7 | Kredensial admin & akses panel di produksi? | Allowlist email + password dari env (selesai Phase 1) | 1 |
| Q8 | Halaman legal footer (`/privacy-policy`, dst.) belum ada: dibuat, diarahkan ke `#`, atau disembunyikan sampai diisi? | Sembunyikan link yang URL-nya kosong; isi URL nyata dari CMS | 2/8 |

## 10. Log Progres
*(diisi saat eksekusi: tanggal, phase, perubahan file, hasil perintah dan exit code)*

- 2026-10-26 — Plan dibuat dari analisis statis. Belum ada kode diubah. Test belum dijalankan.
- 2026-10-26 — Phase 0: `composer install` exit 0; `.env` dibuat; `storage:link` (junction). `php artisan test --compact` → 27/28 pass, 239 assertions; gagal: test symlink (is_link vs junction Windows). B1 terkonfirmasi lewat `Schema::hasColumn`. Tidak ada file kode diubah.
- 2026-10-26 — Phase 1 selesai. File: `app/Support/MediaUrl.php`, `config/cms.php` (baru); `GlobalSetting`, `User`, 5 controller, `routes/web.php`, `DatabaseSeeder`, `GlobalSettingSeeder`, `ManagePageSettings`, `layouts/navbar|footer`, `home.blade.php`, `.env.example`; test baru `MediaUrlTest`, `SharedLayoutGlobalSettingsTest`, `PublicPageDataTest`, `AdminPanelAccessTest` + perubahan `FrontendRoutesTest`, `PageSettingsRenderingTest`, `ExampleTest`. `php artisan test --compact` → 57/57 (346 assertions) ×2; pint passed.
- 2026-10-26 — Phase 2 selesai. File: `layouts/navbar.blade.php`, `layouts/footer.blade.php`, `home.blade.php` (include + selector scrollspy), `AppServiceProvider` (composer), `SharedLayoutGlobalSettingsTest`. Test 60/60 (358 assertions), pint passed, cek browser OK.
- 2026-10-26 — Phase 3 selesai. File: `works.blade.php` (ditulis ulang bagian tab/grid/modal/pagination/JS), `Work` (accessor `image_url`, `gallery_urls`, `production_date_label`, `description_plain`), `WorkResource` (maks 6 featured), `WorksPageRenderingTest`. Test 71/71 (446 assertions), pint passed, cek browser OK.
- 2026-10-26 — Phase 4–9 selesai. Perubahan utama: `license`, `portofolio`, `abtus`, `home` (kontak, social, maps, modal form, gambar), `layouts/footer` (legal link), model `CorporateAccreditation`, `ProductCertification`, `PortfolioItem`, `AboutCompany` (accessor media/label), `HomeController`, `LicensesController`, `GlobalSettingSeeder`, `CertificationSeeder`; test baru `LicensePageRenderingTest`, `PortfolioPageRenderingTest`, `AboutPageRenderingTest`, `HomePageRenderingTest`, `PageQueryEfficiencyTest`. `php artisan test --compact` → 116/116 (711 assertions); pint passed; `git diff --check` bersih.

## 11. Sisa / Keputusan yang Masih Terbuka
| # | Item | Status |
|---|---|---|
| Q6 | Notifikasi email ke `notification_email` saat ada pesan kontak baru | Belum dibuat (butuh konfigurasi mail). Data tetap masuk ke koleksi Contact Submissions di Filament. |
| Q8 | Halaman legal (Privacy Policy, dst.) | Link legal tampil sebagai teks sampai admin mengisi URL di Global Settings. |
| - | Gambar seed | **Selesai**: `SeedMedia` membuat gambar SVG dan PDF contoh di `storage/app/public/seed/` saat seeding (butuh `php artisan storage:link`), sehingga semua halaman langsung terisi. Ganti lewat Filament saat mengisi konten asli. |
| - | `copyright_text` | Tahun masih tertanam di teks (PRD §3: sebaiknya otomatis). |
| - | `service_icon_svg` | Dirender mentah (`{!! !!}`); aman selama hanya admin tepercaya yang mengisinya. |
| - | Menu mobile navbar | Belum ada (sudah begitu sebelumnya). |
| - | JS interaktif | Tidak ada test otomatis (plugin browser tidak terpasang); diverifikasi manual. |
- 2026-10-26 — Seeder media: `database/seeders/SeedMedia.php` (SVG + PDF placeholder pada disk `public`), semua seeder memakai `SeedMedia::image()/pdf()`. Database dev dipisah dari database test: `.env` kini `laravel_trissan` (dibuat dan di-seed), `laravel_test` hanya untuk phpunit. `php artisan test --compact` → 117/117 (728 assertions); data dev tidak terhapus oleh test.
- 2026-10-26 — Audit responsif (keluhan "terlalu zoom / tidak enak di HP"). Dibandingkan dengan versi HEAD lewat route sementara (sudah dihapus): tidak ada overflow horizontal dan ukuran heading identik, jadi masalah berasal dari desain lama yang makin terlihat setelah data terisi. Diperbaiki: navbar bersama (tinggi 64px di HP, tanpa menu → **menu hamburger** aksesibel, breakpoint menu desktop dinaikkan md → lg, brand tidak wrap di lebar 1024-1280), hero Landing (tinggi tetap 620px memotong konten di HP → `min-h`, padding bawah untuk overlap kartu layanan, heading 3xl di HP), section Landing py-12 di HP, bar filter Works tidak lagi sticky di HP (menutup separuh layar), heading Works/Portfolio lebih kecil di HP, chip "Active" nyasar di footer dihapus, baris "Fokus Teknis" personil tidak lagi menumpuk, gambar seed hero tanpa teks watermark. Test 123/123.
