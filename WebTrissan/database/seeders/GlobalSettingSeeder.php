<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Seeder;

class GlobalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Content is taken from the company's previous website (trissan.co.id).
     */
    public function run(): void
    {
        GlobalSetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PT. Trissan',
                'company_tagline' => 'Always Present Better',
                'navbar_logo' => SeedMedia::image('logo'),
                'footer_logo' => SeedMedia::image('logo-footer'),
                'footer_description' => 'PT. Trissan didirikan dengan tujuan memberi solusi dalam pemenuhan kebutuhan pelanggan. Kami menawarkan beragam produk kelistrikan dan mekanikal dengan dukungan dan keahlian yang lebih baik, serta berkomitmen memberikan pelayanan purna jual yang maksimal untuk produk dan layanan berkualitas.',
                'footer_partner_image' => SeedMedia::image('footer-partners'),
                'copyright_text' => '© '.date('Y').' PT. Trissan. All rights reserved.',
                'footer_legal_links' => [
                    ['label' => 'Privacy Policy', 'url' => null],
                    ['label' => 'Terms of Service', 'url' => null],
                ],
                'company_stats' => [
                    ['value' => '3', 'label_landing' => 'Kantor Cabang', 'label_about' => 'Kantor Cabang'],
                    ['value' => '145', 'label_landing' => 'Karyawan', 'label_about' => 'Tim Profesional'],
                    ['value' => '12', 'label_landing' => 'Bidang Layanan', 'label_about' => 'Layanan Utama'],
                    ['value' => '7', 'label_landing' => 'Proyek Terdokumentasi', 'label_about' => 'Proyek Terdokumentasi'],
                ],
                'phones' => [
                    ['display_text' => '(021) 88373107', 'tel_url' => 'tel:+622188373107', 'is_whatsapp' => false],
                    ['display_text' => '(021) 88321576', 'tel_url' => 'tel:+622188321576', 'is_whatsapp' => false],
                ],
                'primary_email' => 'marketing@trissan.co.id',
                'corporate_email' => null,
                'whatsapp_number' => null,
                'factory_address' => 'Ruko Metland Tambun Blok A2 No.24, Tambun Selatan, Bekasi, Jawa Barat 17510, Indonesia',
                'facility_zone' => 'Kantor Pusat Bekasi',
                'maps_button_url' => 'https://maps.google.com/?q=Ruko+Metland+Tambun+Blok+A2+No.24+Tambun+Selatan+Bekasi',
                'maps_iframe_embed_url' => 'https://maps.google.com/maps?q=Ruko+Metland+Tambun+Blok+A2+No.24+Tambun+Selatan+Bekasi&hl=id&z=15&output=embed',
                'overlay_title' => 'PT. Trissan - Kantor Pusat',
                'overlay_coordinate' => 'Ruko Metland Tambun • Bekasi, Jawa Barat',
                'social_media' => [
                    [
                        'platform_name' => 'Facebook',
                        'platform_icon' => 'facebook',
                        'handle' => null,
                        'description' => 'Ikuti kabar terbaru kami di Facebook',
                        'profile_url' => 'https://www.facebook.com/',
                        'action_text' => 'Ikuti',
                        'sort_order' => 1,
                    ],
                    [
                        'platform_name' => 'Instagram',
                        'platform_icon' => 'instagram',
                        'handle' => null,
                        'description' => 'Lihat dokumentasi proyek kami di Instagram',
                        'profile_url' => 'https://www.instagram.com/',
                        'action_text' => 'Lihat',
                        'sort_order' => 2,
                    ],
                ],
                'notification_email' => 'marketing@trissan.co.id',
            ]
        );
    }
}
