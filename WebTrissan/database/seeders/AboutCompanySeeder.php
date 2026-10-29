<?php

namespace Database\Seeders;

use App\Models\AboutCompany;
use Illuminate\Database\Seeder;

class AboutCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Content is taken from the company's previous website (trissan.co.id).
     */
    public function run(): void
    {
        $about = AboutCompany::firstOrNew(['id' => 1]);

        $about->hero_badge = 'Always Present Better';
        $about->hero_title = 'PT. Trissan: Solusi untuk Memenuhi Kebutuhan Pelanggan';
        $about->hero_description = "PT. Trissan didirikan dengan tujuan memberi solusi dalam pemenuhan kebutuhan pelanggan. Kami menawarkan beragam produk kelistrikan dan mekanikal dengan dukungan dan keahlian yang lebih baik.\n\nKomitmen kami adalah memberikan pelayanan purna jual yang maksimal untuk produk dan layanan yang berkualitas. PT. Trissan adalah perusahaan dengan integritas tinggi, berpengalaman, dinamis, dan berpikiran terbuka.";
        $about->hero_image = SeedMedia::image('about-hero');
        $about->image_overlay = [
            'plant_badge' => 'KANTOR PUSAT • BEKASI',
            'caption_title' => 'PT. Trissan',
            'caption_subtitle' => 'Ruko Metland Tambun, Tambun Selatan, Bekasi',
            'tolerance_chip' => '3 Cabang • 145 Karyawan',
        ];
        $about->company_profile_pdf = SeedMedia::pdf('Trissan_Company_Profile');
        $about->partners_image = SeedMedia::image('about-clients');

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
                'badge' => 'Slogan',
                'title' => 'Komitmen Perusahaan',
                'body' => 'PT. Trissan selalu hadir lebih baik: menyediakan solusi untuk memenuhi kebutuhan pelanggan dengan produk dan layanan yang berkualitas serta dukungan purna jual yang maksimal.',
                'footer_label' => 'Slogan',
                'footer_value' => 'Always Present Better',
            ],
            [
                'card_type' => 'mission',
                'badge' => 'Operasional',
                'title' => 'Misi Strategis',
                'body' => [
                    'Memberi solusi dalam pemenuhan kebutuhan pelanggan.',
                    'Menawarkan beragam produk kelistrikan dan mekanikal dengan dukungan dan keahlian yang lebih baik.',
                    'Memberikan pelayanan purna jual yang maksimal untuk produk dan layanan yang berkualitas.',
                ],
                'footer_label' => 'Fokus',
                'footer_value' => 'Kelistrikan & Mekanikal',
            ],
            [
                'card_type' => 'values',
                'badge' => 'Prioritas Kerja',
                'title' => 'Nilai Pokok Perusahaan',
                'body' => [
                    ['name' => 'Kepuasan Pelanggan', 'value' => 'Tolok ukur keberhasilan proyek'],
                    ['name' => 'Mutu & Kualitas Produk', 'value' => 'Hasil nyata yang maksimal'],
                    ['name' => 'Profesionalisme', 'value' => 'Membangun kepercayaan konsumen'],
                    ['name' => 'Integritas', 'value' => 'Konsisten secara internal & eksternal'],
                ],
                'footer_label' => 'Prinsip',
                'footer_value' => '4 Prioritas Kerja',
            ],
        ];
    }

    /**
     * Machines in the PT. Trissan Bekasi workshop (milling, cutting and CNC machine).
     */
    private function facilities(): array
    {
        return [
            [
                'facility_image' => SeedMedia::image('facility-1'),
                'facility_tag' => 'MESIN 01 • MILLING',
                'facility_title' => 'Mesin Milling',
                'facility_description' => 'Mesin milling di workshop PT. Trissan Bekasi, digunakan untuk membuat dan menyesuaikan komponen mekanikal sesuai kebutuhan proyek.',
                'spec_tag_1' => 'Workshop PT. Trissan Bekasi',
                'spec_tag_2' => 'Pemesinan komponen',
                'sort_order' => 1,
            ],
            [
                'facility_image' => SeedMedia::image('facility-2'),
                'facility_tag' => 'MESIN 02 • CUTTING',
                'facility_title' => 'Mesin Cutting',
                'facility_description' => 'Mesin potong di workshop PT. Trissan Bekasi, digunakan untuk memotong material sesuai ukuran yang dibutuhkan proyek.',
                'spec_tag_1' => 'Workshop PT. Trissan Bekasi',
                'spec_tag_2' => 'Pemotongan material',
                'sort_order' => 2,
            ],
            [
                'facility_image' => SeedMedia::image('facility-3'),
                'facility_tag' => 'MESIN 03 • CNC',
                'facility_title' => 'Mesin CNC',
                'facility_description' => 'Mesin CNC di workshop PT. Trissan Bekasi, digunakan untuk pemesinan komponen yang dikendalikan komputer sesuai gambar kerja.',
                'spec_tag_1' => 'Workshop PT. Trissan Bekasi',
                'spec_tag_2' => 'Pemesinan terkomputerisasi',
                'sort_order' => 3,
            ],
        ];
    }

    /**
     * Company milestones by year. Only dated facts from the previous website are used; add the real company
     * history (founding year, new branches, certifications) in the CMS.
     */
    private function milestones(): array
    {
        return [
            [
                'year' => '2023',
                'title' => 'Proyek Instalasi, Konveyor, dan Perawatan Industri',
                'description' => 'Dokumentasi proyek PT. Trissan pada November 2023: fire hydrant system untuk PT Mayora Indah, instalasi water meter dan chiller duct bypass untuk PT Astra Honda Motor, serta konveyor material handling untuk PT Nihon Plast, PT Mikuni, PT Indofood CBP, dan PT Yaskawa Electrical.',
                'highlights' => ['7 proyek terdokumentasi', 'Instalasi, konveyor & maintenance'],
                'is_current' => true,
                'sort_order' => 1,
            ],
        ];
    }

    /**
     * Organization chart provided by the company: everyone with who they report to (`parent`), and
     * `side` for a staff function shown beside its manager (Finance & Accounting beside the COO).
     */
    private function orgChart(): array
    {
        $people = [
            // id, name, position, reports to, placement
            ['komisaris', 'Dr. M. Rina Ujianti, MARS', 'Commissioner', null, 'below'],
            ['direktur', 'Adikus Pringadi, S.T., S.H.', 'Director', 'komisaris', 'below'],
            ['coo', 'Herman Setiawan', 'Chief Operating Officer', 'direktur', 'below'],
            ['keuangan', 'Khansa Wijayanti', 'Finance & Accounting', 'coo', 'side'],

            ['timika', 'Moh. Arifin Tris Hidayanto', 'Head Of Representative Office Timika', 'coo', 'below'],
            ['sales', 'Tazam Wibowo', 'Sales Engineering', 'coo', 'below'],
            ['project-engineering', 'Eko Haryanto', 'Project Engineering Manager', 'coo', 'below'],
            ['back-office', 'Setyo Yanuar Prayitno', 'Back Office Manager', 'coo', 'below'],

            ['koirul', 'Koirul Anam', 'Design Engineer', 'timika', 'below'],
            ['budiarto', 'Budiarto', 'Project Leader', 'timika', 'below'],
            ['laode', 'Laode Iqmal Aztamar', 'Administrator', 'timika', 'below'],
            ['eko-nur', 'M. Eko Nur Utomo', 'Project Coordinator', 'budiarto', 'below'],
            ['randy', 'Randy', 'Project Coordinator', 'budiarto', 'below'],
            ['rio', 'Rio Sandi', 'Engineer', 'eko-nur', 'below'],
            ['oki', 'Oki Lukman', 'Engineer', 'eko-nur', 'below'],
            ['darma', 'Darma', 'Engineer', 'randy', 'below'],

            ['nurul', 'Nurul Amin', 'Research And Development', 'project-engineering', 'below'],
            ['fuad', 'Fuad Hamdhan Arbyanto', 'Production', 'project-engineering', 'below'],
            ['windy', 'Windy Adinda Putri', 'Project Engineer', 'project-engineering', 'below'],
            ['fathurian', 'Fathurian Zaen', 'Project Admin', 'project-engineering', 'below'],
            ['raka', 'Muhammad Raka Saputra', 'Research And Development', 'nurul', 'below'],
            ['purwadi', 'Purwadi', 'Production', 'fuad', 'below'],

            ['hendi', 'Hendi', 'General Affair', 'back-office', 'below'],
        ];

        return [
            'chart_revision_tag' => 'STRUKTUR ORGANISASI',
            'nodes' => collect($people)->map(fn (array $person, int $index): array => [
                'id' => $person[0],
                'name' => $person[1],
                'position' => $person[2],
                'parent_id' => $person[3],
                'placement' => $person[4],
                'sort_order' => $index + 1,
                'photo' => null,
            ])->all(),
        ];
    }
}
