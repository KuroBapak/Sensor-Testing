<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Seeder;

class GlobalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GlobalSetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PT. Apex Industrial Engineering',
                'company_tagline' => 'Industrial Engineering',
                'navbar_logo' => '/images/logo.png',
                'footer_logo' => '/images/logo-footer.png',
                'footer_description' => 'PT Apex Industrial Engineering adalah mitra terpercaya untuk solusi rekayasa presisi dan fabrikasi berstandar global. Kami melayani industri kedirgantaraan, otomotif, minyak & gas, dan manufaktur berat dengan komitmen pada kualitas, inovasi, dan kepuasan pelanggan.',
                'footer_partner_image' => '/images/partners-banner.png',
                'copyright_text' => '© '.date('Y').' PT. Apex Industrial Engineering. All rights reserved.',
                'footer_legal_links' => [
                    ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
                    ['label' => 'Terms of Service', 'url' => '/terms-of-service'],
                    ['label' => 'Quality & Safety Policy', 'url' => '/quality-safety-policy'],
                    ['label' => 'ISO 9001 / AS9100D Compliance', 'url' => '/compliance'],
                ],
                'company_stats' => [
                    ['value' => '25+', 'label_landing' => 'Tahun Pengalaman', 'label_about' => 'Tahun Dedikasi'],
                    ['value' => '99.98%', 'label_landing' => 'Presisi Toleransi', 'label_about' => 'Toleransi Presisi'],
                    ['value' => '150+', 'label_landing' => 'Insinyur Ahli', 'label_about' => 'Insinyur & Ahli'],
                    ['value' => '500+', 'label_landing' => 'Proyek Global', 'label_about' => 'Proyek Selesai'],
                ],
                'phones' => [
                    ['display_text' => '+62 (21) 5508-8990', 'tel_url' => 'tel:+62215508899', 'is_whatsapp' => false],
                    ['display_text' => '+62 812-3456-7890', 'tel_url' => 'tel:+6281234567890', 'is_whatsapp' => true],
                ],
                'primary_email' => 'contact@apex-engineering.co.id',
                'corporate_email' => 'corporate@apex-engineering.co.id',
                'factory_address' => 'Jl. Jababeka Raya Blok V No. 1-2, Jababeka V Industrial Estate, Cikarang Timur, Bekasi, Jawa Barat 17550, Indonesia',
                'facility_zone' => 'Heavy Industrial Zone',
                'maps_button_url' => 'https://maps.google.com/?q=6.315028,107.151806',
                'maps_iframe_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.123456789!2d107.151806!3d-6.315028',
                'overlay_title' => 'Apex Plant Facility 01',
                'overlay_coordinate' => '6°18\'54.1"S 107°09\'06.5"E • Jababeka V',
                'social_media' => [
                    [
                        'platform_name' => 'LinkedIn',
                        'platform_icon' => 'linkedin',
                        'handle' => '@apexindustrial',
                        'description' => 'Ikuti update profesional kami di LinkedIn',
                        'profile_url' => 'https://linkedin.com/company/apexindustrial',
                        'action_text' => 'Ikuti',
                        'sort_order' => 1,
                    ],
                    [
                        'platform_name' => 'Instagram',
                        'platform_icon' => 'instagram',
                        'handle' => '@apexindustrial.id',
                        'description' => 'Lihat portofolio visual kami',
                        'profile_url' => 'https://instagram.com/apexindustrial.id',
                        'action_text' => 'Lihat',
                        'sort_order' => 2,
                    ],
                    [
                        'platform_name' => 'YouTube',
                        'platform_icon' => 'youtube',
                        'handle' => '@ApexIndustrial',
                        'description' => 'Tonton proses produksi dan testimoni',
                        'profile_url' => 'https://youtube.com/@ApexIndustrial',
                        'action_text' => 'Tonton',
                        'sort_order' => 3,
                    ],
                ],
                'notification_email' => 'notifications@apex-engineering.co.id',
            ]
        );
    }
}
