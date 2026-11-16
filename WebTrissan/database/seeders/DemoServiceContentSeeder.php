<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Fills the landing page text of the twelve services (body, process steps and FAQ) with demo copy, for the
 * development deployment, so every service has a page that passes the thin-content check and can be audited.
 *
 * The copy is generic on purpose: it makes no claims about clients, years, certificates or figures.
 * It only fills a field that is still empty, so text written in the admin panel is never overwritten, and
 * running it twice changes nothing.
 *
 * Replace this copy in the admin panel (Services) with the real text before launch.
 */
class DemoServiceContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::content() as $slug => $content) {
            $service = Service::query()->where('slug', $slug)->first();

            if ($service === null) {
                continue;
            }

            $service->fill(array_filter([
                'landing_body' => blank($service->landing_body) ? implode("\n\n", $content['body']) : null,
                'process_steps' => blank($service->process_steps) ? $content['steps'] : null,
                'faqs' => blank($service->faqs) ? $content['faqs'] : null,
            ], fn (mixed $value): bool => $value !== null))->save();
        }
    }

    /**
     * The demo landing page copy per service slug.
     *
     * @return array<string, array{body: array<int, string>, steps: array<int, array{title: string, description: string}>, faqs: array<int, array{question: string, answer: string}>}>
     */
    public static function content(): array
    {
        return [
            'mechanical-electrical' => [
                'body' => [
                    'PT. Trissan adalah kontraktor mekanikal elektrikal untuk pabrik dan fasilitas industri. Layanan Mechanical & Electrical mencakup instalasi peralatan mekanikal, perpipaan, penarikan kabel daya dan kontrol, pemasangan panel listrik, sampai pengujian sistem sebelum dioperasikan. Sisi mekanikal dan kelistrikan dikerjakan oleh satu tim, sehingga koordinasi di lapangan lebih sederhana dan jadwal proyek lebih mudah dikendalikan oleh pelanggan.',
                    'Kami bekerja berdasarkan gambar kerja dan spesifikasi dari pelanggan, lalu menyusun jadwal pekerjaan yang menyesuaikan jam operasional pabrik. Untuk pekerjaan di area produksi yang sedang berjalan, pekerjaan dibagi per tahap agar lini produksi tidak berhenti lebih lama dari yang diperlukan. Material dan komponen dipilih sesuai kebutuhan beban, kondisi lingkungan kerja, dan aturan keselamatan yang berlaku di lokasi proyek.',
                    'Setiap pekerjaan didokumentasikan dengan foto, daftar material, dan catatan pengujian, sehingga tim perawatan pelanggan dapat menelusuri kembali instalasi yang sudah terpasang. Layanan ini cocok untuk pembangunan lini produksi baru, relokasi mesin, perluasan area pabrik, maupun perbaikan instalasi lama di kawasan industri Bekasi dan sekitarnya.',
                    'Setiap proyek memiliki satu penanggung jawab lapangan yang menjadi kontak utama pelanggan. Perubahan lingkup, kendala akses, atau temuan di lokasi dibahas langsung dan dicatat, sehingga biaya dan jadwal tetap transparan bagi kedua pihak. Pelanggan juga dapat meminta kunjungan survei terlebih dahulu untuk mendapat gambaran pekerjaan, kebutuhan material, dan perkiraan waktu pengerjaan sebelum memutuskan kerja sama dengan PT. Trissan.',
                ],
                'steps' => [
                    ['title' => 'Survei dan kebutuhan', 'description' => 'Tim kami meninjau lokasi, gambar kerja, dan kapasitas yang dibutuhkan sebelum penawaran dibuat.'],
                    ['title' => 'Perencanaan dan jadwal', 'description' => 'Lingkup pekerjaan, daftar material, dan jadwal disusun bersama pelanggan agar sesuai jam operasional pabrik.'],
                    ['title' => 'Instalasi', 'description' => 'Pekerjaan mekanikal dan kelistrikan dikerjakan bertahap dengan pengawasan teknisi berpengalaman.'],
                    ['title' => 'Pengujian dan serah terima', 'description' => 'Sistem diuji bersama pelanggan, lalu dokumentasi pekerjaan diserahkan.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah PT. Trissan bisa mengerjakan mekanikal dan elektrikal sekaligus?', 'answer' => 'Bisa. Kedua bagian dikerjakan oleh satu tim sehingga koordinasi lebih mudah dan tidak perlu mengatur dua kontraktor terpisah.'],
                    ['question' => 'Apakah pekerjaan dapat dilakukan saat pabrik masih beroperasi?', 'answer' => 'Bisa untuk banyak jenis pekerjaan. Kami membagi pekerjaan per tahap dan menyesuaikannya dengan jadwal produksi agar gangguan sekecil mungkin.'],
                    ['question' => 'Wilayah mana saja yang dilayani?', 'answer' => 'Kami melayani proyek dari kantor pusat di Bekasi, serta lokasi industri lain sesuai kesepakatan.'],
                ],
            ],
            'digital-automation' => [
                'body' => [
                    'Layanan Digital Automation & Controlling dari PT. Trissan membantu pabrik mengotomasi proses produksi dengan sistem kendali yang andal. Sebagai penyedia jasa otomasi industri, kami merancang dan memprogram PLC, HMI, dan sistem SCADA, memasang sensor serta aktuator, dan mengintegrasikan mesin yang sudah ada agar dapat dipantau dari satu layar. Tujuannya sederhana: proses lebih stabil, operator lebih mudah bekerja, dan data produksi tersedia saat dibutuhkan.',
                    'Kami dapat mengerjakan mesin baru maupun retrofit untuk mesin lama yang masih bagus secara mekanik tetapi sistem kendalinya sudah usang. Pemrograman disusun dengan struktur yang rapi dan dokumentasi yang jelas, sehingga teknisi pelanggan dapat memahami logikanya dan melakukan perubahan kecil tanpa harus bergantung pada kami setiap saat.',
                    'Sebelum diserahkan, sistem diuji dengan skenario kerja yang nyata, termasuk kondisi darurat dan pemulihan setelah listrik padam. Operator mendapat pelatihan singkat di lokasi, dan kami tetap tersedia untuk dukungan setelah mesin beroperasi. Layanan ini sering digabung dengan pembuatan panel listrik dan layanan material handling agar seluruh lini bekerja sebagai satu sistem.',
                    'Banyak pabrik memulai otomasi dari satu mesin atau satu proses yang paling sering bermasalah, lalu melanjutkan ke bagian lain setelah hasilnya terlihat. Pendekatan bertahap seperti ini menjaga biaya tetap terkendali dan memberi waktu bagi tim produksi untuk terbiasa. Kami membantu menyusun urutan prioritas otomasi, perkiraan manfaatnya, dan kebutuhan perangkat keras, sehingga keputusan investasi dapat dibuat dengan dasar yang jelas dan masuk akal.',
                ],
                'steps' => [
                    ['title' => 'Analisis proses', 'description' => 'Kami memahami alur produksi, titik masalah, dan data apa yang perlu dikendalikan atau dipantau.'],
                    ['title' => 'Desain sistem kendali', 'description' => 'Arsitektur PLC, HMI, dan jaringan dirancang bersama daftar input dan output yang disepakati.'],
                    ['title' => 'Pemrograman dan perakitan', 'description' => 'Program dibuat dan panel dirakit, lalu diuji lebih dulu di workshop sebelum dikirim.'],
                    ['title' => 'Commissioning dan pelatihan', 'description' => 'Sistem dipasang dan dijalankan di lokasi, kemudian operator dilatih menggunakan HMI.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah mesin lama bisa diotomasi ulang?', 'answer' => 'Sering kali bisa. Kami menilai kondisi mekanik dan kelistrikan mesin terlebih dahulu, lalu menyarankan retrofit yang paling masuk akal dari sisi biaya.'],
                    ['question' => 'Merek PLC dan HMI apa yang digunakan?', 'answer' => 'Kami menyesuaikan dengan standar pelanggan agar suku cadang dan perawatannya mudah. Merek dibahas pada tahap perencanaan.'],
                    ['question' => 'Apakah ada dukungan setelah sistem berjalan?', 'answer' => 'Ada. Kami dapat membantu perbaikan program, penambahan fitur, dan penelusuran masalah sesuai kesepakatan layanan.'],
                ],
            ],
            'jigs-fixtures' => [
                'body' => [
                    'Jig dan fixture menentukan seberapa konsisten sebuah komponen dikerjakan, diukur, dan dirakit. PT. Trissan membuat jig presisi dan clamping fixture untuk proses pemesinan, pengelasan, perakitan, dan inspeksi sesuai gambar dan kebutuhan lini produksi pelanggan. Dengan perkakas bantu yang tepat, waktu penyetelan berkurang, hasil lebih seragam, dan operator tidak perlu mengatur posisi benda kerja secara manual setiap kali.',
                    'Pekerjaan dimulai dari memahami benda kerja, toleransi yang dibutuhkan, dan cara operator menggunakannya. Dari situ kami merancang titik tumpu, penjepit, dan pelindung yang mudah dipakai dan aman. Pemilihan material disesuaikan dengan beban dan frekuensi pemakaian, misalnya baja karbon, aluminium, atau bahan lain yang cocok untuk lingkungan kerja pelanggan.',
                    'Fixture dibuat di workshop kami dan diperiksa dimensinya sebelum dikirim. Jika diperlukan, kami menyediakan uji coba bersama benda kerja asli dan melakukan penyesuaian kecil setelah pemakaian awal. Layanan ini cocok untuk industri otomotif, komponen mesin, peralatan listrik, dan manufaktur umum yang membutuhkan hasil berulang dengan kualitas yang terjaga.',
                    'Selain pembuatan baru, kami menerima permintaan perbaikan dan penyesuaian jig yang aus karena pemakaian harian. Permukaan tumpu yang sudah berubah ukuran dapat dikerjakan ulang, penjepit yang longgar dapat diganti, dan bagian yang berkarat dapat dilapis ulang. Dengan perawatan seperti ini, perkakas bantu tetap akurat lebih lama dan pelanggan tidak perlu mengganti seluruh fixture setiap kali ada gangguan kecil pada proses produksi mereka.',
                ],
                'steps' => [
                    ['title' => 'Kajian benda kerja', 'description' => 'Gambar, toleransi, dan cara kerja operator ditinjau untuk menentukan konsep jig atau fixture.'],
                    ['title' => 'Desain dan persetujuan', 'description' => 'Desain gambar kerja diajukan kepada pelanggan untuk disetujui sebelum produksi dimulai.'],
                    ['title' => 'Fabrikasi dan pemesinan', 'description' => 'Komponen dibuat dan dirakit di workshop, kemudian dimensi pentingnya diperiksa.'],
                    ['title' => 'Uji coba dan penyesuaian', 'description' => 'Fixture dicoba dengan benda kerja asli dan disesuaikan bila ada penyimpangan.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah desain jig bisa dibuat dari sampel benda kerja?', 'answer' => 'Bisa, selama dimensi pentingnya dapat diukur. Gambar kerja dibuat terlebih dahulu dan disetujui sebelum fabrikasi.'],
                    ['question' => 'Berapa lama pembuatan satu fixture?', 'answer' => 'Tergantung tingkat kerumitan dan jumlahnya. Estimasi waktu diberikan setelah gambar dan kebutuhan dikaji.'],
                    ['question' => 'Apakah tersedia perbaikan atau modifikasi fixture lama?', 'answer' => 'Tersedia. Kami dapat memperbaiki, menyesuaikan, atau membuat ulang fixture yang aus atau sudah tidak sesuai produk terbaru.'],
                ],
            ],
            'unload-robot-chucking' => [
                'body' => [
                    'Sistem unload robot chucking dipakai untuk memindahkan benda kerja masuk dan keluar dari mesin secara otomatis, misalnya mesin bubut atau mesin pemesinan lain. PT. Trissan merancang dan memasang sistem chucking otomatis untuk robot industri, termasuk gripper, penjepit, sensor posisi, dan logika kendali yang menghubungkan robot dengan mesin. Hasilnya, operator tidak perlu memuat dan melepas benda kerja secara manual pada setiap siklus.',
                    'Otomasi seperti ini menjaga waktu siklus tetap konsisten, mengurangi kelelahan operator, dan membuat mesin dapat bekerja lebih lama dengan pengawasan yang lebih ringan. Kami memperhatikan keamanan sejak awal, mulai dari pagar pengaman, sensor area, sampai tombol henti darurat, sehingga robot dan operator dapat bekerja berdampingan dengan aman.',
                    'Rancangan gripper disesuaikan dengan bentuk dan berat benda kerja pelanggan, dan dapat diubah bila nantinya ada varian produk baru. Seluruh sistem diuji di workshop sebelum dipasang, lalu diuji kembali di lokasi bersama tim produksi. Layanan ini sering dipadukan dengan jasa otomasi industri dan pembuatan panel kendali untuk satu paket pekerjaan yang utuh.',
                    'Sebelum proyek dimulai, kami biasanya menghitung waktu siklus dan gambaran penghematan tenaga kerja agar pelanggan bisa menilai manfaat otomasi secara terukur. Setelah sistem berjalan, kami dapat membantu penyesuaian program ketika ada produk baru, perawatan gripper, dan pelatihan ulang operator. Dukungan lanjutan ini penting agar investasi robot tetap bermanfaat dan tidak berhenti di tengah jalan karena kendala kecil di lapangan.',
                ],
                'steps' => [
                    ['title' => 'Kajian proses mesin', 'description' => 'Siklus mesin, bentuk benda kerja, dan jangkauan robot dipelajari bersama tim produksi.'],
                    ['title' => 'Desain gripper dan layout', 'description' => 'Gripper, penjepit, dan tata letak robot dirancang beserta pengamannya.'],
                    ['title' => 'Pemasangan dan integrasi', 'description' => 'Robot, sensor, dan sinyal mesin dihubungkan lalu diprogram sesuai urutan kerja.'],
                    ['title' => 'Uji siklus dan pelatihan', 'description' => 'Siklus kerja diuji berulang, kemudian operator dan teknisi pelanggan mendapat pelatihan.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah sistem ini cocok untuk produksi bervariasi?', 'answer' => 'Cocok, selama gripper dan program dirancang agar dapat disesuaikan. Hal ini dibahas sejak tahap desain.'],
                    ['question' => 'Bagaimana keselamatan operator dijaga?', 'answer' => 'Kami memasang pagar pengaman, sensor area, dan tombol henti darurat, lalu menguji fungsi pengamannya sebelum serah terima.'],
                    ['question' => 'Apakah robot dari merek mana pun bisa digunakan?', 'answer' => 'Umumnya bisa. Kami menyesuaikan dengan robot yang sudah dimiliki pelanggan atau merekomendasikan yang sesuai kebutuhan.'],
                ],
            ],
            'material-handling' => [
                'body' => [
                    'Material handling adalah urat nadi logistik di dalam pabrik. PT. Trissan merancang, membuat, dan memasang konveyor serta sistem material handling untuk memindahkan bahan baku, produk setengah jadi, dan barang jadi antar proses dengan lebih cepat dan teratur. Pilihannya meliputi konveyor rol, sabuk, rantai, dan pengering, serta modifikasi dan perawatan konveyor yang sudah terpasang.',
                    'Setiap rancangan dimulai dari alur barang, dimensi, berat, dan kecepatan yang dibutuhkan. Kami menggabungkan struktur mekanik, penggerak, dan kendali listrik dalam satu paket, sehingga konveyor siap berjalan dengan sensor dan panel yang sesuai. Untuk lini yang membutuhkan otomasi, konveyor dapat dihubungkan ke PLC agar bekerja selaras dengan mesin di sekitarnya.',
                    'Selain pekerjaan baru, kami menangani perawatan berkala seperti penggantian rol, rantai, dan bantalan, serta perbaikan struktur yang mulai aus. Perawatan terjadwal membantu mencegah mesin berhenti mendadak. Layanan material handling ini dikerjakan untuk pabrik makanan, otomotif, manufaktur umum, dan fasilitas industri lain di wilayah layanan kami.',
                    'Dalam merancang konveyor, kami memperhatikan juga hal-hal kecil yang sering terlewat, seperti tinggi kerja operator, akses perawatan, pelindung bagian berputar, dan kemudahan pembersihan. Hal ini terutama penting untuk pabrik makanan dan produk yang menuntut kebersihan tinggi. Rancangan yang mempertimbangkan perawatan sejak awal membuat biaya operasional lebih rendah dan umur peralatan lebih panjang bagi pelanggan di berbagai jenis industri. Pelanggan dapat meminta penawaran terpisah untuk konveyor baru dan perawatan, atau menggabungkannya dalam satu paket yang lebih ringkas.',
                ],
                'steps' => [
                    ['title' => 'Pemetaan alur barang', 'description' => 'Alur material, dimensi, berat, dan target kecepatan dikumpulkan dari lapangan.'],
                    ['title' => 'Desain konveyor', 'description' => 'Jenis konveyor, penggerak, dan sistem kendali ditentukan beserta gambar kerjanya.'],
                    ['title' => 'Fabrikasi dan pemasangan', 'description' => 'Struktur dibuat di workshop, kemudian dipasang dan disejajarkan di lokasi.'],
                    ['title' => 'Uji jalan dan perawatan', 'description' => 'Konveyor diuji dengan beban nyata dan jadwal perawatan berkala disepakati.'],
                ],
                'faqs' => [
                    ['question' => 'Jenis konveyor apa saja yang dapat dibuat?', 'answer' => 'Kami dapat membuat konveyor rol, sabuk, rantai, dan jenis khusus sesuai bentuk serta berat barang yang dipindahkan.'],
                    ['question' => 'Apakah konveyor lama bisa dimodifikasi?', 'answer' => 'Bisa. Kami dapat memperpanjang, mengganti penggerak, atau menambah sensor dan kendali pada konveyor yang sudah ada.'],
                    ['question' => 'Apakah tersedia kontrak perawatan berkala?', 'answer' => 'Tersedia. Jadwal dan lingkup perawatan disusun sesuai jam kerja dan kondisi peralatan pelanggan.'],
                ],
            ],
            'distribution-panel' => [
                'body' => [
                    'PT. Trissan merakit panel listrik industri, mulai dari panel distribusi, panel kontrol motor, kapasitor bank, sampai panel PLC dan HMI. Panel dirancang berdasarkan single line diagram dan kebutuhan beban pelanggan, dengan pemilihan komponen yang sesuai kapasitas, tingkat hubung singkat, dan kondisi lingkungan, misalnya debu, panas, atau kelembapan di area pabrik.',
                    'Perakitan dilakukan di workshop dengan pengkabelan yang rapi, penandaan kabel yang jelas, dan pengujian fungsi sebelum panel dikirim. Rapi dan terdokumentasi berarti teknisi perawatan dapat menelusuri rangkaian dengan cepat ketika terjadi gangguan. Untuk panel kendali, kami juga memprogram PLC dan menyusun tampilan HMI sesuai cara operator bekerja.',
                    'Kapasitor bank membantu memperbaiki faktor daya sehingga beban jaringan listrik pabrik lebih efisien dan terhindar dari denda daya reaktif. Kami menghitung kebutuhan kompensasi dari data beban, lalu merakit dan memasang panelnya. Semua panel diserahkan bersama gambar akhir, daftar komponen, dan catatan pengujian untuk arsip pelanggan.',
                    'Kami juga melayani modifikasi dan perbaikan panel lama, misalnya penambahan feeder, penggantian komponen yang sudah usang, atau perapihan pengkabelan yang berantakan. Pekerjaan semacam ini dapat direncanakan pada waktu pemeliharaan pabrik agar tidak mengganggu produksi. Bila panel lama belum memiliki gambar, tim kami dapat menelusuri rangkaiannya dan membuat gambar akhir yang baru, sehingga perawatan selanjutnya menjadi jauh lebih mudah dilakukan oleh teknisi pelanggan. Pelanggan dapat meminta penawaran terpisah untuk panel baru dan modifikasi, atau menggabungkannya dalam satu paket pekerjaan kelistrikan yang lebih ringkas.',
                ],
                'steps' => [
                    ['title' => 'Data beban dan kebutuhan', 'description' => 'Kapasitas, jenis beban, dan kondisi lokasi dikumpulkan untuk menentukan spesifikasi panel.'],
                    ['title' => 'Desain dan gambar panel', 'description' => 'Single line diagram, tata letak, dan daftar komponen disusun dan disetujui pelanggan.'],
                    ['title' => 'Perakitan dan pengujian', 'description' => 'Panel dirakit di workshop, kemudian diuji fungsi dan keamanannya sebelum dikirim.'],
                    ['title' => 'Pemasangan dan dokumentasi', 'description' => 'Panel dipasang dan dikomisioning di lokasi, lalu gambar akhir diserahkan.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah PT. Trissan menerima pesanan panel sesuai gambar pelanggan?', 'answer' => 'Menerima. Kami dapat merakit sesuai gambar dan spesifikasi pelanggan, atau membantu menyusun desain dari kebutuhan beban.'],
                    ['question' => 'Apa fungsi kapasitor bank?', 'answer' => 'Kapasitor bank memperbaiki faktor daya sehingga pemakaian listrik lebih efisien dan risiko denda daya reaktif berkurang.'],
                    ['question' => 'Apakah panel dilengkapi dokumentasi?', 'answer' => 'Ya. Setiap panel diserahkan dengan gambar akhir, daftar komponen, dan catatan pengujian.'],
                ],
            ],
            'electrical-installation' => [
                'body' => [
                    'Layanan Electrical Installation & Machine Maintenance dari PT. Trissan mencakup instalasi kelistrikan pabrik serta perawatan mesin dan peralatan listrik. Pekerjaan instalasi meliputi penarikan kabel, pemasangan tray dan conduit, grounding, pencahayaan industri, serta penyambungan mesin ke panel. Semuanya dikerjakan sesuai gambar, standar keselamatan, dan jadwal yang disepakati dengan pelanggan.',
                    'Untuk perawatan, kami melakukan inspeksi berkala, pengencangan koneksi, pemeriksaan suhu panel, pengujian isolasi, dan penggantian komponen yang mulai aus. Perawatan preventif mencegah kerusakan kecil berkembang menjadi mesin berhenti total, dan biasanya jauh lebih murah daripada perbaikan setelah rusak. Laporan hasil inspeksi diberikan setelah setiap kunjungan.',
                    'Bila terjadi gangguan, tim teknisi kami membantu menelusuri penyebabnya, memperbaiki, dan memberi saran agar masalah yang sama tidak berulang. Layanan ini dapat berupa pekerjaan sekali jalan maupun kontrak perawatan rutin, dan dapat mencakup mesin produksi, motor, panel, serta sistem pendukung seperti kompresor dan pompa di fasilitas industri.',
                    'Keselamatan menjadi bagian tetap dari setiap pekerjaan kelistrikan. Teknisi kami menerapkan prosedur pengamanan energi, memakai alat pelindung diri, dan memeriksa ulang sebelum sistem dialiri listrik kembali. Kami juga dapat memberi saran perbaikan sederhana yang meningkatkan keandalan, seperti penataan kabel, penambahan label, dan perbaikan grounding, sehingga risiko gangguan dan bahaya kerja di area pabrik dapat ditekan secara bertahap dari waktu ke waktu. Pelanggan dapat meminta penawaran terpisah untuk instalasi baru dan perawatan berkala, atau menggabungkannya dalam satu kesepakatan layanan yang lebih ringkas.',
                ],
                'steps' => [
                    ['title' => 'Inspeksi awal', 'description' => 'Kondisi instalasi dan mesin diperiksa untuk menentukan lingkup pekerjaan atau perawatan.'],
                    ['title' => 'Rencana kerja', 'description' => 'Jadwal, material, dan prosedur keselamatan disepakati sebelum pekerjaan dimulai.'],
                    ['title' => 'Pelaksanaan', 'description' => 'Instalasi atau perawatan dikerjakan oleh teknisi sesuai rencana dan standar keselamatan.'],
                    ['title' => 'Laporan dan tindak lanjut', 'description' => 'Hasil pekerjaan dilaporkan beserta rekomendasi perawatan berikutnya.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah perawatan bisa dijadwalkan rutin?', 'answer' => 'Bisa. Kontrak perawatan berkala dapat disusun bulanan, triwulanan, atau sesuai kebutuhan pabrik.'],
                    ['question' => 'Apakah tersedia layanan perbaikan saat ada gangguan?', 'answer' => 'Ada. Hubungi kami dengan penjelasan masalahnya dan tim akan menjadwalkan teknisi secepat mungkin sesuai wilayah.'],
                    ['question' => 'Apakah hasil inspeksi dilaporkan?', 'answer' => 'Ya. Setiap kunjungan menghasilkan laporan temuan beserta rekomendasi tindak lanjut.'],
                ],
            ],
            'electrical-equipment-supplier' => [
                'body' => [
                    'PT. Trissan juga bertindak sebagai supplier peralatan kelistrikan industri untuk pabrik dan kontraktor. Kami membantu pengadaan komponen seperti kabel, circuit breaker, kontaktor, relay, inverter, sensor, push button, dan perlengkapan panel dari berbagai merek. Pelanggan cukup menyampaikan daftar kebutuhan atau spesifikasi, dan kami membantu mencarikan barang yang sesuai.',
                    'Keunggulan membeli dari kontraktor yang juga mengerjakan instalasi adalah pemahaman akan kebutuhan lapangan. Bila spesifikasi kurang jelas, tim teknis kami dapat membantu memeriksa kecocokan rating, ukuran, dan kompatibilitas sebelum pesanan dibuat, sehingga risiko salah beli dapat ditekan. Kami juga dapat menyarankan padanan merek ketika barang tertentu sulit didapat.',
                    'Pengadaan dapat dilakukan satuan untuk kebutuhan perawatan maupun dalam jumlah besar untuk proyek. Waktu pengiriman, harga, dan ketersediaan dikonfirmasi lebih dulu pada penawaran. Layanan supplier ini sering digabung dengan pekerjaan panel dan instalasi agar seluruh kebutuhan proyek terkoordinasi dalam satu penyedia di wilayah layanan kami.',
                    'Pelanggan yang memiliki banyak titik perawatan dapat meminta daftar suku cadang kritis yang sebaiknya selalu tersedia, misalnya sekering, kontaktor, dan relay yang sering diganti. Dengan stok cadangan yang tepat, mesin tidak perlu menunggu pengiriman berhari-hari saat terjadi kerusakan. Kami membantu menyusun daftar tersebut berdasarkan riwayat gangguan dan jenis peralatan, lalu memperbaruinya secara berkala sesuai perubahan kondisi di pabrik pelanggan. Pelanggan dapat meminta penawaran terpisah untuk kebutuhan proyek dan kebutuhan perawatan, atau menggabungkannya dalam satu kesepakatan pengadaan berkala yang lebih ringkas.',
                ],
                'steps' => [
                    ['title' => 'Daftar kebutuhan', 'description' => 'Pelanggan mengirim daftar barang atau spesifikasi yang dibutuhkan.'],
                    ['title' => 'Pengecekan spesifikasi', 'description' => 'Tim teknis memeriksa kecocokan rating dan ukuran sebelum penawaran dibuat.'],
                    ['title' => 'Penawaran dan konfirmasi', 'description' => 'Harga, ketersediaan, dan waktu pengiriman dikonfirmasi secara tertulis.'],
                    ['title' => 'Pengiriman', 'description' => 'Barang dikirim ke lokasi pelanggan dan diperiksa bersama saat diterima.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah bisa memesan dalam jumlah kecil?', 'answer' => 'Bisa. Pengadaan tersedia untuk kebutuhan perawatan satuan maupun jumlah besar untuk proyek.'],
                    ['question' => 'Bagaimana bila barang yang diminta tidak tersedia?', 'answer' => 'Kami menawarkan padanan merek atau tipe yang setara setelah memeriksa kecocokannya dengan pelanggan.'],
                    ['question' => 'Apakah ada bantuan memeriksa spesifikasi?', 'answer' => 'Ada. Tim teknis membantu memeriksa rating dan kompatibilitas sebelum pesanan dibuat.'],
                ],
            ],
            'grease-system' => [
                'body' => [
                    'Sistem pelumasan otomatis atau auto grease system membantu alat berat dan mesin produksi mendapat gemuk pada titik-titik pelumasan secara teratur tanpa harus dilakukan manual. PT. Trissan merancang dan memasang sistem grease otomatis, termasuk untuk bagian loader, lengkap dengan pompa, pembagi, selang, dan pengatur waktu. Pelumasan yang teratur memperpanjang umur bantalan dan pin, serta mengurangi waktu berhenti akibat keausan.',
                    'Pelumasan manual sering terlewat, terlalu sedikit, atau berlebihan, apalagi pada titik yang sulit dijangkau. Dengan sistem otomatis, jumlah dan interval gemuk dapat diatur sesuai kebutuhan masing-masing titik. Teknisi juga tidak perlu naik atau masuk ke area berbahaya hanya untuk memberi gemuk, sehingga pekerjaan perawatan menjadi lebih aman.',
                    'Kami memulai dengan memetakan titik pelumasan dan memilih komponen yang sesuai dengan tekanan serta jenis gemuk yang dipakai. Setelah pemasangan, sistem diuji dan pelanggan menerima panduan pengisian ulang serta pemeriksaan rutin. Layanan ini cocok untuk loader, konveyor, dan mesin produksi yang bekerja terus menerus di lingkungan berat.',
                    'Pemasangan sistem grease otomatis juga bisa menjadi bagian dari program perawatan yang lebih luas. Data pelumasan yang teratur memudahkan tim perawatan mengenali titik yang bermasalah, misalnya yang terlalu cepat menghabiskan gemuk. Kami dapat membantu memantau hasil beberapa minggu pertama dan menyesuaikan pengaturan interval, sehingga pelumasan benar-benar sesuai dengan kondisi kerja alat di lapangan dan bukan sekadar mengikuti pengaturan awal dari pabrikan.',
                ],
                'steps' => [
                    ['title' => 'Pemetaan titik pelumasan', 'description' => 'Semua titik gemuk, jarak, dan jenis pelumas yang dipakai didata di lapangan.'],
                    ['title' => 'Desain sistem', 'description' => 'Pompa, pembagi, dan jalur selang dipilih sesuai tekanan dan jumlah titik.'],
                    ['title' => 'Pemasangan', 'description' => 'Komponen dipasang dengan perlindungan selang dan jalur yang aman dari benturan.'],
                    ['title' => 'Uji dan panduan', 'description' => 'Aliran gemuk diuji, lalu pelanggan menerima panduan pengisian dan pemeriksaan rutin.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah sistem ini bisa dipasang pada alat yang sudah ada?', 'answer' => 'Umumnya bisa. Kami memeriksa titik pelumasan yang ada dan merancang jalur yang sesuai.'],
                    ['question' => 'Seberapa sering gemuk harus diisi ulang?', 'answer' => 'Tergantung kapasitas tangki dan pengaturan interval. Panduan pengisian diberikan setelah sistem dipasang.'],
                    ['question' => 'Apa keuntungan dibanding pelumasan manual?', 'answer' => 'Pelumasan lebih teratur dan terukur, titik sulit terjangkau tetap terlayani, dan risiko kerja teknisi berkurang.'],
                ],
            ],
            'general-supplier' => [
                'body' => [
                    'Selain pekerjaan teknis, PT. Trissan melayani pengadaan sebagai general supplier mesin industri, suku cadang, aksesori, peralatan, material konstruksi, dan barang kebutuhan pabrik lainnya. Pelanggan yang membutuhkan banyak jenis barang dapat menyampaikan satu daftar kebutuhan, dan kami membantu mengumpulkan serta mengirimkannya sehingga proses pembelian lebih sederhana dan tertib administrasi.',
                    'Karena kami juga mengerjakan proyek mekanikal dan elektrikal, pemilihan barang tidak hanya berdasarkan harga. Tim kami mempertimbangkan kecocokan dengan mesin, ketersediaan suku cadang jangka panjang, dan kebutuhan perawatan. Jika spesifikasi masih kurang jelas, kami membantu memeriksanya terlebih dahulu agar barang yang datang benar-benar dapat dipakai.',
                    'Layanan ini mencakup pengadaan rutin untuk kebutuhan perawatan maupun pengadaan untuk proyek tertentu. Penawaran memuat spesifikasi, harga, dan estimasi pengiriman secara tertulis, dan barang diperiksa bersama saat diterima. Kami melayani pelanggan industri dari kantor pusat di Bekasi, termasuk pengiriman ke lokasi proyek di sekitarnya.',
                    'Untuk pelanggan proyek, kami dapat mengelola pengadaan bertahap sesuai jadwal pekerjaan, sehingga barang datang saat dibutuhkan dan tidak menumpuk di gudang. Dokumen seperti penawaran, surat jalan, dan berita acara penerimaan disiapkan rapi untuk memudahkan penagihan dan pembukuan. Jika ada barang yang tidak sesuai saat diterima, kami membantu proses penukaran atau penggantian sesuai ketentuan yang telah disepakati sebelumnya oleh kedua pihak. Pelanggan dapat meminta penawaran terpisah untuk setiap proyek, atau menggabungkan seluruh kebutuhan pengadaan dalam satu kesepakatan berkala yang lebih ringkas.',
                ],
                'steps' => [
                    ['title' => 'Permintaan barang', 'description' => 'Pelanggan menyampaikan daftar barang, jumlah, dan spesifikasi yang dibutuhkan.'],
                    ['title' => 'Pencarian dan pemeriksaan', 'description' => 'Barang dicarikan dan kecocokannya dengan mesin atau proyek diperiksa.'],
                    ['title' => 'Penawaran tertulis', 'description' => 'Penawaran memuat spesifikasi, harga, dan estimasi pengiriman.'],
                    ['title' => 'Pengiriman dan serah terima', 'description' => 'Barang dikirim ke lokasi dan diperiksa bersama pelanggan saat diterima.'],
                ],
                'faqs' => [
                    ['question' => 'Barang apa saja yang dapat dipasok?', 'answer' => 'Mesin industri, suku cadang, aksesori, peralatan, material konstruksi, dan kebutuhan pabrik lainnya sesuai permintaan.'],
                    ['question' => 'Apakah bisa mengirim ke luar Bekasi?', 'answer' => 'Bisa. Pengiriman ke lokasi proyek dan pabrik di wilayah lain diatur pada saat penawaran.'],
                    ['question' => 'Apakah tersedia pembelian rutin?', 'answer' => 'Tersedia. Pengadaan berkala untuk kebutuhan perawatan dapat disepakati dengan jadwal yang tetap.'],
                ],
            ],
            'special-construction' => [
                'body' => [
                    'Selain pekerjaan mekanikal dan elektrikal, PT. Trissan menangani konstruksi khusus untuk kebutuhan industri yang tidak termasuk pekerjaan bangunan biasa. Contohnya struktur penyangga mesin, platform dan tangga kerja, rangka baja, pondasi mesin sederhana, serta pekerjaan sipil pendukung di area pabrik. Pekerjaan ini sering menjadi bagian dari proyek pemasangan lini produksi atau relokasi mesin.',
                    'Kami mengerjakannya bersama tim mekanikal dan elektrikal, sehingga tidak ada celah koordinasi antara pekerjaan struktur dan pemasangan peralatan di atasnya. Gambar kerja dan perhitungan beban disiapkan sesuai kebutuhan, lalu pekerjaan dilakukan dengan memperhatikan keselamatan kerja di area pabrik yang masih aktif, termasuk izin kerja, alat pelindung diri, dan pembatasan area.',
                    'Hasil pekerjaan diperiksa bersama pelanggan dan diserahkan dengan dokumentasi foto serta catatan material. Layanan konstruksi khusus ini cocok untuk perluasan area produksi, pemasangan mesin baru yang membutuhkan struktur tambahan, dan perbaikan konstruksi pendukung yang sudah berumur. Jangkauan layanan kami mencakup Bekasi dan sekitarnya.',
                    'Keselamatan menjadi perhatian utama dalam pekerjaan konstruksi khusus, terutama pekerjaan di ketinggian dan pekerjaan las. Tim kami memakai alat pelindung diri yang sesuai, memasang rambu dan pembatas area, dan memeriksa peralatan kerja sebelum digunakan. Setiap pekerjaan berisiko tinggi didahului pembahasan singkat dengan pengawas pelanggan agar semua pihak memahami batas area, urutan kerja, dan langkah yang harus dilakukan bila terjadi keadaan darurat. Pelanggan dapat meminta penawaran terpisah untuk setiap pekerjaan, atau menggabungkannya dengan pemasangan mesin dalam satu paket proyek yang lebih ringkas.',
                ],
                'steps' => [
                    ['title' => 'Survei lokasi', 'description' => 'Kondisi area, beban yang dipikul, dan akses kerja ditinjau terlebih dahulu.'],
                    ['title' => 'Gambar kerja dan rencana', 'description' => 'Gambar, material, dan rencana keselamatan kerja disusun dan disetujui pelanggan.'],
                    ['title' => 'Pelaksanaan konstruksi', 'description' => 'Pekerjaan dilakukan bertahap dengan pembatasan area dan pengawasan di lapangan.'],
                    ['title' => 'Pemeriksaan dan serah terima', 'description' => 'Hasil diperiksa bersama pelanggan dan dokumentasi pekerjaan diserahkan.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah pekerjaan konstruksi bisa dilakukan di area pabrik yang aktif?', 'answer' => 'Bisa. Kami menyusun izin kerja, pembatasan area, dan jadwal agar aktivitas produksi tetap terlindungi.'],
                    ['question' => 'Apakah gambar kerja disiapkan oleh PT. Trissan?', 'answer' => 'Dapat disiapkan oleh kami atau mengikuti gambar dari pelanggan, sesuai kesepakatan lingkup pekerjaan.'],
                    ['question' => 'Apakah termasuk pekerjaan sipil besar?', 'answer' => 'Fokus kami pada konstruksi khusus pendukung industri. Pekerjaan sipil besar dibahas per proyek.'],
                ],
            ],
            'labor-provision' => [
                'body' => [
                    'PT. Trissan menyediakan tenaga kerja terampil untuk kebutuhan manufaktur dan proyek industri, termasuk tenaga paruh waktu dalam negeri. Layanan ini mencakup seleksi, penempatan, dan penyediaan tenaga kerja, misalnya teknisi mekanikal, teknisi listrik, operator, tenaga las, dan tenaga pendukung produksi. Pelanggan dapat menambah tim saat beban kerja naik tanpa harus melalui proses perekrutan yang panjang.',
                    'Proses seleksi dimulai dari kebutuhan posisi dan kemampuan yang dicari. Kandidat disaring melalui pemeriksaan berkas, wawancara, dan penilaian keterampilan yang sesuai dengan bidangnya. Kami menjelaskan aturan keselamatan dan tata tertib sebelum tenaga kerja ditempatkan, sehingga mereka siap bekerja dengan standar yang sama dengan karyawan di lokasi pelanggan.',
                    'Selama penempatan, kami memantau kehadiran dan kinerja, serta mengganti tenaga kerja bila ada ketidakcocokan sesuai kesepakatan. Administrasi dan kewajiban ketenagakerjaan diatur jelas dalam perjanjian kerja sama. Layanan ini cocok untuk proyek musiman, pekerjaan instalasi besar, atau kebutuhan tambahan tenaga saat perawatan besar pabrik berlangsung.',
                    'Pelanggan dapat memulai dari jumlah kecil untuk menguji kecocokan, kemudian menambah tenaga kerja bila hasilnya sesuai harapan. Kami juga dapat menyiapkan tim yang sudah terbiasa bekerja bersama, misalnya satu regu instalasi lengkap dengan pengawasnya, agar koordinasi di lapangan berjalan lebih cepat. Dengan cara ini, kebutuhan tenaga musiman atau proyek jangka pendek dapat dipenuhi tanpa menambah beban administrasi tetap bagi perusahaan pelanggan. Pelanggan dapat meminta penawaran terpisah untuk setiap proyek, atau menggabungkan seluruh kebutuhan tenaga kerja dalam satu perjanjian kerja sama yang lebih ringkas.',
                ],
                'steps' => [
                    ['title' => 'Kebutuhan posisi', 'description' => 'Pelanggan menyampaikan posisi, jumlah, keahlian, dan durasi penempatan yang dibutuhkan.'],
                    ['title' => 'Seleksi kandidat', 'description' => 'Berkas, wawancara, dan penilaian keterampilan dilakukan sesuai bidang kerja.'],
                    ['title' => 'Penempatan dan pembekalan', 'description' => 'Tenaga kerja dibekali aturan keselamatan sebelum mulai bertugas di lokasi.'],
                    ['title' => 'Pemantauan', 'description' => 'Kehadiran dan kinerja dipantau dan dilaporkan sesuai kesepakatan.'],
                ],
                'faqs' => [
                    ['question' => 'Apakah tersedia tenaga kerja paruh waktu?', 'answer' => 'Tersedia. Durasi dan jam kerja disesuaikan dengan kebutuhan proyek atau produksi pelanggan.'],
                    ['question' => 'Bagaimana jika tenaga kerja tidak sesuai?', 'answer' => 'Penggantian dapat dilakukan sesuai ketentuan dalam perjanjian kerja sama.'],
                    ['question' => 'Posisi apa saja yang dapat disediakan?', 'answer' => 'Teknisi mekanikal dan listrik, operator, tenaga las, serta tenaga pendukung produksi, sesuai ketersediaan.'],
                ],
            ],
        ];
    }
}
