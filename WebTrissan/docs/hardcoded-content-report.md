# Laporan Konten Hardcoded & Checklist Sebelum Hosting

Dibuat 28 Oktober 2026 dari pemindaian seluruh view (`resources/views`), seeder, konfigurasi, dan `.env`.
Nomor baris mengacu pada kondisi kode saat laporan dibuat, jadi bisa bergeser kalau file diedit. Cari teksnya dengan pencarian di editor bila baris tidak cocok.

## Status keputusan (28 Oktober 2026)

Keputusan dari checklist sudah diterapkan di kode:

- **Jadikan CMS (19 baris):** badge, judul, dan deskripsi section kini field di **Page Settings** (disimpan di `page_settings.extra_data`), plus pesan sukses form kontak. Seeder mengisinya dengan teks lama.
- **Hapus:** teks bawaan/cadangan dihapus dari semua view, termasuk footer, navbar, gambar cadangan, URL peta bawaan, dan angka statistik cadangan. Bar bawah License dihapus. Label footer "Portofolio Manpower" menjadi "Portofolio".
- **Biarkan:** tidak diubah (judul/deskripsi cadangan hero Works dan License tetap).
- **Aturan field kosong:** teks tidak tampil, tetapi elemen dan jarak tetap ada seperti saat berisi. Badge dan tombol memakai `invisible`, teks biasa memakai spasi tak terputus. Kartu "Trusted by" disembunyikan seluruhnya bila semua isinya kosong.

Tabel di bawah mencatat kondisi sebelum keputusan itu diterapkan.

## Cara membaca laporan

| Kode | Arti | Cara mengubah |
|---|---|---|
| **A** | Teks **hardcoded di Blade**: selalu tampil, tidak bisa diubah lewat CMS | Edit file view (kolom Lokasi), atau minta dijadikan field CMS |
| **B** | **Fallback default** di kode: hanya tampil kalau field CMS kosong | Isi field di CMS; hapus fallback di kode kalau tidak mau ada cadangan |
| **C** | **Data contoh dari seeder**: sudah ada di database dan bisa diedit di CMS, tapi isinya karangan | Ganti lewat Filament sebelum hosting |
| **D** | **Konfigurasi server / `.env` / file publik** | Atur di server atau `.env` |

Prioritas: 🔴 wajib sebelum hosting · 🟡 sebaiknya · ⚪ opsional.

---

## 0. Ringkasan: yang paling perlu Anda urus dulu

| # | Prioritas | Item | Kenapa |
|---|---|---|---|
| 1 | 🔴 | **Data contoh dianggap data asli** (C): nomor sertifikat, nama lembaga akreditasi, klien (Siemens, Boeing, CAT, Komatsu), rating "★ 4.9/5.0", statistik "120+ deliveries", "99.98%", alamat, telepon, email | Semuanya karangan seeder. Menampilkan nomor sertifikat atau nama klien palsu di situs publik berisiko hukum dan reputasi |
| 2 | 🔴 | **Akses admin produksi** (D): `CMS_ADMIN_EMAILS` dan `CMS_ADMIN_PASSWORD` kosong di `.env` | Di produksi, panel `/admin` **tertutup untuk semua orang** dan seeder **tidak membuat admin** sampai keduanya diisi |
| 3 | 🔴 | **`.env` produksi** (D): `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://localhost:8000`, `APP_NAME=Laravel`, DB memakai user `root` | Debug aktif membocorkan detail error, nama admin panel tampil "Laravel", URL salah |
| 4 | 🔴 | **Jangan jalankan `db:seed` di produksi tanpa sadar** (D) | Seeder menimpa konten (`updateOrCreate`) dan membuat data contoh. Lihat bagian 6 |
| 5 | 🔴 | **Nama perusahaan belum konsisten** (A/B/C): "PT. Apex Industrial Engineering" vs proyek/login "Trissan" | Putuskan satu nama, lalu samakan di CMS dan fallback |
| 6 | 🔴 | **Halaman legal tidak ada** (C): Privacy Policy, Terms, dst. | Link footer tampil sebagai teks biasa sampai URL diisi; situs bisnis sebaiknya punya halaman privasi |
| 7 | 🟡 | **Judul section hardcoded** (A), mis. "ENGINEERED OUTPUT": lihat bagian 1–5 | Tidak bisa diubah dari CMS |
| 8 | 🟡 | **Tailwind CDN + `lucide@latest` + Google Fonts dimuat dari internet saat runtime** (D) | Tailwind sendiri tidak menyarankan CDN untuk produksi; halaman lambat dan bergantung pihak ketiga |
| 9 | 🟡 | **Favicon kosong** (0 byte) dan **tidak ada meta SEO sosial** (D) | Tab browser tanpa ikon; tidak ada Open Graph untuk preview WhatsApp/LinkedIn |
| 10 | 🟡 | **Hotlink gambar Google (`lh3.googleusercontent.com`)** sebagai gambar cadangan (B) | Bukan aset milik Anda; bisa putus atau melanggar hak cipta |
| 11 | 🟡 | **Notifikasi email pesan kontak belum ada** (D) | Pesan hanya masuk ke tabel Contact Submissions; tidak ada email ke tim Anda |
| 12 | ⚪ | Teks antarmuka dalam JavaScript, `lang="en"` di Works & Portfolio | Detail, lihat bagian 7 |

---

## 1. Landing (`/`) — `resources/views/home.blade.php`

### Teks hardcoded (A)

| Section | Teks | Lokasi |
|---|---|---|
| Slider layanan | "Core Capabilities (N Services)" (label mobile), tombol "EXPLORE WORKS", pesan kosong "Belum ada data layanan." | home:~150–175 |
| **Featured Works** | **Badge "ENGINEERED OUTPUT"**, judul **"Featured Industrial Works"**, deskripsi "Tinjau contoh rekam jejak presisi manufaktur…", tombol "Detail" / "Lihat Spesifikasi & Detail", "Lihat Semua Portofolio Proyek (Works)", pesan kosong | home:260–306 |
| **Direktori layanan** | Badge "LAYANAN & SPESIALISASI TEKNIK / CAPABILITY DIRECTORY", judul "Layanan & Kapabilitas Rekayasa Industri", deskripsi "Direktori lengkap kapabilitas…", tombol "Eksplorasi Semua Services" | home:311 |
| **Contact** | "GET IN TOUCH", "Contact Us", "Hubungi tim rekayasa industri kami…", "Direct Inquiries", "How Can We Help?", "Konsultasikan kebutuhan permesinan…", label "Telepon Hotline", "Alamat Pabrik & Fasilitas", "SURAT ELEKTRONIK (EMAIL)", tombol "CONTACT FORM" | home:376–418 |
| Sosial media | "MEDIA SOSIAL & KOMUNITAS", "Terhubung di Saluran Resmi" | home:428 |
| Lokasi | "LOKASI KANTOR PUSAT & FASILITAS PABRIK", "Headquarters & Plant Location", "Akses strategis koridor industri Cikarang…", "Buka di Google Maps", "Alamat Resmi Operasional", "Zona Fasilitas" | home:454 |
| Form kontak | "Kirim Pesan", "Tim kami akan membalas…", label Nama/Email/Telepon/Layanan/Pesan, "Pilih layanan (opsional)" | home:470–503 |
| Pesan sukses | "Thank you for contacting us! We will get back to you soon." (**bahasa Inggris**) | `app/Http/Controllers/ContactController.php` |

### Fallback default (B), tampil bila CMS kosong

| Bagian | Nilai default | Lokasi |
|---|---|---|
| `<title>` | "Apex Industrial Engineering - Modern Industrial Solutions" | home:<title> |
| Hero | "Dedikasi Presisi Manufaktur Mutakhir", subtitle "Menghadirkan rekayasa komponen…", tombol "Lihat Portofolio"; 3 slide gambar Google | home:~65–92 |
| Trust card | "TRUSTED BY 50+ LEADERS", "★ 4.9/5.0", deskripsi, klien SIEMENS/BOEING/CAT/KOMATSU, "AS9100D & ISO 9001", "Global Audited" | home:~118–125 |
| About Teaser | "EST. 1998 • 25+ THN", "TENTANG {NAMA PERUSAHAAN}", "Dedikasi Rekayasa Presisi…", "Pelajari Profil Lengkap", stat 25+ / 99.98% / 150+ / 500+ | home:~187–215 |
| Featured works | badge "INDUSTRIAL WORK", kode "SERIES-TR-xxxx", sektor "INDUSTRIAL TIER-1", gambar Google | home:~262–280 |
| Peta | Koordinat Jababeka V Cikarang (embed dan tombol Maps) | home:~375 |

### Dari CMS (C), ganti isinya di Filament

Page Settings → **Landing**: SEO, hero (judul, subtitle, CTA, slide), Trust Card (label, rating, klien), About Teaser.
Global Settings: telepon, alamat, email, maps, overlay lokasi, sosial media.
Services / Service Clusters: seluruh layanan. Works: Featured (maks 6).

---

## 2. Works (`/works`) — `resources/views/works.blade.php`

### Teks hardcoded (A)

| Teks | Lokasi |
|---|---|
| Breadcrumb "Beranda / Works" | works:17 |
| Tab "Semua Layanan (ALL WORKS)", "Kategori Aktif: Semua Layanan (ALL WORKS)" | works:59, 99 |
| Opsi sort: "Terbaru / Newest First", "Toleransi Tertinggi (Micron)", "Skala Batch Terbesar" | works:82–84 |
| **"Sertifikasi AS9100D & Rekam NDT Lengkap"** (klaim sertifikasi) | works:105 |
| **"Arsip Batch 2024-2025"** (periode tertulis mati) | works:172 |
| Opsi "9 per halaman", "18 per halaman", "Semua" | works:189–190 |
| Modal: "SAMPLE PRODUCT OUTPUT", **"DOKUMENTASI BENCHMARK: Gambar di atas merupakan representasi sampel output produk…"**, "Spesifikasi Utama", "Tanggal Produksi / Delivery", "Sertifikasi & Standar Mutu", "Tutup Detail" | works:210–212 |
| State kosong "Tidak ada proyek yang sesuai…" + tombol reset | works:157–162 |

### Fallback (B)

| Bagian | Nilai | Lokasi |
|---|---|---|
| `<title>`, hero title, subtitle | "Daftar Produk & Hasil Pengerjaan (Works)…" | works:2, 24–25 |
| 4 stat card bila CMS kosong | "120+ Total Deliveries", "±0.002mm Micron Gauge", "100% Quality Assurance", "AS9100D Compliance" (angka karangan) | works:43 |

CMS: Page Settings → **Works** (hero, 4 stat), Works (semua card dan modal), Services (tab filter).

---

## 3. License (`/license`) — `resources/views/license.blade.php`

### Teks hardcoded (A)

| Teks | Lokasi |
|---|---|
| Breadcrumb, tombol "Sertifikasi Produk Manufaktur", "Akreditasi Fasilitas & Korporat" | license:253 |
| Badge "Corporate & Facility Accreditation", judul **"Sertifikasi Perusahaan & Fasilitas Fabrikasi"**, deskripsi "Akreditasi sistem manajemen mutu…" | license:269 |
| Tab "Semua Kategori", header tabel (Standar Perusahaan & Dokumen, Badan Akreditasi, ID / Registrasi, Masa Berlaku, Gambar Sertifikasi), tombol "Show Image" | license:287–305 |
| Badge "Product-Level Compliance", judul **"Sertifikasi Khusus Produk Manufaktur"**, deskripsi "Dokumentasi sertifikasi uji tipe… (CE, ATEX, ASME, ISO)" | license:309 |
| Label kartu: "Unggulan", "Lingkup:", "Show Gambar Sertifikasi", "Download", "Lihat Produk" | license:325–357 |
| **🐞 BUG: "Total 4 Dokumen Produk · Menampilkan 1 - 4 dari 4 Sertifikasi Produk · Menampilkan 2 Produk Unggulan"**: angka **tertulis mati**, tidak mengikuti data | license:362 |
| Modal: "Apex Verification Engine", "Detail Lisensi", "Nomor Registrasi:", "Badan Akreditasi:", "Validitas Hingga:", "Ruang Lingkup Sertifikasi:", "Download Sertifikat" | license:380–406 |

### Fallback (B)

| Bagian | Nilai | Lokasi |
|---|---|---|
| `<title>`, hero title/subtitle, badge card | "License & Certification", "Portofolio Lisensi, Akreditasi, dan Sertifikasi…", "Integritas Mutu Pabrik / Fasilitas Terakreditasi Penuh / Standar Internasional ISO & ASME" | license:2–6, 253 |
| 4 stat | "4+ Sertifikasi Produk", "6 QMS & Aerospace", "100% Bejana Tekan & Offshore", "7 K3, Lingkungan & Otomotif" | license:264 |
| 4 highlight card | ISO 9001:2015 (TÜV SÜD · Audit 2025), AS9100 Rev D (Bureau Veritas), ASME 'U' & 'R', ISO 14001/45001 (DNV GL & SGS) | license:284 |

CMS: Page Settings → **Licenses** (hero, badge, stat, highlight card), Corporate Accreditations, Certification Categories, Product Certifications.

---

## 4. Portfolio (`/portofolio`) — `resources/views/portofolio.blade.php`

### Teks hardcoded (A)

| Teks | Lokasi |
|---|---|
| Breadcrumb "Beranda / Portofolio" | portofolio:8 |
| Badge "Katalog Hasil Kerja Presisi", judul **"Showcase Engineered Deliverables & Output Manufaktur"**, deskripsi "Representasi aktual standar pengerjaan… FAI, CMM Inspection…" | portofolio:23 |
| Badge "Database Registrasi Resmi", opsi sort "Urutkan: ID Registrasi / Kualifikasi Tertinggi / Klaster Spesialisasi", label "Aktif"/"Non-Aktif", "Fokus Teknis:" | portofolio:40–67 |
| Pesan kosong ("Belum ada hasil kerja…", "Tidak ada personil yang cocok…") | portofolio:40, 71 |

### Fallback (B)

| Bagian | Nilai | Lokasi |
|---|---|---|
| `<title>`, hero | "Portofolio Kapabilitas Teknik…", deskripsi menyebut **"15 insinyur berlisensi internasional (ASME, AWS, BNSP, Siemens, ASNT)"** | portofolio:2–7 |
| 5 stat | "15 Tenaga Ahli", "100% Audit Kualifikasi", "4 Klaster", "45+ Lisensi Valid", "500+ Deliverables" | portofolio:21 |
| Tag revisi | "APEX REGISTRY: REV-2024.Q2-ACTIVE" | portofolio:~40 |

CMS: Page Settings → **Portfolio** (hero, 5 stat, registry tag), Portfolio Categories/Items, Personnel, Skills, Specialty Clusters. Catatan: seeder memuat hanya 5 personil; teks "15 insinyur" masih tertulis di fallback hero.

---

## 5. About (`/about`) — `resources/views/abtus.blade.php`

### Teks hardcoded (A)

| Teks | Lokasi |
|---|---|
| Breadcrumb "Beranda / About Us" | abtus:61 |
| "Fondasi Korporasi & Integritas Rekayasa", "Prinsip Kepatuhan & Standar Mutu Berkelanjutan" | abtus:69–71 |
| "Infrastruktur & Galeri Pabrik", judul **"Fasilitas Manufaktur & Laboratorium Uji"**, **"Workshop terpadu seluas 12.000 m² berstandar industri dirgantara di Kawasan Industri Jababeka V Cikarang."** | abtus:129–135 |
| **"100% Operational Capacity"** (klaim) | abtus:138 |
| "Perjalanan Sejarah", judul "Milestone Perkembangan & Rekayasa Presisi", **"Transformasi konsisten lebih dari seperempat abad…"** | abtus:176–182 |
| "Tata Kelola & Kepemimpinan", "Bagan Struktur Organisasi Perusahaan", "Struktur kepemimpinan teknis dan manajemen…", "Dokumen Resmi:" | abtus:222–232 |

### Dari CMS (C)

About Company: hero, overlay, PDF profil, partner banner, Visi/Misi/Nilai, fasilitas, milestone, struktur organisasi. Page Settings → **About**: hanya SEO.
Fallback `<title>`: "About Us — PT Apex Industrial Engineering".

---

## 6. Navbar & Footer (semua halaman) — `resources/views/layouts/`

| Teks | File:baris | Tipe |
|---|---|---|
| Menu: "Landing Page", "Capabilities & Works", "Services", "Featured Works", "Portofolio", "Company & Credentials", "About Us", "License & Certification", "Contact" | navbar:17–80 | A |
| **Sub-judul menu**: "Output & Dokumentasi Proyek", "Tenaga Kerja & Fabrikasi Lapangan", "Profil Pabrik, Visi & Standar Mutu", "AS9100D, ISO 9001 & Legalitas Resmi" | navbar:38–72 | A |
| Tombol "Kontak", tab bawah "Home / Works / Portofolio / About / Lisensi" (campuran Inggris/Indonesia) | navbar:85–116 | A |
| Brand fallback "PT. Apex Industrial Engineering" + tagline "Industrial Engineering" | navbar/footer | B |
| Footer: "NAVIGASI UTAMA", 7 label menu (mis. "Portofolio Manpower", "Contact Us / Hubungi Kami"), "KANTOR PUSAT & FASILITAS MANUFAKTUR", label "ALAMAT / TELEPON HOTLINE & WHATSAPP / SURAT ELEKTRONIK" | footer:30–74 | A |
| Footer fallback: deskripsi "Mitra manufaktur presisi tier-1…", alamat "Kawasan Industri Jababeka V, Blok C-18 No. 4…", telepon "+62 (21) 5508-8990" dan "+62 811-9870-221", email "contact@apex-engineering.co.id", copyright "© 2026 PT. Apex…" | footer | B |
| Gambar cadangan Google (logo navbar 1×, footer 2×) | navbar:7, footer:10, 22 | B |

Catatan: "Portofolio Manpower" di footer dan "Portofolio" di navbar menunjuk ke halaman yang sama; samakan istilahnya.

---

## 7. Data contoh seeder yang harus diganti di CMS (C)

Semua ini sudah ada di database lokal dan tampil di situs. Ganti sebelum hosting (atau kosongkan).

| Menu Filament | Isi contoh yang karangan | Yang harus dilakukan |
|---|---|---|
| **Global Settings** | Nama "PT. Apex Industrial Engineering", telepon `+62 (21) 5508-8990` dan `+62 812-3456-7890`, email `contact@/corporate@apex-engineering.co.id`, alamat Jababeka V, link sosial media (apexindustrial), koordinat peta dan overlay "Apex Plant Facility 01", copyright (tahun 2026 tertanam), legal links (URL kosong) | Isi data perusahaan asli; logo navbar/footer dan banner partner masih gambar contoh |
| **Page Settings** (5 tab) | Hero landing, trust card (klien, rating), stat di semua halaman, teks About Teaser, registry tag "REV-2024.Q2" | Ganti teks dan **hapus klaim yang tidak bisa dibuktikan** |
| **About Company** | Sejarah "1998", fasilitas 3 bay, 5 milestone, struktur organisasi (nama CEO/GM/4 kepala divisi karangan), Visi/Misi/Nilai, PDF profil contoh | Isi data asli; unggah PDF profil asli |
| **Services & Service Clusters** | 12 layanan + 4 klaster contoh | Sesuaikan dengan layanan sebenarnya |
| **Works** | 9 work contoh (serial, material, toleransi, klien/sektor, tanggal produksi) | Ganti atau hapus; gambar placeholder "CONTOH GAMBAR" |
| **Corporate Accreditations** (6) dan **Product Certifications** (3) | **Nomor registrasi, lembaga, tanggal berlaku, lingkup karangan** (mis. `CERT-ISO-9001-2024`, `ASME-STAMP-U-4412`) | 🔴 Wajib diganti dengan sertifikat asli atau dihapus |
| **Portfolio Items** (8) dan **Personnel** (5) | Proyek dan personil karangan, kode registrasi `ENG-CNC-001` dst. | Ganti atau hapus |
| **Users** | `admin@trissan.com` dan `test@example.com` dengan password `password` (hanya lokal/testing) | Jangan bawa ke produksi |
| **Media** | Semua gambar/PDF di `storage/app/public/seed/` | Hapus folder `seed/` setelah semua diganti |

---

## 8. Konfigurasi, `.env`, dan file publik (D)

| Item | Kondisi sekarang | Yang harus dilakukan |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://localhost:8000` | URL domain asli (`https://…`) |
| `APP_NAME` | `Laravel` (tampil di judul panel admin dan nama pengirim email) | Nama perusahaan |
| `APP_LOCALE` | `en` | `id` bila ingin pesan validasi/sistem Indonesia |
| `LOG_LEVEL` | `debug` | `warning`/`error` |
| `DB_USERNAME` | `root` | User khusus dengan hak terbatas; password kuat |
| `CMS_ADMIN_EMAILS` | kosong | Isi email admin yang berhak masuk `/admin` (pisahkan dengan koma) |
| `CMS_ADMIN_PASSWORD` | kosong | Isi password kuat sebelum membuat admin pertama |
| `MAIL_MAILER` | `log` (email tidak benar-benar terkirim) | SMTP asli bila mau notifikasi; `MAIL_FROM_ADDRESS` masih `hello@example.com` |
| `SESSION_DRIVER` / `CACHE_STORE` / `QUEUE_CONNECTION` | `database` | Pastikan tabelnya termigrasi di server; jalankan worker bila memakai queue |
| `public/storage` | symlink lokal | Jalankan `php artisan storage:link` di server |
| `public/favicon.ico` | **0 byte (kosong)** | Ganti favicon asli |
| `public/robots.txt` | mengizinkan semua | Tambah `Sitemap:` dan blok `/admin` bila perlu |
| Meta SEO sosial | tidak ada `og:title`, `og:image`, `canonical` | Tambahkan di `<head>` tiap halaman |
| CDN pihak ketiga | `cdn.tailwindcss.com`, `unpkg.com/lucide@latest`, Google Fonts | Build Tailwind sendiri (`npm run build`), kunci versi Lucide, host font lokal |
| `<html lang>` | `en` di Works dan Portfolio, `id` di halaman lain | Samakan ke `id` |
| Halaman error | tidak ada 404/500 kustom | Opsional: buat `resources/views/errors/404.blade.php` |

---

## 9. Cara mengisi konten saat produksi (penting)

`DatabaseSeeder` membuat admin, **lalu mengisi semua data contoh** dengan `updateOrCreate`. Kalau dijalankan lagi setelah Anda mengedit CMS, isian Anda **tertimpa**.

Saran alur produksi:
1. `php artisan migrate --force`
2. Isi `CMS_ADMIN_EMAILS` dan `CMS_ADMIN_PASSWORD`, lalu buat admin saja (mis. lewat `php artisan tinker` atau pisahkan seeder admin dari seeder konten).
3. Isi semua konten lewat Filament. Jangan menjalankan `db:seed` penuh di produksi.
4. Hapus data/gambar contoh (bagian 7).

---

## 10. Temuan kecil lain

- `license:362`: angka "4" pada bar bawah kartu produk tertulis mati (lihat bagian 3). Perlu diganti dinamis atau bar dihapus.
- Pesan sukses form kontak berbahasa Inggris; sisa situs berbahasa Indonesia.
- Teks yang dibuat lewat JavaScript (mis. "Menampilkan 1 - 9 dari N Total Hasil Proyek", "Tidak ada hasil proyek", "Halaman Sebelumnya") tertulis di blok `<script>` tiap view dan tidak bisa diubah dari CMS.
- `service_icon_svg` dirender mentah (`{!! !!}`); aman selama hanya admin tepercaya yang mengisinya.
- Gambar contoh bertuliskan "CONTOH GAMBAR - GANTI MELALUI CMS"; pastikan tidak ada yang tersisa di halaman publik.

---

## 11. Rekomendasi tindak lanjut

Kalau Anda ingin judul dan deskripsi section (mis. "ENGINEERED OUTPUT", "Featured Industrial Works", "Layanan & Kapabilitas Rekayasa Industri", "GET IN TOUCH / Contact Us", judul About dan License) bisa diubah dari CMS tanpa menyentuh kode, itu bisa dijadikan field di **Page Settings**, per halaman, dengan teks sekarang sebagai nilai awal. Ini sama dengan "Keputusan Terbuka #1" di `prd.md`.
