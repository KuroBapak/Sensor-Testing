# Panduan Deploy dari Nol (untuk pemula)

Panduan ini menjelaskan cara memakai deploy pack langkah demi langkah, dengan contoh aplikasi **AFM**. Untuk aplikasi lain, cukup ganti folder aplikasinya.

Yang membedakan pack ini: kamu **tidak perlu menyesuaikan file satu per satu**. Satu script (`init.php`) membaca aplikasimu, lalu memasang dan mengisi semua file deploy yang sesuai.

## 0. Gambaran besarnya dulu

Ada tiga tempat, dan masing-masing punya satu tugas:

```
  LAPTOP KAMU                 GITHUB (GHCR)               SERVER COOLIFY
  ───────────                 ─────────────               ──────────────
  Kode + Docker      push     Gudang image      pull      Menjalankan image
  "memasak" image  ───────►   (menyimpan)     ───────►    jadi website
                                                          yang bisa dibuka
```

- **Image** = paket aplikasi yang sudah jadi (kode + PHP + nginx + semua yang dibutuhkan). Bayangkan seperti file `.zip` yang tinggal dijalankan.
- **Build** = membuat image di laptop. **Push** = mengunggah image ke GitHub. **Deploy** = Coolify mengunduh image itu lalu menjalankannya.
- Server **tidak pernah** melihat kode sumber kamu. Server hanya mengunduh image.

Setiap kali kamu mengubah kode, siklusnya selalu sama: **build → push → deploy**. Script di pack ini menjalankan build + push sekaligus.

## 1. Yang harus sudah ada

- [ ] **Docker Desktop** terpasang dan sedang menyala di laptop (cek: `docker --version`)
- [ ] **PHP** di laptop (cek: `php -v`). Kalau kamu sudah menjalankan `php artisan`, berarti sudah ada. Kalau belum, lihat catatan di langkah 3.
- [ ] Akun **GitHub** (username kamu: `kurobapak`)
- [ ] Server **Coolify** sudah jalan, dan domain sudah diarahkan ke server
- [ ] Aplikasi Laravel yang mau di-deploy (contoh di sini: folder `AFM`)

## 2. Persiapan sekali saja (per laptop dan per server)

### 2a. Login Docker di laptop ke GitHub

1. Buka GitHub → klik foto profil → **Settings** → **Developer settings** → **Personal access tokens** → **Tokens (classic)** → **Generate new token (classic)**.
2. Beri nama (mis. `push-image`), centang **`write:packages`**, lalu Generate.
3. **Salin token-nya sekarang** (hanya ditampilkan sekali).
4. Di terminal:

```powershell
docker login ghcr.io -u kurobapak
```

5. Saat diminta `Password:`, tempel token tadi lalu Enter (teks tidak akan terlihat, itu normal). Muncul `Login Succeeded`.

Cukup sekali. Docker mengingatnya.

### 2b. Login Docker di server Coolify (supaya server boleh mengunduh)

Image di GHCR otomatis **private**, jadi server perlu izin.

1. Buat token classic kedua dengan centang **`read:packages`** saja.
2. Masuk ke server lewat SSH, sebagai user yang dipakai Coolify (biasanya `root`).
3. Jalankan, lalu tempel token saat diminta password:

```bash
docker login ghcr.io -u kurobapak
```

Alternatif yang lebih gampang: nanti setelah image pertama terunggah, jadikan package-nya **Public** (GitHub → profil → **Packages** → pilih image → **Package settings** → **Change visibility**). Tapi image berisi kode PHP aplikasi, jadi private lebih aman.

## 3. Pasang pack ke aplikasi (otomatis)

### 3a. Lihat dulu apa yang akan dilakukan (aman, tidak menulis apa pun)

```powershell
cd D:\GitHub\Sensor-Testing\DFM\laravel-deploy-pack
php init.php D:\GitHub\Sensor-Testing\AFM --db=mysql --dry-run
```

### 3b. Jalankan sungguhan

```powershell
php init.php D:\GitHub\Sensor-Testing\AFM --db=mysql
```

`--db=mysql` (atau `mariadb`, `pgsql`, `sqlite`) memberi tahu script database yang kamu pakai di produksi. Tanpa opsi ini script membaca `.env.example`, yang di aplikasi Laravel baru biasanya `sqlite` (bawaan Laravel) walau di produksi kamu pakai MySQL. Itu tidak berbahaya: pilihan database hanya memengaruhi isi `.env.coolify`, bukan image. Tapi kalau tidak diberi `--db=mysql`, `.env.coolify` akan berisi `DB_CONNECTION=sqlite`, dan kalau ditempel apa adanya ke Coolify, aplikasi memakai SQLite, bukan MySQL kamu.

Script akan **membaca aplikasimu** dan memutuskan sendiri hal-hal yang dulu harus kamu cek manual:

| Yang dibaca | Dari mana | Yang dilakukan script |
|---|---|---|
| Ekstensi PHP yang dibutuhkan paket Composer | `composer.lock` (semua paket, bukan cuma yang kamu pasang langsung) dan driver di `.env.example` (mis. redis) | Menulis resep instalnya ke `Dockerfile`. Ekstensi yang sudah ada (mis. `gd`, `zip`) dilewati. Yang belum punya resep masuk daftar WARNINGS. |
| Perlu queue worker? | Mencari `ShouldQueue`, job, event broadcast, `->queue()`, dan paket Horizon | Menyalakan service `queue` di `docker-compose.yaml` |
| Perlu scheduler? | Mencari `Schedule::` di `routes/console.php` | Menyalakan service `scheduler` |
| Perlu Reverb (WebSocket)? | Paket `laravel/reverb` | Menyalakan service `reverb` dan menyambungkan env-nya |
| Variabel `VITE_...` di frontend | Kode di `resources/` dan `.env.example` | Menulis `ARG` di `Dockerfile` dan mengisi `.env.build` |
| Database | `.env.example`, atau opsi `--db=` | Hanya menyesuaikan isi `.env.coolify` (SQLite atau MySQL/PostgreSQL). Image-nya sama untuk semua database. |
| Nama image | git remote (pemilik GitHub) + nama folder | Mengisi nama image di `docker-compose.yaml`. Itu **satu-satunya tempat** nama image; script build membacanya dari sana. Untuk AFM: `ghcr.io/kurobapak/afm`. Mau nama lain: tambahkan `--image=ghcr.io/kurobapak/nama-lain`. Kalau sudah terlanjur dibuat dan mau ganti nama, cukup ubah baris `image:` di compose (Find & Replace). |

Script mencetak laporan. **Baca bagian `WARNINGS`**: itu hal yang tidak bisa diputuskan otomatis.

File yang dibuat di folder aplikasi:

| File | Fungsinya |
|---|---|
| `Dockerfile` | Resep untuk membuat image |
| `.dockerignore` | Daftar file yang **tidak boleh** masuk image (terutama `.env` berisi rahasia) |
| `docker/` | Pengaturan nginx, PHP, dan script yang jalan saat aplikasi start |
| `docker-compose.yaml` | Dibaca Coolify: "jalankan image ini, begini caranya" |
| `build-push.ps1` / `build-push.sh` | Script "build + push" sekali jalan (pilih satu yang kamu pakai) |
| `.env.build` | Nilai untuk tampilan (frontend) yang harus diketahui saat build |
| `.env.coolify` | Daftar variabel untuk ditempel di Coolify, sudah disesuaikan dengan aplikasimu |

Aturan keselamatan script:

- Di terminal biasa, script menampilkan daftar file yang akan ditulis lalu bertanya `Continue? [y/N]`. Tekan `N` kalau ragu: tidak ada yang ditulis. Pakai `--yes` untuk melewati pertanyaan itu. **Pastikan folder yang kamu berikan benar**: script akan menulis file ke folder tersebut.
- File yang **sudah ada** tidak ditimpa (dilaporkan `skipped`). Pakai `--force` kalau memang mau menimpa; salinan lama disimpan sebagai `.bak`.
- Aman dijalankan **berulang kali**. Kalau nanti kamu menambah paket Composer baru yang butuh ekstensi, jalankan `php init.php ...` lagi: hanya blok yang dikelola script di `Dockerfile` yang diperbarui, perubahan manualmu di luar blok itu tidak disentuh.
- Kalau aplikasimu sudah punya `Dockerfile` sendiri tanpa penanda pack, script tidak mengubahnya dan memberi peringatan.

**Kalau tidak punya PHP di laptop**, jalankan lewat Docker (nama image harus diberikan karena Docker tidak membaca git):

```powershell
docker run --rm `
  -v "D:\GitHub\Sensor-Testing\DFM\laravel-deploy-pack:/pack:ro" `
  -v "D:\GitHub\Sensor-Testing\AFM:/app" `
  php:8.4-cli php /pack/init.php /app --image=ghcr.io/kurobapak/afm
```

### 3c. Lengkapi dua file yang dihasilkan

1. **`.env.build`**: buka, isi nilai `VITE_...` yang masih kosong kalau frontend membutuhkannya. Untuk AFM biasanya sudah terisi (`VITE_APP_NAME`), tidak perlu diubah.
2. **`.env.coolify`**: buka, isi semua yang bertuliskan `CHANGE_ME` atau kosong (`APP_KEY`, domain di `APP_URL`, dan kredensial database kalau pakai MySQL/PostgreSQL). Dipakai di langkah 6.

Buat `APP_KEY` sekali (jalankan di folder AFM):

```powershell
php artisan key:generate --show
```

Hasilnya seperti `base64:AbCd...=`. Tempel sebagai nilai `APP_KEY`. **Simpan**, jangan diganti-ganti setelah dipakai.

## 4. Coba dulu di laptop (disarankan sebelum push pertama)

Build tanpa push:

```powershell
cd D:\GitHub\Sensor-Testing\AFM
.\build-push.ps1 -NoPush
```

Kalau muncul error `running scripts is disabled on this system`, jalankan dengan:

```powershell
powershell -ExecutionPolicy Bypass -File .\build-push.ps1 -NoPush
```

(Atau pakai Git Bash: `PUSH=0 ./build-push.sh`.)

Build pertama makan waktu beberapa menit. Setelah itu jauh lebih cepat karena ada cache. Lalu jalankan imagenya (ganti kunci dengan punyamu):

```powershell
docker run --rm -p 8000:8000 `
  -e APP_KEY="base64:ISI_KUNCI_KAMU" -e APP_ENV=production -e APP_DEBUG=false `
  -e DB_CONNECTION=sqlite -e DB_DATABASE=/app/storage/database/database.sqlite `
  -e SESSION_DRIVER=file -e CACHE_STORE=file -e QUEUE_CONNECTION=sync `
  -e AUTO_MIGRATE=true `
  ghcr.io/kurobapak/afm:latest
```

Buka http://localhost:8000. Kalau halaman login muncul, image-nya sehat. Hentikan dengan `Ctrl+C`.

## 5. Push pertama

```powershell
cd D:\GitHub\Sensor-Testing\AFM
.\build-push.ps1
```

Yang terjadi, berurutan:

1. Build image dan memberi dua nama: `:sha-xxxxxxx` (nomor commit git, untuk rollback) dan `:latest` (versi terbaru).
2. Push kedua nama itu ke GHCR.
3. Menampilkan `Pushed. Now click Deploy / Redeploy in Coolify.`

Cek hasilnya di GitHub → profil → **Packages**: image `afm` harus muncul.

Kalau `denied` saat push: ulangi langkah 2a (login Docker, token harus punya `write:packages`).

## 6. Siapkan Coolify (sekali per aplikasi)

1. Di Coolify: **New Resource** → pilih **Docker Compose Empty** (di kelompok Docker Based; namanya bisa sedikit beda tergantung versi) → tempel seluruh isi `docker-compose.yaml` dari folder aplikasi → Save.
2. Buka **Environment Variables**, pilih tampilan **Developer view**, lalu tempel seluruh isi **`.env.coolify`** yang sudah kamu lengkapi di langkah 3c. Nilainya sengaja ditulis tanpa tanda kutip.

   Penting:
   - **`APP_KEY` jangan diganti-ganti** setelah dipakai. Mengganti kunci membuat sesi login dan data terenkripsi tidak bisa dibaca lagi.
   - `AUTO_MIGRATE=true` artinya tabel database dibuat/diperbarui otomatis setiap aplikasi start.
   - Variabel `VITE_...` **tidak** dimasukkan di sini. Itu tugas `.env.build`.
   - Kalau memilih SQLite, database disimpan di volume `/app/storage/database/`, jadi tidak hilang saat deploy ulang.
3. Buka tab **Domains** pada service `app`, isi domain (mis. `https://afm.domainkamu.com`) dan port **`8000`**. Kalau script menyalakan `reverb`, service itu butuh subdomain sendiri di port **`8080`** (sama dengan `VITE_REVERB_HOST` di `.env.build`).
4. Klik **Deploy**. Buka tab **Logs** dan tunggu tulisan `✅ Application ready!`.
5. Buka domainnya. Selesai.

Kalau pertama kali tidak jalan, lihat bagian 9.

## 7. Update rutin (yang kamu lakukan setiap ada perubahan kode)

```powershell
cd D:\GitHub\Sensor-Testing\AFM
.\build-push.ps1          # build + push
```

Lalu klik **Redeploy** di Coolify.

Dua aturan penting:

- **Selalu push dulu, baru Redeploy.** Kalau build atau push gagal lalu kamu tetap klik Redeploy, yang jalan tetap versi lama.
- Kalau mau satu langkah saja, isi `COOLIFY_WEBHOOK` dan `COOLIFY_TOKEN` di PowerShell sebelum menjalankan script, dan script akan memicu deploy sendiri. Webhook ada di tab **Webhooks** aplikasi di Coolify, token dibuat di **Settings → API Tokens** (centang izin Deploy).

Kalau kamu menambah paket Composer baru, jalankan `php init.php D:\GitHub\Sensor-Testing\AFM` lagi sebelum build (lihat langkah 3b).

## 8. Rollback (kembali ke versi sebelumnya)

Script menampilkan nama tag tiap selesai, mis. `sha-1a2b3c4`.

1. Di Coolify → Environment Variables → tambah `APP_IMAGE_TAG` = `sha-1a2b3c4`.
2. Redeploy.
3. Untuk kembali ke versi terbaru, hapus variabel `APP_IMAGE_TAG` lalu redeploy.

## 9. Kalau ada masalah

| Gejala | Penyebab / solusi |
|---|---|
| `php` tidak dikenali saat menjalankan `init.php` | PHP tidak ada di laptop. Pakai cara Docker di langkah 3b. |
| `init.php`: "does not look like a Laravel app" | Folder yang diberikan salah. Harus folder yang berisi `artisan` dan `composer.json`. |
| `init.php` melapor `skipped` untuk `Dockerfile` | Aplikasi sudah punya Dockerfile tanpa penanda pack. Ganti namanya dulu, atau pakai `--force` (salinan lama jadi `.bak`). |
| WARNINGS: `ext-xxx` tidak punya resep | Tambahkan ekstensi itu sendiri di `Dockerfile`, **di atas** blok `deploy-pack:extra-extensions` (isi blok itu ditulis ulang tiap `init.php` dijalankan). |
| `docker: command not found` / tidak bisa konek | Docker Desktop belum menyala. |
| Build gagal: `requires ext-...` | Jalankan `php init.php ...` lagi supaya ekstensi baru terdeteksi. |
| `push` ditolak (`denied`) | Belum login ke `ghcr.io` di laptop, atau token tidak punya `write:packages`. |
| Coolify tidak bisa mengunduh image | Server belum login (langkah 2b), atau salah nama image di compose. |
| Halaman 500 | `APP_KEY` kosong/salah, atau variabel salah. Lihat Logs. |
| Tampilan lama padahal sudah push | Lupa push, atau `APP_IMAGE_TAG` masih menunjuk versi lama. |
| Upload file gagal (413) | Ukuran file lebih besar dari batas 100 MB di `docker/nginx.conf`. |
| Setelah login tidak pindah halaman sampai di-refresh manual (di lokal normal) | Di belakang proxy Coolify, Laravel mengira request-nya `http`, jadi redirect-nya ke `http://...` dan diblokir browser. Perbaikannya ada di `docker/nginx.conf` versi terbaru dari pack (teruskan `X-Forwarded-Proto`). Salin file itu ke aplikasi, build ulang, push, lalu Redeploy. Perbaikan resmi dari Laravel adalah menambah `$middleware->trustProxies(at: '*');` di `bootstrap/app.php` (juga membuat IP klien terbaca benar); `init.php` memberi peringatan kalau baris itu belum ada. Cek dengan `curl -sI https://domainmu/halaman-yang-butuh-login`: baris `Location:` harus diawali `https://`. |
| Nilai `VITE_...` kosong di produksi | Belum diisi di `.env.build`. Isi lalu build ulang image. |
| Data hilang setelah deploy | Database SQLite tidak diletakkan di `/app/storage/...` (volume), atau ada volume yang di-mount ke `/app/database`. |
| Job di queue tidak jalan | Script tidak mendeteksi job, atau `QUEUE_CONNECTION=sync`. Cek laporan `Queue worker` dari `init.php`. |

Perintah bantu di server:

```bash
docker ps                          # lihat container yang jalan
docker logs <nama-container> --tail 100
```

## 10. Aplikasi baru berikutnya

Ulangi bagian **3 sampai 6** saja. Bagian 2 (login Docker) tidak perlu diulang. Setiap aplikasi punya nama image sendiri, domain sendiri, dan webhook Coolify sendiri.
