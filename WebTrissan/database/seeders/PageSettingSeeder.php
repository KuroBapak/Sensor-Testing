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
                'seo_title' => 'PT Apex Industrial Engineering | Precision Machining & Global Standard Fabrication',
                'seo_description' => 'Solusi rekayasa presisi dan fabrikasi berstandar global untuk sektor kedirgantaraan, otomotif, bejana tekan, dan permesinan presisi 5-axis.',
                'hero_title' => 'Rekayasa Presisi & Fabrikasi Berstandar Global',
                'hero_subtitle' => 'Menghadirkan solusi permesinan multi-axis CNC, fabrikasi bejana tekan bersertifikasi ASME, dan otomasi industri dengan toleransi mikron.',
                'hero_cta_text' => 'Lihat Portofolio',
                'hero_cta_url' => '/works',
                'hero_slides' => [
                    ['image' => '/images/hero-1.jpg', 'alt_text' => '5-Axis CNC Precision Machining Center'],
                    ['image' => '/images/hero-2.jpg', 'alt_text' => 'Heavy Welding & ASME Pressure Vessel Fabrication'],
                    ['image' => '/images/hero-3.jpg', 'alt_text' => 'Industrial Robotics & Automation System'],
                ],
                'trust_card' => [
                    'label' => 'TRUSTED BY 50+ LEADERS',
                    'rating' => '★ 4.9/5.0',
                    'description' => 'Dipercaya oleh prinsipal industri multinasional tier-1 untuk komponen kritis dan sistem manufaktur kompleks.',
                    'clients' => ['SIEMENS', 'BOEING', 'CAT', 'KOMATSU'],
                    'footer_left' => 'AS9100D & ISO 9001',
                    'footer_right' => 'Global Audited',
                ],
                'about_teaser' => [
                    'image' => '/images/about-teaser.jpg',
                    'image_badge' => 'EST. 1998 • 25+ THN',
                    'eyebrow' => 'TENTANG PERUSAHAAN',
                    'title' => 'Dedikasi Presisi Sejak 1998',
                    'paragraph_1' => 'Apex Industrial Engineering berdiri dengan komitmen memberikan solusi teknik presisi dengan toleransi ekstrem bagi industri kritis.',
                    'paragraph_2' => 'Didukung oleh lebih dari 150 insinyur berkualifikasi tinggi dan fasilitas modern seluas 12.000 m² di Kawasan Industri Jababeka.',
                    'cta_text' => 'Pelajari Profil Lengkap',
                    'cta_url' => '/about',
                ],
            ]
        );
    }

    private function seedWorks(): void
    {
        PageSetting::updateOrCreate(
            ['page' => 'works'],
            [
                'seo_title' => 'Daftar Produk & Hasil Pengerjaan (Works) | PT Apex Industrial Engineering',
                'seo_description' => 'Katalog lengkap hasil manufaktur komponen presisi, sub-assembly berstandar ASME, dan sistem rekayasa kritis.',
                'hero_title' => 'DAFTAR PRODUK & HASIL PENGERJAAN (WORKS)',
                'hero_subtitle' => 'Katalog komponen presisi, sub-assembly berstandar ASME, dan sistem rekayasa kritis dengan toleransi mikron dan sertifikasi internasional.',
                'stats' => [
                    ['value' => '120+', 'label' => 'Total Deliveries', 'icon' => 'local_shipping', 'caption' => 'Pengiriman On-Time 99.4%'],
                    ['value' => '±0.002mm', 'label' => 'Micron Gauge', 'icon' => 'straighten', 'caption' => 'Akurasi Zeiss CMM Laser'],
                    ['value' => '100%', 'label' => 'Quality Assurance', 'icon' => 'verified', 'caption' => 'Zero Defect Delivery'],
                    ['value' => 'AS9100D', 'label' => 'Compliance', 'icon' => 'flight_takeoff', 'caption' => 'Aerospace Certified'],
                ],
            ]
        );
    }

    private function seedPortfolio(): void
    {
        PageSetting::updateOrCreate(
            ['page' => 'portfolio'],
            [
                'seo_title' => 'Direktori Personil & Hasil Kerja Portofolio | PT Apex Industrial Engineering',
                'seo_description' => 'Profil insinyur ahli berlisensi internasional dan rekam jejak deliverable industri berpresisi tinggi.',
                'hero_title' => 'DIREKTORI REKAYASA & PORTOFOLIO TEKNIK',
                'hero_subtitle' => 'Menghubungkan kapabilitas 15 insinyur ahli bersertifikasi internasional dengan rekam jejak deliverable industri berpresisi tinggi.',
                'registry_revision_tag' => 'APEX REGISTRY: REV-2024.Q2-ACTIVE',
                'stats' => [
                    ['value' => '15', 'label' => 'Tenaga Ahli', 'caption' => 'Lead Engineers'],
                    ['value' => '100%', 'label' => 'Audit Kualifikasi', 'caption' => 'Verified Skills'],
                    ['value' => '4', 'label' => 'Disiplin Rekayasa', 'caption' => 'Specialty Clusters'],
                    ['value' => '28+', 'label' => 'Lisensi Valid', 'caption' => 'Active Licenses'],
                    ['value' => '40', 'label' => 'Deliverables', 'caption' => 'Documented Projects'],
                ],
            ]
        );
    }

    private function seedLicenses(): void
    {
        PageSetting::updateOrCreate(
            ['page' => 'licenses'],
            [
                'seo_title' => 'Lisensi & Akreditasi Mutu Internasional | PT Apex Industrial Engineering',
                'seo_description' => 'Sertifikasi ISO 9001, AS9100D, ASME, dan akreditasi pengujian mutu PT Apex Industrial Engineering.',
                'hero_title' => 'LISENSI & AKREDITASI MUTU INTERNASIONAL',
                'hero_subtitle' => 'Komitmen tak kenal kompromi terhadap keselamatan kerja, ketertelusuran material, dan standar industri global.',
                'hero_badge_card' => [
                    'eyebrow' => 'Integritas Mutu Pabrik',
                    'title' => 'Fasilitas Terakreditasi Penuh',
                    'caption' => 'Standar Internasional ISO & ASME',
                ],
                'stats' => [
                    ['value' => '4+', 'label' => 'Sertifikasi Produk', 'caption' => 'Tervalidasi uji lab independen'],
                    ['value' => '6', 'label' => 'QMS & Aerospace', 'caption' => 'Sertifikat sistem mutu aktif'],
                    ['value' => '100%', 'label' => 'Bejana Tekan & Offshore', 'caption' => 'Kepatuhan ASME Sec VIII & IX'],
                    ['value' => '7', 'label' => 'K3 Lingkungan & Otomotif', 'caption' => 'Akreditasi ISO 14001 & 45001'],
                ],
                'corporate_highlight_cards' => [
                    ['icon' => 'verified_user', 'title' => 'ISO 9001:2015', 'subtitle' => 'QMS Fabrikasi', 'caption' => 'TÜV SÜD · Audit 2025'],
                    ['icon' => 'flight', 'title' => 'AS9100D Rev B', 'subtitle' => 'Aerospace & Defense', 'caption' => 'PRI / Nadcap Audited'],
                    ['icon' => 'speed', 'title' => 'ASME Boiler & PV', 'subtitle' => 'U & S Stamp Certified', 'caption' => 'Hartford Steam Boiler'],
                    ['icon' => 'eco', 'title' => 'ISO 14001 & 45001', 'subtitle' => 'HSE & Keselamatan', 'caption' => "Lloyd's Register Valid"],
                ],
            ]
        );
    }

    private function seedAbout(): void
    {
        PageSetting::updateOrCreate(
            ['page' => 'about'],
            [
                'seo_title' => 'Tentang Kami | PT Apex Industrial Engineering',
                'seo_description' => 'Sejarah, visi, misi, fasilitas permesinan modern, dan struktur kepemimpinan PT Apex Industrial Engineering.',
            ]
        );
    }
}
