<?php

namespace Database\Seeders;

use App\Models\AboutCompany;
use Illuminate\Database\Seeder;

class AboutCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $about = AboutCompany::firstOrNew(['id' => 1]);

        $about->hero_badge = 'Rekayasa Presisi & Fabrikasi Berstandar Global';
        $about->hero_title = 'Membangun Keunggulan Industri Berkelanjutan Sejak 1998';
        $about->hero_description = "PT Apex Industrial Engineering berawal dari bengkel rekayasa presisi kecil dan kini telah berevolusi menjadi manufaktur tier-1 pilihan sektor kedirgantaraan, minyak & gas, dan otomasi skala besar.\n\nKami mengintegrasikan permesinan CNC 5-axis ultra presisi, fabrikasi bejana tekan bersertifikat ASME, serta sistem mekatronika terdistribusi untuk menghasilkan produk berkeandalan tinggi.";
        $about->hero_image = '/images/about-hero.jpg';
        $about->image_overlay = [
            'plant_badge' => 'PLANT 01 • JABABEKA V (12.000 m²)',
            'caption_title' => 'Apex Advanced Manufacturing Facility',
            'caption_subtitle' => 'Cikarang Industrial Estate, Bekasi',
            'tolerance_chip' => 'Toleransi ±0.002mm',
        ];
        $about->company_profile_pdf = '/files/Apex_Company_Profile.pdf';
        $about->partners_image = '/images/partners-banner.png';

        $about->vision_mission_values = $this->visionMissionValues();
        $about->facilities = $this->facilities();
        $about->milestones = $this->milestones();
        $about->org_chart = $this->orgChart();

        $about->save();
    }

    private function visionMissionValues(): array
    {
        return [
            [
                'card_type' => 'vision',
                'badge' => 'Visi 2030',
                'title' => 'Visi Perusahaan',
                'body' => 'Menjadi tolok ukur manufaktur presisi dan rekayasa fabrikasi tier-1 di Asia Tenggara dengan keunggulan teknologi tanpa kompromi.',
                'footer_label' => 'Target',
                'footer_value' => 'Tier-1 Regional Leader',
            ],
            [
                'card_type' => 'mission',
                'badge' => 'Operasional',
                'title' => 'Misi Strategis',
                'body' => [
                    'Menyediakan komponen berpresisi tinggi dengan toleransi mikron sesuai standar keselamatan global.',
                    'Mengembangkan kapabilitas rekayasa otomasi untuk meningkatkan efisiensi manufaktur klien.',
                    'Membangun ekosistem tenaga kerja teknik bersertifikat dan berintegritas tinggi.',
                ],
                'footer_label' => 'Metode',
                'footer_value' => 'Continuous Kaizen',
            ],
            [
                'card_type' => 'values',
                'badge' => 'Standar Kualitas',
                'title' => 'Nilai Pokok Mutu',
                'body' => [
                    ['name' => 'Precision First', 'value' => 'Toleransi s/d 0.002 mm'],
                    ['name' => 'Zero Defect', 'value' => 'Inspeksi CMM 100%'],
                    ['name' => 'Traceability', 'value' => 'MTC bersertifikat material'],
                ],
                'footer_label' => 'Integritas',
                'footer_value' => '100% Traceable',
            ],
        ];
    }

    private function facilities(): array
    {
        return [
            [
                'facility_image' => '/images/facility-1.jpg',
                'facility_tag' => 'BAY 01 • MACHINING',
                'facility_title' => '5-Axis CNC Precision Machining Center',
                'facility_description' => 'Pusat permesinan 5-axis multiaxis berkapasitas tinggi untuk komponen kedirgantaraan, turbin, dan mold otomotif berakurasi mikron.',
                'spec_tag_1' => 'DMG MORI 5-Axis Center',
                'spec_tag_2' => 'Volume Kerja: 2.000 x 1.500 mm',
                'sort_order' => 1,
            ],
            [
                'facility_image' => '/images/facility-2.jpg',
                'facility_tag' => 'BAY 02 • FABRICATION',
                'facility_title' => 'Heavy Welding & Pressure Vessel Bay',
                'facility_description' => 'Fasilitas fabrikasi bejana tekan tersertifikasi ASME dengan sistem pengelasan otomatis SAW, GTAW, dan cladding Inconel.',
                'spec_tag_1' => 'Kapasitas Crane: 50 Ton',
                'spec_tag_2' => 'ASME U & S Stamp Compliant',
                'sort_order' => 2,
            ],
            [
                'facility_image' => '/images/facility-3.jpg',
                'facility_tag' => 'BAY 03 • QUALITY QC',
                'facility_title' => 'Temperature Controlled Metrology Lab',
                'facility_description' => 'Laboratorium metrologi ber-AC konstan 20°C dilengkapi Zeiss CMM 3D scanning dan pengujian NDT ultrasonik level II/III.',
                'spec_tag_1' => 'Zeiss Accura CMM',
                'spec_tag_2' => 'Akurasi 0.9 µm + L/350',
                'sort_order' => 3,
            ],
        ];
    }

    private function milestones(): array
    {
        return [
            [
                'year' => '1998',
                'title' => 'Pendirian Bengkel Mekanikal',
                'description' => 'Didirikan di Jakarta Timur sebagai penyedia jasa perbaikan mesin perkakas dan fabrikasi komponen suku cadang pabrik.',
                'highlights' => ['Workshop 300 m²', '3 Mesin Bubut Konvensional'],
                'is_current' => false,
                'sort_order' => 1,
            ],
            [
                'year' => '2005',
                'title' => 'Adopsi CNC & Sertifikasi ISO 9001',
                'description' => "Investasi pertama pada pusat pemesinan CNC 3-axis serta meraih akreditasi ISO 9001 dari Lloyd's Register.",
                'highlights' => ['Sertifikasi ISO 9001:2000', 'Ekspansi Workshop ke Bekasi'],
                'is_current' => false,
                'sort_order' => 2,
            ],
            [
                'year' => '2012',
                'title' => 'Relokasi Pabrik ke Kawasan Jababeka',
                'description' => 'Pembangunan pabrik terpadu seluas 12.000 m² di Kawasan Industri Jababeka V untuk memenuhi permintaan manufaktur alat berat.',
                'highlights' => ['Lahan 12.000 m²', 'Sertifikasi Bejana Tekan ASME'],
                'is_current' => false,
                'sort_order' => 3,
            ],
            [
                'year' => '2019',
                'title' => 'Akreditasi Kedirgantaraan AS9100D',
                'description' => 'Lolos audit kualifikasi dirgantara AS9100D dan mulai menyuplai komponen turbin serta aerospace structural part ke tier-1 global.',
                'highlights' => ['AS9100D Rev B Nadcap', 'Mesin 5-Axis Hermle & DMG'],
                'is_current' => false,
                'sort_order' => 4,
            ],
            [
                'year' => '2024 - SEKARANG',
                'title' => 'Transformasi Otomasi & Smart Manufacturing',
                'description' => 'Implementasi sistem monitoring IoT pada lini permesinan dan robot chucking terintegrasi untuk produksi zero-defect.',
                'highlights' => ['Integrasi SCADA & Robotika', 'Ekspor Regional ASEAN & Jepang'],
                'is_current' => true,
                'sort_order' => 5,
            ],
        ];
    }

    private function orgChart(): array
    {
        return [
            'chart_revision_tag' => 'REV-2024.10',
            'ceo' => [
                'name' => 'Ir. Hendra Wijaya, M.Sc.',
                'position' => 'Chief Executive Officer',
                'photo' => '/images/team-ceo.jpg',
                'experience_tag' => '28+ Thn Pengalaman Dirgantara & Manufaktur',
            ],
            'general_manager' => [
                'name' => 'Bambang Kusuma, S.T., MBA',
                'position' => 'General Manager & Chief Operations Officer',
            ],
            'division_heads' => [
                [
                    'division_tag' => 'DIVISI 01',
                    'head_name' => 'Rian Hidayat, S.T.',
                    'head_position' => 'Head of Precision Machining',
                    'teams' => ['5-Axis Machining Team', 'Tooling & CAM Engineering', 'High Precision Turning'],
                    'sort_order' => 1,
                ],
                [
                    'division_tag' => 'DIVISI 02',
                    'head_name' => 'Agus Santoso, IWE',
                    'head_position' => 'Head of Welding & Pressure Vessel',
                    'teams' => ['ASME Code Fabrication', 'Specialty Alloy Cladding', 'Structural Assembly'],
                    'sort_order' => 2,
                ],
                [
                    'division_tag' => 'DIVISI 03',
                    'head_name' => 'Dimas Pratama, S.T.',
                    'head_position' => 'Head of Automation & SCADA',
                    'teams' => ['PLC & DCS Architecture', 'Robotic Chucking Systems', 'Smart Panel Assembly'],
                    'sort_order' => 3,
                ],
                [
                    'division_tag' => 'DIVISI 04',
                    'head_name' => 'Dr. Maya Surtikanti',
                    'head_position' => 'Head of Quality Assurance & Metrology',
                    'teams' => ['CMM Laser Inspection', 'NDT Testing Lab', 'ISO/AS9100 Compliance'],
                    'sort_order' => 4,
                ],
            ],
        ];
    }
}
