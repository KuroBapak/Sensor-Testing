<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The Material Symbols icons offered to editors in the admin panel, each with Indonesian search words.
 *
 * The name is what the public pages draw (see the material-icon component). Every icon already used by the seeders
 * and views is in the list, so existing content always has a matching choice.
 */
class MaterialIcons
{
    /**
     * Icon name => Indonesian description and search words.
     *
     * @var array<string, string>
     */
    public const ICONS = [
        'ac_unit' => 'Pendingin, AC, salju',
        'air' => 'Udara, angin, ventilasi',
        'analytics' => 'Analitik, statistik, grafik',
        'apartment' => 'Gedung, apartemen, kantor',
        'apps' => 'Semua, aplikasi, kotak-kotak',
        'architecture' => 'Arsitektur, desain teknik, jangka',
        'arrow_forward' => 'Panah ke kanan, lanjut',
        'arrow_outward' => 'Panah keluar, tautan keluar',
        'assignment' => 'Tugas, dokumen kerja, daftar periksa',
        'badge' => 'Tanda pengenal, tenaga kerja, karyawan',
        'bar_chart' => 'Grafik batang, data',
        'battery_charging_full' => 'Baterai, pengisian daya',
        'biotech' => 'Laboratorium, riset, sains',
        'bolt' => 'Listrik, petir, daya',
        'bookmark' => 'Penanda, tersimpan',
        'build' => 'Perbaikan, kunci inggris, servis',
        'cable' => 'Kabel, kabel listrik, koneksi',
        'calendar_today' => 'Kalender, tanggal, jadwal',
        'call' => 'Telepon, hubungi',
        'category' => 'Kategori, jenis',
        'check' => 'Centang, benar',
        'check_circle' => 'Centang bulat, selesai, disetujui',
        'chevron_left' => 'Panah kiri kecil',
        'chevron_right' => 'Panah kanan kecil',
        'close' => 'Tutup, silang',
        'conveyor_belt' => 'Konveyor, ban berjalan, material handling',
        'construction' => 'Konstruksi, proyek, pekerjaan sipil',
        'deployed_code' => 'Kotak 3D, komponen, paket',
        'description' => 'Dokumen, berkas, laporan',
        'developer_board' => 'Panel kontrol, PLC, papan sirkuit',
        'diversity_3' => 'Tim, kelompok, komunitas',
        'domain' => 'Gedung, perusahaan, kantor pusat',
        'done_all' => 'Selesai semua, centang ganda',
        'download' => 'Unduh',
        'eco' => 'Lingkungan, daun, ramah lingkungan',
        'electric_bolt' => 'Listrik, tegangan, sambaran',
        'electrical_services' => 'Instalasi listrik, steker, kabel',
        'emergency' => 'Darurat, bantuan segera',
        'emoji_events' => 'Penghargaan, piala, juara',
        'engineering' => 'Teknisi, insinyur, rekayasa',
        'error' => 'Kesalahan, peringatan',
        'expand_more' => 'Panah bawah, buka',
        'fact_check' => 'Pemeriksaan, verifikasi, audit',
        'factory' => 'Pabrik, industri, manufaktur',
        'favorite' => 'Favorit, hati, suka',
        'filter_alt_off' => 'Hapus filter',
        'fire_extinguisher' => 'Pemadam kebakaran, APAR, hydrant',
        'flight' => 'Pesawat, dirgantara, penerbangan',
        'forklift' => 'Forklift, angkut barang, gudang',
        'gavel' => 'Hukum, regulasi, palu',
        'groups' => 'Tim, banyak orang, kelompok kerja',
        'handshake' => 'Kerja sama, kemitraan, mitra',
        'handyman' => 'Tukang, perawatan, perkakas',
        'hardware' => 'Perkakas, palu, mesin',
        'headset_mic' => 'Layanan pelanggan, dukungan, bantuan',
        'health_and_safety' => 'K3, keselamatan kerja, kesehatan',
        'help' => 'Bantuan, tanya',
        'home' => 'Beranda, rumah',
        'info' => 'Informasi',
        'insights' => 'Wawasan, tren, analisis',
        'inventory' => 'Persediaan, stok, daftar barang',
        'inventory_2' => 'Barang, kotak, pengadaan, supplier',
        'language' => 'Bahasa, dunia, situs web',
        'lightbulb' => 'Lampu, ide, solusi',
        'link' => 'Tautan, link',
        'local_fire_department' => 'Api, kebakaran, proteksi kebakaran',
        'local_shipping' => 'Truk, pengiriman, logistik',
        'location_on' => 'Lokasi, alamat, peta',
        'lock' => 'Kunci, keamanan, terkunci',
        'mail' => 'Email, surat',
        'memory' => 'Chip, prosesor, elektronik',
        'military_tech' => 'Medali, lencana prestasi',
        'monitoring' => 'Pemantauan, monitor, grafik langsung',
        'oil_barrel' => 'Oli, pelumas, gemuk, drum',
        'open_in_new' => 'Buka di tab baru',
        'package_2' => 'Paket, kotak, kemasan',
        'person' => 'Orang, pengguna, kontak',
        'plumbing' => 'Perpipaan, pipa, instalasi air',
        'power' => 'Colokan, sumber daya',
        'power_settings_new' => 'Tombol daya, hidup mati',
        'precision_manufacturing' => 'Lengan robot, manufaktur presisi, mesin',
        'receipt_long' => 'Struk, penawaran, faktur',
        'router' => 'Jaringan, router, komunikasi',
        'rule' => 'Aturan, standar, prosedur',
        'schedule' => 'Jam, waktu, jadwal',
        'schema' => 'Diagram, alur, skema',
        'school' => 'Pelatihan, pendidikan, sekolah',
        'science' => 'Sains, uji, laboratorium',
        'search' => 'Cari, kaca pembesar',
        'security' => 'Keamanan, perisai, perlindungan',
        'sensors' => 'Sensor, deteksi, gelombang',
        'settings' => 'Pengaturan, roda gigi, mesin',
        'settings_suggest' => 'Otomasi, pengaturan pintar',
        'shield' => 'Perisai, perlindungan',
        'smart_toy' => 'Robot, otomasi, kendali digital',
        'solar_power' => 'Tenaga surya, panel surya',
        'speed' => 'Kecepatan, kinerja, meteran',
        'star' => 'Bintang, unggulan',
        'stars' => 'Bintang-bintang, unggulan, kualitas',
        'straighten' => 'Penggaris, ukuran, presisi, toleransi',
        'supervisor_account' => 'Pengawas, manajer',
        'support_agent' => 'Layanan pelanggan, bantuan teknis',
        'task_alt' => 'Tugas selesai, centang bulat',
        'thermostat' => 'Suhu, termostat, pendingin',
        'thumb_up' => 'Jempol, puas, bagus',
        'timer' => 'Pewaktu, durasi',
        'travel_explore' => 'Jangkauan, jelajah, wilayah',
        'trending_up' => 'Naik, pertumbuhan',
        'tune' => 'Pengaturan, spesifikasi, penyesuaian',
        'verified' => 'Terverifikasi, tanda centang berlogo',
        'verified_user' => 'Terverifikasi, perisai centang, terpercaya',
        'visibility' => 'Lihat, mata',
        'warehouse' => 'Gudang, penyimpanan',
        'warning' => 'Peringatan, hati-hati',
        'water_drop' => 'Air, tetesan, cairan',
        'workspace_premium' => 'Sertifikat, penghargaan, kualitas premium',
        'zoom_in' => 'Perbesar',
    ];

    /**
     * Whether an icon name is part of the list, so it is also part of the font the public pages load.
     */
    public static function has(string $name): bool
    {
        return array_key_exists($name, self::ICONS);
    }

    /**
     * The choices for a select field: each icon drawn next to its name and description.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_map(fn (string $name): string => self::label($name), array_combine(array_keys(self::ICONS), array_keys(self::ICONS)));
    }

    /**
     * The icons whose name or description contain the search words.
     *
     * @return array<string, string>
     */
    public static function search(string $search): array
    {
        $needle = Str::lower(trim($search));

        return array_map(
            fn (string $name): string => self::label($name),
            array_combine(
                $names = array_keys(array_filter(
                    self::ICONS,
                    fn (string $words, string $name): bool => $needle === '' || str_contains(Str::lower($name.' '.$words), $needle),
                    ARRAY_FILTER_USE_BOTH,
                )),
                $names,
            ),
        );
    }

    /**
     * The Google Fonts stylesheet URL for an icon font that holds only the listed icons (about 14 KB).
     */
    public static function fontUrl(): string
    {
        $names = array_keys(self::ICONS);
        sort($names);

        return 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names='
            .implode(',', $names).'&display=block';
    }

    /**
     * The HTML shown for an icon in the dropdown. A name that is not in the list is shown as typed.
     */
    public static function label(string $name): string
    {
        $description = self::ICONS[$name] ?? null;

        return '<span style="display:inline-flex;align-items:center;gap:.5rem">'
            .'<span class="material-symbols-outlined" data-icon="'.e($name).'" aria-hidden="true"></span>'
            .'<span>'.e($name).($description === null ? '' : ' <span style="opacity:.65">· '.e($description).'</span>').'</span>'
            .'</span>';
    }
}
