# Unified CMS Architecture: Apex Industrial Engineering (v2.1)

*Versi 2.1 (v2 + batas min/max repeater) · divalidasi terhadap 5 file HTML asli (landing, about us, portfolio, works, license & certification) · 16 Oktober 2026*

## 1. Perubahan dari v1

| Area | Perubahan |
|---|---|
| Jumlah item | Slider Services = **12** (bukan 8), Featured Works = **6** (bukan 5), Facilities = 3, Milestones = 5, Personnel tampil 9 dari 15, Portfolio 12 dari 40 |
| Services | Slider = 12 service = total isi direktori, jadi `Show in Slider` dihapus. Tambah `Short Title` karena judul slider berbeda dari judul direktori/tab Works |
| Landing | Tambah: hero carousel (3 slide), Trust Card di hero, About teaser (section `#about`), brand name/tagline, footer (deskripsi, partner image, legal links, copyright) |
| Global | Hotline jadi repeater (2 nomor, URL `tel:` terpisah). Social Media tambah `Icon`. Tambah `Company Stats` bersama (dipakai Landing dan About) |
| Page Settings | Works dan Portfolio **punya hero + stat cards** (v1 salah menganggap hardcode). Licenses punya hero badge card + 4 stat. Tambah SEO per halaman |
| About | Tambah Vision, Mission & Values (belum ada sama sekali di v1), hero badge, overlay gambar, partner image, revision tag struktur organisasi |
| Works | Hapus konvensi slot tag (kartu menampilkan 2-3 tag apa adanya). Tambah field angka untuk sorting (`Tolerance Value`, `Scale Value`), `Production Date` wajib, gallery menggantikan thumbnail statis. Satu set field untuk Landing dan Works |
| Licenses | Tabel korporat punya filter kategori, jadi tambah master `Certification Categories`. `Scope / Detail` jadi wajib. Tambah Corporate Highlight Cards. Product Certification: spec rows dengan label bervariasi, `Certificate Number`, `Issuing Body`, `Validity Date` |
| Portfolio | Modal hanya menampilkan 1 gambar, judul, dan deskripsi. `Additional Images`, `Tags / Specs`, `Production Date` dihapus. Specialty Cluster personnel jadi master data |
| Contact form | Form belum ada di HTML (tombol mengarah ke `#contact-modal` yang tidak ada). Koleksi Contact Submissions ditandai opsional |

## 2. Peta Relasi

| Koleksi | Tipe | Tampil di | Relasi |
|---|---|---|---|
| Global Settings | Singleton | Navbar, Footer, Contact, Social, Maps | Standalone |
| Page Settings | Singleton per halaman | Hero & SEO tiap halaman | Standalone |
| About Company | Singleton + repeater | About Us | Standalone |
| Service Clusters | Collection (4) | Capability Directory Landing | 1 Cluster → banyak Services |
| Services | Collection (12) | Slider & Directory Landing, tab filter Works | 1 Service → banyak Works |
| Works | Collection (9) | Works Page, Featured Landing (6) | Wajib 1 Service; 1 Work → banyak Product Certifications |
| Certification Categories | Collection (4) | Filter tabel korporat Licenses | 1 Category → banyak Corporate Accreditations |
| Corporate Accreditations | Collection (6) | Tabel Licenses | N → 1 Category |
| Product Certifications | Collection (4) | Card Licenses | N → 1 Work (opsional) |
| Portfolio Categories | Collection (4) | Filter Portfolio | 1 Category → banyak Portfolio Items |
| Portfolio Items | Collection (40) | Grid Portfolio | Standalone dari Works |
| Specialty Clusters | Collection (4) | Personnel card, sorting | 1 Cluster → banyak Personnel |
| Skills (Master) | Collection | Tag lisensi di Personnel card | Banyak-ke-banyak dengan Personnel |
| Personnel | Collection (15) | Direktori Portfolio | N → 1 Specialty Cluster; N ↔ N Skills (maks 3 tampil) |
| Contact Submissions | Collection (opsional) | Form kontak Landing (belum ada) | Opsional ke Services |

## 3. Konvensi Umum

- `Sort Order` (Number) di semua koleksi yang tampil sebagai list. Penomoran `01.`, `02.`, `CLUSTER 01` di-generate dari urutan.
- Filter memakai satu sumber: `Service Slug`. Tidak ada lagi kategori terpisah di tiap halaman.
- Icon = nama Material Symbol (Text). Pengecualian: icon slider Landing berupa SVG (field `Service Icon SVG`).
- Tanggal disimpan sebagai Date, ditampilkan dengan format Indonesia (`24 Nov 2026`, `Oktober 2024`, `Valid s/d 2028`).
- Nilai turunan (tidak diinput manual): jumlah item ("5 Layanan", "40 hasil kerja", "15 personil"), `Service Link URL`, tag "Valid s/d {tahun}", tahun copyright.
- Komponen **Stat Item** dipakai berulang di semua hero: `Icon` (opsional), `Value`, `Label`, `Caption` (opsional).
- Pagination, search, dan sorting adalah logika frontend, tidak masuk CMS.
- Batas min/max semua repeater ada di bagian "Batas Min / Max" di bawah. Batas diturunkan dari konfigurasi HTML, bukan dari perubahan layout.

### Batas Min / Max

Cara membaca kolom **Dasar**:

- **Grid**: jumlah kolom atau slot terkunci di HTML. Di atas Max, item turun ke baris baru atau merusak layout.
- **Komposisi**: jumlah baris/chip tetap di dalam satu card atau modal.
- **List**: carousel, slider, tab scroll, atau paginasi. Tidak ada batas teknis di HTML, jadi `Max` = Bebas dan validasi CMS hanya `Min`.

**Global Settings**

| Repeater | Min | Max | Isi HTML saat ini | Dasar |
|---|---|---|---|---|
| Company Stats | 4 | 4 | 4 | Grid 4 kolom |
| Phones | 1 | 2 | 2 | Komposisi (baris teks) |
| Social Media | 0 | Bebas | 3 | List (stack vertikal) |
| Footer Legal Links | 0 | Bebas | 4 | List (flex-wrap) |

**Page Settings**

| Repeater | Min | Max | Isi HTML saat ini | Dasar |
|---|---|---|---|---|
| Landing: Hero Slides | 1 | Bebas | 3 | List (carousel) |
| Landing: Trust Card Clients | 0 | 4 | 4 | Grid 2 × 2 |
| Works: Stats | 4 | 4 | 4 | Grid 4 kolom |
| Portfolio: Stats | 5 | 5 | 5 | Grid 5 kolom (desktop) |
| Licenses: Stats | 4 | 4 | 4 | Grid 4 kolom |
| Licenses: Corporate Highlight Cards | 4 | 4 | 4 | Grid 4 kolom |

**About Company**

| Repeater | Min | Max | Isi HTML saat ini | Dasar |
|---|---|---|---|---|
| Vision, Mission & Values (card) | 3 | 3 | 3 | Grid 3 kolom, satu per tipe |
| Mission Points | 1 | 3 | 3 | Komposisi |
| Values Items | 1 | 3 | 3 | Komposisi |
| Facilities | 1 | Bebas | 3 | List (carousel di mobile, grid 3 kolom di desktop; kelipatan 3 dianjurkan) |
| Milestones | 1 | Bebas | 5 | List (timeline) |
| Milestone Highlights | 0 | 2 | 2 | Komposisi (satu kotak, 2 poin) |
| Division Heads | 1 | 4 | 4 | Grid 4 kolom + garis konektor |
| Division Teams / Roles | 1 | 3 | 3 | Komposisi (3 baris per card) |

**Services**

| Repeater | Min | Max | Isi HTML saat ini | Dasar |
|---|---|---|---|---|
| Service Clusters | 1 | Bebas | 4 | List (grid 2 kolom, jumlah genap dianjurkan) |
| Services (total, slider) | 1 | Bebas | 12 | List (slider scroll) |
| Services per Cluster | 1 | Bebas | 5 / 3 / 2 / 2 | List |

**Works**

| Repeater | Min | Max | Isi HTML saat ini | Dasar |
|---|---|---|---|---|
| Works (total) | 0 | Bebas | 9 | List (paginasi) |
| Works dengan `Is Featured` | 3 | 6 | 6 | Grid 3 kolom × 2 baris |
| Gallery Images | 1 | 3 | 3 (1 besar + 2 thumbnail) | Komposisi (modal) |
| Highlight Tags | 2 | 3 | 2 di Works card (cards 1-6), 3 di Landing dan cards 7-9 | Komposisi |
| Quality Standards | 1 | 3 | 3 chip (1 dinamis + 2 statis) | Komposisi (modal) |

**Certifications**

| Repeater | Min | Max | Isi HTML saat ini | Dasar |
|---|---|---|---|---|
| Certification Categories | 1 | Bebas | 4 | List (tab scroll horizontal) |
| Corporate Accreditations | 1 | Bebas | 6 | List (tabel + paginasi) |
| Product Certifications | 1 | Bebas | 4 | List (grid 2 kolom, jumlah genap dianjurkan) |
| Spec Rows (per Product Certification) | 1 | 4 | 4 | Komposisi (4 baris per card) |

**Portfolio & Personnel**

| Repeater | Min | Max | Isi HTML saat ini | Dasar |
|---|---|---|---|---|
| Portfolio Categories | 1 | Bebas | 4 | List (tab scroll horizontal) |
| Portfolio Items | 0 | Bebas | 40 | List (paginasi) |
| Specialty Clusters | 1 | Bebas | 4 | List (master data) |
| Personnel | 0 | Bebas | 15 | List (paginasi) |
| Assigned Skills (per Personnel) | 1 | 3 | 3 | Komposisi (3 tag per card) |

**Jumlah card per tampilan** (konfigurasi frontend, bukan field CMS)

| Halaman | Komponen | Tampil | Catatan |
|---|---|---|---|
| Landing | Featured Works | 6 | Tanpa paginasi, tombol "Lihat Semua" menuju Works |
| Landing | Services Slider | Semua (scroll) | |
| Works | Grid project | 9 | Opsi 9 / 18 / Semua |
| Portfolio | Grid deliverables | 12 | Mobile hanya menampilkan 6 pertama |
| Portfolio | Personnel | 9 | Dari 15 |
| Licenses | Tabel korporat | 6 | Satu halaman |
| Licenses | Card produk | 4 | Satu halaman |
| About | Facilities | Semua | Carousel di mobile, grid 3 kolom di desktop |

## 4. Singleton: Global & Page Content

### 4.1 Global Settings

**Brand**

| Field | Tipe | Keterangan |
|---|---|---|
| Company Name | Text | Navbar dan footer saat ini `PT. Trissan`, halaman lain `PT Apex Industrial Engineering` (lihat bagian 12) |
| Company Tagline | Text | `Industrial Engineering` |
| Navbar Logo Image | Image | Logo kiri atas |
| Footer Logo Image | Image | Logo kolom kiri footer (saat ini gambar yang sama dengan navbar) |
| Footer Description | Textarea | Paragraf di bawah logo footer |
| Footer Partner Image | Image | Banner "Our Partner" di footer |
| Copyright Text | Text | Tahun di-generate otomatis |
| Footer Legal Links | Repeater | `Label`, `URL` (Privacy Policy, Terms of Service, Quality & Safety Policy, ISO 9001 / AS9100D Compliance) |

**Company Stats** (Repeater, 4 Stat Item, dipakai Landing About teaser dan About hero)

| Value | Label (Landing) | Label (About) |
|---|---|---|
| 25+ | Tahun Pengalaman | Tahun Dedikasi |
| 99.98% | Presisi Toleransi | Toleransi Presisi |
| 150+ | Insinyur Ahli | Insinyur & Ahli |
| 500+ | Proyek Global | Proyek Selesai |

Satukan label agar satu field cukup, atau simpan dua label per stat.

**Contact Info** (Group)

| Field | Tipe | Keterangan |
|---|---|---|
| Phones | Repeater | `Display Text`, `Tel URL`, `Is WhatsApp` (Boolean). Saat ini 2 nomor, hanya nomor pertama punya link |
| Primary Email | Text | `contact@apex-engineering.co.id` |
| Corporate Email | Text | `corporate@apex-engineering.co.id` |

**Locations** (Group)

| Field | Tipe | Keterangan |
|---|---|---|
| Factory Address | Textarea | Satu sumber untuk Contact, Official Address Card, dan footer |
| Facility Zone | Text | `Heavy Industrial Zone` |
| Maps Button URL | Text | Link "Buka di Google Maps" |
| Maps Iframe Embed URL | Text | `src` iframe |
| Overlay Title | Text | `Apex Plant Facility 01` |
| Overlay Coordinate / Address | Text | `6°18'54.1"S 107°09'06.5"E • Jababeka V` |

**Social Media** (Repeater, saat ini 3)

| Field | Tipe | Keterangan |
|---|---|---|
| Platform Name | Text | LinkedIn, Instagram, YouTube |
| Platform Icon | Select | linkedin / instagram / youtube (SVG per platform) |
| Handle / Username | Text | Contoh `@apexindustrial.id` |
| Description | Textarea | Deskripsi di card |
| Profile URL | Text | Saat ini masih URL generik (`linkedin.com`, dst.) |
| Action Text | Text | Ikuti, Lihat, Tonton |
| Sort Order | Number | |

### 4.2 Page Settings

Field umum di semua halaman: `SEO Title` (Text), `SEO Description` (Textarea). Hero About dikelola di 4.3.

**Landing**

| Field | Tipe | Keterangan |
|---|---|---|
| Hero Slides | Repeater (3) | `Image`, `Alt Text`. Background hero berupa carousel |
| Hero Title | Text | |
| Hero Subtitle | Textarea | |
| Hero CTA Text / URL | Text | `Lihat Portofolio` → `#works` |
| Trust Card | Group | `Label` (TRUSTED BY 50+ LEADERS), `Rating` (★ 4.9/5.0), `Description`, `Clients` (Repeater of Text: SIEMENS, BOEING, CAT, KOMATSU), `Footer Left` (AS9100D & ISO 9001), `Footer Right` (Global Audited) |
| About Teaser | Group | `Image`, `Image Badge` (EST. 1998 • 25+ THN), `Eyebrow`, `Title`, `Paragraph 1`, `Paragraph 2`, `CTA Text`, `CTA URL`. Stat diambil dari Company Stats |

**Works**

| Field | Tipe | Keterangan |
|---|---|---|
| Hero Title | Text | `DAFTAR PRODUK & HASIL PENGERJAAN (WORKS)` |
| Hero Description | Textarea | |
| Stats | Repeater (4 Stat Item) | 120+ Total Deliveries, ±0.002mm Micron Gauge, 100% Quality Assurance, AS9100D Compliance (dengan icon dan caption) |

**Portfolio**

| Field | Tipe | Keterangan |
|---|---|---|
| Hero Title | Text | |
| Hero Description | Textarea | Menyebut "15 insinyur", sebaiknya angka diambil dari jumlah Personnel |
| Stats | Repeater (5 Stat Item) | Tenaga Ahli, Audit Kualifikasi, Disiplin Rekayasa, Lisensi Valid, Deliverables |
| Registry Revision Tag | Text | `APEX REGISTRY: REV-2024.Q2-ACTIVE` |

**Licenses**

| Field | Tipe | Keterangan |
|---|---|---|
| Hero Title | Text | |
| Hero Subtitle | Textarea | |
| Hero Badge Card | Group | `Eyebrow` (Integritas Mutu Pabrik), `Title` (Fasilitas Terakreditasi Penuh), `Caption` (Standar Internasional ISO & ASME) |
| Stats | Repeater (4 Stat Item) | 4+ Sertifikasi Produk, 6 QMS & Aerospace, 100% Bejana Tekan & Offshore, 7 K3 Lingkungan & Otomotif |
| Corporate Highlight Cards | Repeater (4) | `Icon`, `Title` (ISO 9001:2015), `Subtitle` (QMS Fabrikasi), `Caption` (TÜV SÜD · Audit 2025). Satu card bisa menggabungkan 2 standar (ISO 14001/45001) |

### 4.3 About Company

**Hero Profile** (Group)

| Field | Tipe | Keterangan |
|---|---|---|
| Hero Badge | Text | `Rekayasa Presisi & Fabrikasi Berstandar Global` |
| Hero Title | Text | |
| Hero Description | Rich Text | 2 paragraf |
| Hero Image | Image | |
| Image Overlay | Group | `Plant Badge` (PLANT 01 • JABABEKA V (12.000 m²)), `Caption Title`, `Caption Subtitle`, `Tolerance Chip` (Toleransi ±0.002mm) |
| Company Profile PDF | File | Tombol Unduh (saat ini `href="#"`) |
| Partners Image | Image | Banner partner di bawah stat |

Stat hero diambil dari Company Stats.

**Vision, Mission & Values** (Repeater, 3 card)

| Field | Tipe | Keterangan |
|---|---|---|
| Card Type | Select | vision / mission / values |
| Badge | Text | Visi 2030, Operasional, Standar Kualitas |
| Title | Text | Visi Perusahaan, Misi Strategis, Nilai Pokok Mutu |
| Body | Textarea / Repeater | Vision: teks. Mission: 3 poin (Repeater of Text). Values: 3 pasangan `Name` + `Value` (Precision First → Toleransi s/d 0.002 mm) |
| Footer Label / Value | Text | Target: Tier-1 Regional Leader, Metode: Continuous Kaizen, Integritas: 100% Traceable |

**Facilities** (Repeater, saat ini 3)

| Field | Tipe |
|---|---|
| Facility Image | Image |
| Facility Tag / Bay | Text |
| Facility Title | Text |
| Facility Description | Textarea |
| Spec Tag 1, Spec Tag 2 | Text |
| Sort Order | Number |

**Milestones** (Repeater, saat ini 5)

| Field | Tipe | Keterangan |
|---|---|---|
| Year / Period | Text | `1998`, `2024 - SEKARANG` |
| Milestone Title | Text | |
| Milestone Description | Textarea | |
| Highlights | Repeater of Text | 2 poin per milestone |
| Is Current | Boolean | Item terakhir memakai gaya badge biru dan node hijau |
| Sort Order | Number | |

**Struktur Organisasi**

| Level | Field | Tipe |
|---|---|---|
| Umum | Chart Revision Tag | Text (`REV-2024.10`) |
| CEO | Photo, Name, Position, Experience Tag | Image, Text |
| General Manager | Name, Position | Text |

**Division Heads** (Repeater, saat ini 4): `Division Tag`, `Head Name`, `Head Position`, `Teams / Roles` (Repeater of Text, 3 baris), `Sort Order`.

## 5. Koleksi Services

### 5.1 Service Clusters

| Field | Tipe | Keterangan |
|---|---|---|
| Cluster Name | Text | |
| Cluster Tag | Text | `CLUSTER 01` (bisa di-generate dari Sort Order) |
| Cluster Icon | Text | Material Symbol |
| Sort Order | Number | |

Turunan: `Service Count` ("5 Layanan").

### 5.2 Services

| Field | Tipe | Keterangan |
|---|---|---|
| Cluster | Relation | Ke Service Clusters |
| Service Title | Text | Judul resmi (direktori Landing dan tab Works) |
| Short Title | Text | Judul pendek di card slider |
| Service Slug | Text | Filter dan URL |
| Service Description | Textarea | Deskripsi di card slider |
| Service Icon | Text | Material Symbol (tab Works) |
| Service Icon SVG | Text / SVG | Icon slider Landing. Hapus jika slider dipindah ke Material Symbol |
| Sort Order | Number | Satu urutan untuk slider, tab Works, dan direktori |

Turunan: `Service Link URL` = `works.html?category={slug}`, `Item Filter Target` direktori = `Service Slug`.

### 5.3 Seed Data

Clusters:

| # | Cluster Name | Icon | Layanan |
|---|---|---|---|
| 01 | Mechanical & Automation Engineering | precision_manufacturing | 5 |
| 02 | Electrical Systems, PLC & Maintenance | bolt | 3 |
| 03 | Supply & Industrial Procurement | inventory_2 | 2 |
| 04 | Special Construction & Workforce Solutions | engineering | 2 |

Services (urutan = Sort Order):

| # | Short Title | Service Title | Slug | Cluster | Icon |
|---|---|---|---|---|---|
| 1 | Mechanical & Electrical | Mechanical and Electrical | mechanical-electrical | 01 | precision_manufacturing |
| 2 | Digital Automation & Controlling | Digital Automation and Controlling | digital-automation | 01 | smart_toy |
| 3 | Jigs & Fixtures | Jigs and Fixtures | jigs-fixtures | 01 | architecture |
| 4 | Unload Robot Chucking | Unload Robot Chucking | unload-robot-chucking | 01 | hardware |
| 5 | Material Handling | Material Handling | material-handling | 01 | forklift |
| 6 | Distribution Panel, PLC, HMI | Distribution Panel Assembly, Capacitor Bank, PLC, HMI | distribution-panel | 02 | developer_board |
| 7 | Electrical & Machine Maintenance | Electrical Installation and Machine Maintenance | electrical-installation | 02 | electrical_services |
| 8 | Electrical Equipment Supplier | Industrial Electrical Equipment Supplier | electrical-equipment-supplier | 03 | inventory_2 |
| 9 | Auto Grease System (Loader) | Automatically Grease System For Loader Part | grease-system | 02 | oil_barrel |
| 10 | Industrial Machinery & Parts | General Supplier Of Industrial Machinery, Spare Parts, Accessories, Equipment, Construction Material, And Others Goods | general-supplier | 03 | conveyor_belt |
| 11 | Other Special Construction | Other Special Construction (YTDL) | special-construction | 04 | construction |
| 12 | Workforce & Labor Solutions | Selection, Placement And Provision Activities domestic Part-time Labor | labor-provision | 04 | badge |

## 6. Koleksi Works

Sumber Works Page (card + modal) dan Featured Landing (6 pertama).

| Field | Tipe | Keterangan |
|---|---|---|
| Service Category | Relation (wajib) | Ke Services |
| Work Slug | Text | Deep link `works.html#turbine-gearbox-shafts` |
| Is Featured on Landing | Boolean | |
| Featured Sort Order | Number | |
| Work Image | Image | Gambar card |
| Gallery Images | Gallery (maks 3) | Modal: 1 besar + 2 thumbnail (saat ini thumbnail statis sama untuk semua work) |
| Top Badge | Text | Contoh `MECHANICAL` |
| Bottom Badge | Text | Contoh `0.002 mm` |
| Serial Number / Code | Text | `SERIES-AG-8840` |
| Client Sector | Text | `AEROSPACE T1` |
| Work Title | Text | |
| Short Description | Textarea | Card |
| Full Description | Textarea / Rich Text | Modal |
| Highlight Tags | Repeater (maks 3) | Landing menampilkan semuanya, Works card menampilkan 2-3 |
| Quality Standards | Repeater of Text | Modal, contoh `ASME Sec IX, API 6A Spec`. Dua chip `AS9100D Audited` dan `100% CMM Inspected` saat ini hardcode untuk semua work |

**Spesifikasi Detail / Modal** (Group)

| Field | Tipe | Keterangan |
|---|---|---|
| Material | Text | `Inconel 625 Clad + ASTM A694 F65` |
| Tolerance / Specs | Text | Teks tampilan, contoh `0.002mm Max Deviation` |
| Tolerance Value | Number (mm) | Untuk sort "Toleransi Tertinggi". HTML sekarang memakai `parseFloat` pada teks, hasilnya NaN untuk `NDT Zero Porosity (0.05mm Max)` |
| Batch / Scale | Text | Teks tampilan, contoh `1,200 Units / Cycle` |
| Scale Value | Number | Untuk sort "Skala Batch Terbesar" |
| Production Date | Date | Wajib. Untuk sort "Terbaru" dan modal (sekarang hardcode `Oktober 2024`) |

Tidak dimasukkan: `data-application` dan `data-inspection` ada di HTML Works tetapi tidak ditampilkan di modal.

## 7. Koleksi Certifications

### 7.1 Certification Categories (Master)

| Name | Slug |
|---|---|
| QMS Mutu | qms |
| Kedirgantaraan & Otomotif | aero |
| Bejana Tekan (ASME) | asme |
| K3 & Lingkungan | hse |

### 7.2 Corporate Accreditations

| Field | Tipe | Keterangan |
|---|---|---|
| Category | Relation | Ke Certification Categories (untuk filter tab) |
| Standard Name | Text | `ISO 9001:2015` |
| Standard Description | Text | |
| Accreditation Body | Text | `TÜV SÜD · Lloyd's` |
| Registration ID | Text | `QMS-ID-980144` |
| Validity Date | Date | |
| Scope / Detail | Text | Wajib, tampil di modal pada semua baris |
| Modal Title | Text | Opsional, default `Standard Name` + deskripsi singkat |
| Certificate Image | Image | Modal "Show Image" |
| Sort Order | Number | |

### 7.3 Product Certifications

| Field | Tipe | Keterangan |
|---|---|---|
| Related Work | Relation (opsional) | Ke Works. 4 dari 4 card punya padanan di Works |
| Certification Slug | Text | Anchor deep link, contoh `cert-rpm-buzzer` |
| Category Tag | Text | `Sensor & Manufaktur` |
| Category Icon | Text | Material Symbol (sensors, flight, dst.) |
| Is Featured | Boolean | Label "Unggulan" |
| Validity Date | Date | Tag "Valid s/d 2028" di-generate dari tahun |
| Product / System Name | Text | |
| Subtitle / Component Type | Text | |
| Model / Spec Identifier | Text | Satu baris penuh dengan prefix (`Model:`, `Spesifikasi:`, `Standar:`) |
| Card Icon | Text | Material Symbol |
| Certificate Number | Text | Dipakai di modal |
| Issuing Body | Text | Dipakai di modal |
| Spec Rows | Repeater (maks 4) | `Label` + `Value`. Label berbeda per card (Standar & Kepatuhan, Uji Material, Kelas Kebersihan, dst.) dan urutannya tidak seragam |
| Scope Description | Textarea | Paragraf Lingkup. Modal memakai field yang sama |
| Certificate Image | Image | Modal |
| Certificate PDF File | File | Tombol download |
| Sort Order | Number | |

## 8. Koleksi Portfolio

### 8.1 Portfolio Categories

Name (4: 5-Axis Precision Machining, Heavy Welding & Pressure Vessel, Otomasi & SCADA, NDT & Metrology Inspection), `Sort Order`. Jumlah "Semua Hasil Kerja (40)" dihitung otomatis.

### 8.2 Portfolio Items

| Field | Tipe | Keterangan |
|---|---|---|
| Portfolio Category | Relation | |
| Project Image | Image | Card dan modal |
| Industry Tag | Text | `Aerospace & Turbine` |
| Project Title | Text | |
| Project Description | Textarea | Modal |
| Sort Order | Number | |

## 9. Koleksi Personnel

**Specialty Clusters (Master)**: `Name` (Permesinan Presisi & CAM, Welding & Bejana Tekan, Otomasi & Mekatronika, QA/QC & K3 Fabrikasi), `Sort Order`. Jumlahnya menjadi stat "4 Klaster".

**Skills (Master)**: `Skill Name`.

**Personnel Directory**

| Field | Tipe | Keterangan |
|---|---|---|
| Registration ID | Text | `REG: MP-01` |
| Status | Boolean | Label Aktif / Non-Aktif |
| Job Role | Text | |
| Specialty Cluster | Relation | Ke Specialty Clusters |
| Assigned Skills | Relation (multi, maks 3) | Ke Skills |
| Technical Focus | Text | |
| Qualification Rank | Number | Opsional, hanya jika sort "Kualifikasi Tertinggi" dipertahankan |
| Sort Order | Number | Default urutan ID Registrasi |

## 10. Contact Submissions (Opsional)

Tombol "CONTACT FORM" di Landing mengarah ke `#contact-modal`, tetapi modal dan form tidak ada di HTML. Aktifkan koleksi ini setelah form dibuat.

| Field | Tipe |
|---|---|
| Name, Email, Phone (opsional), Message | Text / Textarea |
| Service Interest | Relation (opsional) ke Services |
| Submitted At | Datetime (otomatis) |
| Status | Select: Baru / Dibaca / Selesai |

Pengaturan terkait: `Notification Email` di Global Settings.

## 11. Peta Halaman ke Sumber Data

| Halaman | Section | Sumber data |
|---|---|---|
| Semua | Navbar, Footer | Global Settings |
| Landing | Hero, Trust Card, About Teaser | Page Settings (Landing) + Company Stats |
| Landing | Services Slider (12) | Services |
| Landing | Featured Works (6) | Works (Is Featured) |
| Landing | Capability Directory | Service Clusters + Services |
| Landing | Contact, Social, Maps | Global Settings |
| About Us | Hero, Vision/Mission/Values, Fasilitas, Milestone, Struktur Organisasi | About Company + Company Stats |
| Portfolio | Hero + Stats | Page Settings (Portfolio) |
| Portfolio | Filter + Grid | Portfolio Categories + Portfolio Items |
| Portfolio | Direktori Personil | Personnel + Specialty Clusters + Skills |
| Works | Hero + Stats | Page Settings (Works) |
| Works | Tab Filter | Services |
| Works | Card + Modal | Works |
| Licenses | Hero, Badge Card, Stats, Highlight Cards | Page Settings (Licenses) |
| Licenses | Tabel Korporat | Corporate Accreditations + Certification Categories |
| Licenses | Card Produk | Product Certifications |

## 12. Temuan di HTML yang Perlu Diperbaiki

1. **Nomor hotline tidak cocok.** Teks `+62 (21) 5508-8990`, tetapi `href="tel:+62215508899"` (kurang satu digit).
2. **Nama brand tidak konsisten.** Navbar, footer, dan About teaser memakai `PT. Trissan`, halaman lain memakai Apex.
3. **Link "Portofolio Manpower" menuju `works.html`** (navbar dan footer). Halaman Portfolio tidak terhubung dari navigasi manapun.
4. **CTA "About us" di Landing mengarah ke `#works`**, seharusnya `about-us.html`.
5. **Tombol "CONTACT FORM" menuju `#contact-modal` yang tidak ada.** Script `handleInquirySubmit` di Licenses juga yatim (section form-nya sudah dihapus).
6. **Sublabel navbar "12 Klaster"** padahal 4 klaster dan 12 layanan.
7. **Filter Landing memakai 3 bucket** (`machining`, `welding`, `projects-assembly`) yang tidak sama dengan 12 service. Cluster 03 memakai `all`. Tidak ada UI filter di Featured Works. Ganti ke `Service Slug` dan link ke Works.
8. **Data work yang sama berbeda di Landing dan Works** (serial number, sector, badge, tag, deskripsi). Contoh: Robotic Gantries `GANTRY-AUTO-V3` vs `MECHA-SYS-X40`. CMS memakai satu sumber, jadi pilih versi yang dipakai.
9. **Works modal:** tanggal produksi hardcode `Oktober 2024`, thumbnail statis, sort toleransi memakai `parseFloat` pada teks.
10. **Portfolio modal:** deskripsi tidak tampil (JS mengisi `modalSubtitle` yang tidak ada, elemennya `modalDesc`). Tab filter tidak terhubung ke kartu.
11. **Licenses:** filter kategori menarget `.cert-card[data-category]` yang tidak ada di tabel. Modal tidak memuat `<img>` sertifikat. Tombol "Check SHA" dan "Download" hanya simulasi (`alert` / toast).
12. **Tag `<title>`:** Licenses memakai judul Landing, Works dan Portfolio tidak punya `<title>`.
13. **Alamat berbeda:** Contact tanpa "Jawa Barat", Maps dan footer dengan "Jawa Barat".
14. **Placeholder:** URL sosial media generik, tombol PDF `href="#"`, legal links footer `href="#"`.
15. **Kategori work yang tampak tidak cocok:** `Cryogenic Gas Storage Shells` berada di Automatically Grease System, `5-Axis Planetary Carrier Gears` di General Supplier.

## 13. Keputusan Terbuka

1. **Judul section** (badge, H2, deskripsi di tiap section) saat ini hardcode di semua halaman. Tambahkan ke Page Settings hanya jika klien perlu mengeditnya.
2. **Icon slider Landing:** pertahankan SVG per service, atau pindah ke Material Symbol agar `Service Icon SVG` bisa dihapus.
3. **Label Company Stats** yang sedikit berbeda antara Landing dan About: satukan atau simpan dua label.
4. **Versi canonical Works vs Landing** (temuan 8): pilih satu, atau tambahkan field khusus Landing jika memang ingin berbeda.
5. **Contact form:** diputuskan dibuat atau tidak (menentukan koleksi Contact Submissions).
6. **Qualification Rank** personnel: dipakai hanya jika sort "Kualifikasi Tertinggi" dipertahankan.
