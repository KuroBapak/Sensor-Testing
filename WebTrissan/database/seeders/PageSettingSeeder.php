<?php

namespace Database\Seeders;

use App\Models\PageSetting;
use Illuminate\Database\Seeder;

class PageSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedLanding();
        $this->seedWorks();
        $this->seedPortfolio();
        $this->seedLicenses();
        $this->seedAbout();
    }

    private function seedLanding(): void
    {
        PageSetting::updateOrCreate(
            ['page' => 'landing'],
            [
                'seo_title' => 'PT. Trissan | Solusi Kelistrikan, Mekanikal & Otomasi Industri',
                'seo_description' => 'PT. Trissan menyediakan produk dan layanan kelistrikan serta mekanikal industri: material handling, otomasi, panel distribusi, hingga perawatan mesin.',
                'hero_title' => 'Solusi Kelistrikan & Mekanikal untuk Industri Anda',
                'hero_subtitle' => 'Dari material handling, otomasi, hingga instalasi dan perawatan mesin. PT. Trissan selalu hadir lebih baik dengan dukungan, keahlian, dan layanan purna jual yang maksimal.',
                'hero_cta_text' => 'Lihat Portofolio',
                'hero_cta_url' => '/works',
                'hero_slides' => [
                    ['image' => SeedMedia::image('hero-1'), 'alt_text' => 'Robot industri pada lini perakitan otomatis'],
                    ['image' => SeedMedia::image('hero-2'), 'alt_text' => 'Teknisi menguji panel listrik dan kontaktor'],
                    ['image' => SeedMedia::image('hero-3'), 'alt_text' => 'Pengerjaan fabrikasi dan pemotongan logam'],
                ],
                'trust_card' => [
                    'label' => 'DIPERCAYA PERUSAHAAN TERKEMUKA',
                    'rating' => '',
                    'description' => 'Dipercaya oleh perusahaan manufaktur dan industri terkemuka di Indonesia untuk instalasi, otomasi, dan perawatan fasilitas produksi.',
                    'clients' => ['YAMAHA', 'YASKAWA', 'DAIKIN', 'FREEPORT'],
                    'footer_left' => '3 Cabang Nasional',
                    'footer_right' => 'Bekasi • Malang • Timika',
                ],
                'about_teaser' => [
                    'image' => SeedMedia::image('about-teaser'),
                    'image_badge' => 'ALWAYS PRESENT BETTER',
                    'eyebrow' => 'TENTANG PERUSAHAAN',
                    'title' => 'Solusi Terbaik untuk Kebutuhan Pelanggan',
                    'description' => "PT. Trissan didirikan dengan tujuan memberi solusi dalam pemenuhan kebutuhan pelanggan. Kami menawarkan beragam produk kelistrikan dan mekanikal dengan dukungan dan keahlian yang lebih baik.\n\nKomitmen kami adalah memberikan pelayanan purna jual yang maksimal untuk produk dan layanan yang berkualitas. Hari ini tim kami melayani pelanggan dari tiga kantor di Bekasi, Malang, dan Timika.",
                    'cta_text' => 'Pelajari Profil Lengkap',
                    'cta_url' => '/about',
                ],
                'extra_data' => [
                    'featured_works_badge' => 'PROYEK TERBARU',
                    'featured_works_title' => 'Proyek Pilihan Kami',
                    'featured_works_description' => 'Beberapa pekerjaan terbaru kami untuk pelanggan industri, mulai dari instalasi fire hydrant dan chiller hingga konveyor material handling.',
                    'services_directory_badge' => 'LAYANAN & SPESIALISASI / CAPABILITY DIRECTORY',
                    'services_directory_title' => 'Layanan & Kapabilitas Kami',
                    'services_directory_description' => 'Dua belas bidang layanan mekanikal, elektrikal, otomasi, dan pengadaan industri. Klik setiap layanan untuk melihat dokumentasi proyek pada rekam jejak pekerjaan kami.',
                    'contact_badge' => 'HUBUNGI KAMI',
                    'contact_title' => 'Contact Us',
                    'contact_description' => 'Hubungi tim PT. Trissan untuk konsultasi kebutuhan kelistrikan dan mekanikal, kemitraan proyek, dan permintaan penawaran.',
                    'contact_card_label' => 'Direct Inquiries',
                    'contact_card_title' => 'Ada yang Bisa Kami Bantu?',
                    'contact_card_text' => 'Sampaikan kebutuhan instalasi, otomasi, material handling, atau perawatan mesin Anda kepada tim kami.',
                    'social_label' => 'MEDIA SOSIAL & KOMUNITAS',
                    'social_title' => 'Terhubung di Saluran Resmi',
                    'map_badge' => 'LOKASI KANTOR PUSAT',
                    'map_title' => 'Kantor Pusat Bekasi',
                    'map_description' => 'Ruko Metland Tambun, Tambun Selatan, Bekasi, Jawa Barat.',
                    'contact_success_message' => 'Terima kasih! Pesan Anda sudah kami terima dan tim kami akan segera menghubungi Anda.',
                ],
            ]
        );
    }

    private function seedWorks(): void
    {
        PageSetting::updateOrCreate(
            ['page' => 'works'],
            [
                'seo_title' => 'Daftar Proyek & Hasil Pengerjaan (Works) | PT. Trissan',
                'seo_description' => 'Dokumentasi proyek PT. Trissan: instalasi fire hydrant, water meter, chiller duct bypass, serta konveyor material handling untuk pelanggan industri.',
                'hero_title' => 'DAFTAR PROYEK & HASIL PENGERJAAN (WORKS)',
                'hero_subtitle' => 'Rekam jejak proyek instalasi, konveyor material handling, dan perawatan mesin yang kami kerjakan untuk pelanggan industri.',
                'stats' => [
                    ['value' => '7', 'label' => 'Proyek Terdokumentasi', 'icon' => 'task_alt', 'caption' => 'Instalasi, Konveyor & Maintenance'],
                    ['value' => '12', 'label' => 'Bidang Layanan', 'icon' => 'build', 'caption' => 'Mekanikal, Elektrikal & Otomasi'],
                    ['value' => '6', 'label' => 'Klien Proyek', 'icon' => 'apartment', 'caption' => 'Astra Honda, Mayora, Indofood CBP, dll.'],
                    ['value' => '4', 'label' => 'Prinsip Kerja', 'icon' => 'verified', 'caption' => 'Kepuasan, Mutu, Profesional, Integritas'],
                ],
            ]
        );
    }

    private function seedPortfolio(): void
    {
        PageSetting::updateOrCreate(
            ['page' => 'portfolio'],
            [
                'seo_title' => 'Portofolio & Tim Profesional | PT. Trissan',
                'seo_description' => 'Portofolio proyek PT. Trissan: instalasi fire hydrant, chiller, dan konveyor untuk pelanggan industri, serta tim profesional di Bekasi, Malang, dan Timika.',
                'hero_title' => 'PORTOFOLIO PROYEK & TIM PROFESIONAL',
                'hero_subtitle' => 'Kebutuhan pelanggan adalah faktor terpenting dalam pekerjaan kami, mulai dari desain hingga finishing assembly, dikerjakan secara terukur, terencana, dan terpadu.',
                'registry_revision_tag' => 'TRISSAN REGISTRY',
                'stats' => [
                    ['value' => '3', 'label' => 'Kantor Cabang', 'caption' => 'Bekasi, Malang, Timika'],
                    ['value' => '145', 'label' => 'Karyawan', 'caption' => 'Seluruh cabang'],
                    ['value' => '20+', 'label' => 'Klien Industri', 'caption' => 'Otomotif, Pertambangan, Manufaktur'],
                    ['value' => '12', 'label' => 'Bidang Layanan', 'caption' => 'Specialty Clusters'],
                    ['value' => '7', 'label' => 'Deliverables', 'caption' => 'Documented Projects'],
                ],
                'extra_data' => [
                    'catalog_badge' => 'Katalog Hasil Kerja',
                    'catalog_title' => 'Proyek yang Telah Kami Kerjakan',
                    'catalog_description' => 'Kepuasan pelanggan adalah tolok ukur keberhasilan proyek yang kami kerjakan, dengan kualitas produk sebagai tujuan kami.',
                    'personnel_badge' => 'Database Registrasi Resmi',
                ],
            ]
        );
    }

    /**
     * The previous website lists no licences or certifications, so this page keeps sample content.
     */
    private function seedLicenses(): void
    {
        PageSetting::updateOrCreate(
            ['page' => 'licenses'],
            [
                'seo_title' => 'Lisensi & Akreditasi Mutu | PT. Trissan',
                'seo_description' => 'Lisensi, sertifikasi, dan akreditasi mutu PT. Trissan sebagai bukti komitmen pada kualitas produk, keselamatan kerja, dan standar industri.',
                'hero_title' => 'LISENSI & AKREDITASI MUTU',
                'hero_subtitle' => 'Komitmen terhadap mutu, keselamatan kerja, dan standar industri.',
                'hero_badge_card' => [
                    'eyebrow' => 'Integritas Mutu',
                    'title' => 'Mutu & Kualitas Produk',
                    'caption' => 'Salah satu prioritas kerja PT. Trissan',
                ],
                'stats' => [
                    ['value' => '4+', 'label' => 'Sertifikasi Produk', 'caption' => 'Data contoh'],
                    ['value' => '6', 'label' => 'QMS & Aerospace', 'caption' => 'Data contoh'],
                    ['value' => '100%', 'label' => 'Bejana Tekan & Offshore', 'caption' => 'Data contoh'],
                    ['value' => '7', 'label' => 'K3 Lingkungan & Otomotif', 'caption' => 'Data contoh'],
                ],
                'corporate_highlight_cards' => [
                    ['icon' => 'verified_user', 'title' => 'ISO 9001:2015', 'subtitle' => 'QMS (data contoh)', 'caption' => 'Ganti melalui CMS'],
                    ['icon' => 'flight', 'title' => 'AS9100D Rev B', 'subtitle' => 'Aerospace (data contoh)', 'caption' => 'Ganti melalui CMS'],
                    ['icon' => 'speed', 'title' => 'ASME Boiler & PV', 'subtitle' => 'Bejana tekan (data contoh)', 'caption' => 'Ganti melalui CMS'],
                    ['icon' => 'eco', 'title' => 'ISO 14001 & 45001', 'subtitle' => 'HSE (data contoh)', 'caption' => 'Ganti melalui CMS'],
                ],
                'extra_data' => [
                    'corporate_badge' => 'Corporate & Facility Accreditation',
                    'corporate_title' => 'Sertifikasi Perusahaan',
                    'corporate_description' => 'Akreditasi sistem manajemen mutu, keselamatan operasional, dan kepatuhan lingkungan. Data pada halaman ini adalah contoh dan perlu diganti dengan dokumen resmi.',
                    'product_badge' => 'Product-Level Compliance',
                    'product_title' => 'Sertifikasi Khusus Produk',
                    'product_description' => 'Dokumentasi sertifikasi uji tipe dan sertifikat kepatuhan standar industri per produk. Data contoh.',
                ],
            ]
        );
    }

    private function seedAbout(): void
    {
        PageSetting::updateOrCreate(
            ['page' => 'about'],
            [
                'seo_title' => 'Tentang Kami | PT. Trissan',
                'seo_description' => 'Profil PT. Trissan: komitmen, prinsip kerja, jaringan kantor di Bekasi, Malang, dan Timika, serta bidang layanan perusahaan.',
                'extra_data' => [
                    'values_badge' => 'Komitmen & Prinsip Kerja',
                    'values_subtitle' => 'Prioritas PT. Trissan dalam Setiap Pekerjaan',
                    'facilities_badge' => 'Fasilitas Workshop',
                    'facilities_title' => 'Mesin Produksi di PT. Trissan Bekasi',
                    'facilities_description' => 'Workshop kami di Bekasi dilengkapi mesin milling, mesin cutting, dan mesin CNC untuk mendukung pembuatan komponen dan perakitan proyek.',
                    'milestones_badge' => 'Perjalanan Sejarah',
                    'milestones_title' => 'Milestone Perkembangan PT. Trissan',
                    'milestones_description' => 'Perjalanan dan pencapaian PT. Trissan dari tahun ke tahun.',
                    'structure_badge' => 'Tata Kelola & Kepemimpinan',
                    'structure_title' => 'Bagan Struktur Organisasi Perusahaan',
                    'structure_description' => 'Struktur kepemimpinan dan tim PT. Trissan, dari jajaran direksi hingga tim proyek di kantor pusat dan kantor perwakilan Timika.',
                ],
            ]
        );
    }
}
