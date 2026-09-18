# Gotcha: Laravel Routes dengan Auth Middleware Memerlukan Login

## Problem
Ketika mengakses route yang dilindungi `auth` middleware (seperti `/fuel-monitoring`), user akan diredirect ke halaman welcome/login. Root URL hanya menampilkan "Welcome Laravel 13".

## Root Cause
Route `/fuel-monitoring` berada di dalam:
```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('fuel-monitoring', 'fuel-monitoring')->name('fuel-monitoring');
});
```

Ini berarti user **wajib login dan verified** sebelum bisa mengakses halaman tersebut.

## Solution
1. **Seed database** terlebih dahulu untuk membuat user test:
   ```bash
   php artisan db:seed
   ```
   
   Ini akan membuat user dengan kredensial:
   - Email: `test@example.com`
   - Password: `password`

2. **Login** melalui `/login` menggunakan kredensial di atas.

3. **Akses dashboard** setelah login melalui sidebar navigation atau langsung ke `/fuel-monitoring`.

## Alternative: Public Access (Tidak Disarankan untuk Production)
Jika ingin membuat route publik (hanya untuk development/testing):
```php
// Di routes/web.php - OUTSIDE auth middleware group
Route::inertia('fuel-monitoring-public', 'fuel-monitoring')->name('fuel-monitoring.public');
```

## Lesson Learned
- Selalu cek middleware group saat membuat route baru
- Pastikan ada user di database sebelum testing fitur yang butuh autentikasi
- Gunakan `php artisan db:seed` untuk setup data awal development
