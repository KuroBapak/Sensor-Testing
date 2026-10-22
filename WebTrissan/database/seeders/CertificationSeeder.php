<?php

namespace Database\Seeders;

use App\Models\CertificationCategory;
use App\Models\CorporateAccreditation;
use App\Models\ProductCertification;
use App\Models\Work;
use Illuminate\Database\Seeder;

class CertificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedCorporateAccreditations();
        $this->seedProductCertifications();
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'ISO & MUTU', 'slug' => 'iso-mutu', 'sort_order' => 1],
            ['name' => 'DIRGANTARA & AKREDITASI', 'slug' => 'dirgantara-akreditasi', 'sort_order' => 2],
            ['name' => 'KESELAMATAN & K3L', 'slug' => 'keselamatan-k3l', 'sort_order' => 3],
            ['name' => 'SERTIFIKASI PRODUK', 'slug' => 'sertifikasi-produk', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            CertificationCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }

    private function seedCorporateAccreditations(): void
    {
        $categories = CertificationCategory::all()->keyBy('slug');

        $accreditations = [
            [
                'category_slug' => 'iso-mutu',
                'standard_name' => 'ISO 9001:2015',
                'standard_description' => 'Sistem Manajemen Mutu Manufaktur',
                'accreditation_body' => "Lloyd's Register Quality Assurance (LRQA)",
                'registration_id' => 'CERT-ISO-9001-2024',
                'validity_date' => '2027-04-09',
                'scope_detail' => 'Desain, permesinan presisi CNC, perakitan fixture mekanikal-elektrikal, dan sistem otomasi.',
                'modal_title' => 'ISO 9001:2015 Quality Management System',
                'certificate_image' => '/images/certs/lrqa.png',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'dirgantara-akreditasi',
                'standard_name' => 'AS9100 Rev D',
                'standard_description' => 'Sistem Mutu Kedirgantaraan dan Pertahanan',
                'accreditation_body' => 'SAE International & Nadcap Approved',
                'registration_id' => 'CERT-AS-9100D-019',
                'validity_date' => '2028-08-14',
                'scope_detail' => 'Pemesinan komponen struktural kedirgantaraan tingkat 1 (Tier-1) dan turbin shafts.',
                'modal_title' => 'AS9100D Aerospace Quality System',
                'certificate_image' => '/images/certs/sae.png',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'keselamatan-k3l',
                'standard_name' => 'ISO 45001:2018',
                'standard_description' => 'Sistem Manajemen Keselamatan dan Kesehatan Kerja',
                'accreditation_body' => 'SGS International Certification',
                'registration_id' => 'CERT-OHSMS-45001',
                'validity_date' => '2026-02-10',
                'scope_detail' => 'Pengendalian keselamatan manufaktur berat, pemesinan kecepatan tinggi, dan bejana tekan.',
                'modal_title' => 'ISO 45001:2018 Occupational Health & Safety',
                'certificate_image' => '/images/certs/sgs.png',
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'keselamatan-k3l',
                'standard_name' => 'ISO 14001:2015',
                'standard_description' => 'Sistem Manajemen Lingkungan Industri',
                'accreditation_body' => 'SGS International Certification',
                'registration_id' => 'CERT-EMS-14001',
                'validity_date' => '2027-06-19',
                'scope_detail' => 'Pengelolaan limbah B3 permesinan pendingin (coolant) dan efisiensi energi.',
                'modal_title' => 'ISO 14001:2015 Environmental Management',
                'certificate_image' => '/images/certs/sgs.png',
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'dirgantara-akreditasi',
                'standard_name' => 'ASME Boiler & Pressure Vessel Code (BPVC) Stamp U',
                'standard_description' => 'Konstruksi dan Fabrikasi Bejana Tekan',
                'accreditation_body' => 'American Society of Mechanical Engineers',
                'registration_id' => 'ASME-STAMP-U-4412',
                'validity_date' => '2026-09-01',
                'scope_detail' => 'Desain, fabrikasi, dan pengujian hidrostatik bejana tekan hingga 15.000 PSI.',
                'modal_title' => 'ASME Stamp U Pressure Vessel Manufacturer',
                'certificate_image' => '/images/certs/asme.png',
                'sort_order' => 5,
            ],
            [
                'category_slug' => 'keselamatan-k3l',
                'standard_name' => 'SMK3 PP 50/2012 Emas',
                'standard_description' => 'Sistem Manajemen K3 Bendera Emas',
                'accreditation_body' => 'Kementerian Ketenagakerjaan RI',
                'registration_id' => 'KEMNAKER-SMK3-2023',
                'validity_date' => '2026-01-14',
                'scope_detail' => 'Pencapaian 166 kriteria evaluasi K3 dengan tingkat kepatuhan 94.5%.',
                'modal_title' => 'Sertifikat SMK3 Bendera Emas',
                'certificate_image' => '/images/certs/kemnaker.png',
                'sort_order' => 6,
            ],
        ];

        foreach ($accreditations as $item) {
            $category = $categories[$item['category_slug']] ?? null;
            if ($category) {
                $item['certification_category_id'] = $category->id;
                unset($item['category_slug']);
                CorporateAccreditation::updateOrCreate(['registration_id' => $item['registration_id']], $item);
            }
        }
    }

    private function seedProductCertifications(): void
    {
        $works = Work::all()->keyBy('work_slug');

        $certifications = [
            [
                'work_slug' => 'turbine-gearbox-shafts',
                'slug' => 'nadcap-turbine-shaft',
                'category_tag' => 'DIRGANTARA',
                'category_icon' => 'plane',
                'is_featured' => true,
                'validity_date' => '2027-03-11',
                'product_name' => 'Turbine Shaft Hardening Nadcap Audit Pass',
                'subtitle' => 'Special Process Heat Treatment for Inconel 625',
                'model_spec_identifier' => 'NADCAP-AC7102-HT',
                'card_icon' => 'flame',
                'certificate_number' => 'TR-NADCAP-2024-88',
                'issuing_body' => 'Nadcap / Performance Review Institute',
                'spec_rows' => [
                    ['label' => 'Standard', 'value' => 'NADCAP AC7102 Rev K'],
                    ['label' => 'Hardness', 'value' => '45-48 HRC Vacuum Treated'],
                    ['label' => 'Compliance', 'value' => '99.8% Batch Consistency'],
                ],
                'scope_description' => 'Heat treatment vacuum austenitizing & cryogenic tempering.',
                'certificate_image' => '/images/certs/nadcap-shaft.png',
                'certificate_pdf' => '/certificates/nadcap-shaft.pdf',
                'sort_order' => 1,
            ],
            [
                'work_slug' => 'scada-power-panel',
                'slug' => 'iec-61439-switchgear',
                'category_tag' => 'ELEKTRIKAL',
                'category_icon' => 'zap',
                'is_featured' => true,
                'validity_date' => '2026-11-19',
                'product_name' => 'IEC 61439-2 Low-Voltage Switchgear Arc Resistance Test',
                'subtitle' => 'Internal Arc Containment & Temperature Rise Limit',
                'model_spec_identifier' => 'IEC-61439-2:2020',
                'card_icon' => 'shield-check',
                'certificate_number' => 'TUV-LV-2023-404',
                'issuing_body' => 'TUV Rheinland Indonesia',
                'spec_rows' => [
                    ['label' => 'Rated Voltage', 'value' => '415V / 690V AC'],
                    ['label' => 'Short Circuit', 'value' => '65 kA / 1s RMS'],
                    ['label' => 'IP Protection', 'value' => 'IP65 Tested'],
                ],
                'scope_description' => 'Short-circuit withstand up to 65kA 1s internal arc containment.',
                'certificate_image' => '/images/certs/tuv-switchgear.png',
                'certificate_pdf' => '/certificates/tuv-switchgear.pdf',
                'sort_order' => 2,
            ],
            [
                'work_slug' => 'inspection-jigs',
                'slug' => 'cmm-zeiss-verification',
                'category_tag' => 'METROLOGI',
                'category_icon' => 'crosshair',
                'is_featured' => true,
                'validity_date' => '2026-06-01',
                'product_name' => 'CMM Geometric Dimensioning & Tolerancing (GD&T)',
                'subtitle' => 'Zeiss 3D Multi-Sensor Laser Coordinate Calibration',
                'model_spec_identifier' => 'ISO 1101 / ASME Y14.5',
                'card_icon' => 'check-circle-2',
                'certificate_number' => 'GD-T-2024-110',
                'issuing_body' => 'Zeiss Industrial Metrology Lab',
                'spec_rows' => [
                    ['label' => 'Resolution', 'value' => '0.0001 mm (0.1 µm)'],
                    ['label' => 'Uncertainty', 'value' => 'E0 = 0.9 + L/350 µm'],
                    ['label' => 'Compliance', 'value' => '100% Zero Defect Score'],
                ],
                'scope_description' => 'Complete 3D multi-coordinate volumetric alignment verification.',
                'certificate_image' => '/images/certs/zeiss-report.png',
                'certificate_pdf' => '/certificates/zeiss-report.pdf',
                'sort_order' => 3,
            ],
        ];

        foreach ($certifications as $item) {
            $work = $works[$item['work_slug']] ?? null;
            if ($work) {
                $item['work_id'] = $work->id;
            }
            unset($item['work_slug']);
            ProductCertification::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
