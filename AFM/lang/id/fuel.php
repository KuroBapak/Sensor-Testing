<?php

/*
 * Server-side messages of the fuel monitoring app (Indonesian). Screen text
 * lives in resources/js/lang/id.ts; both are meant to be edited without
 * touching components or controllers.
 */
return [
    'vehicle' => [
        'name_taken' => "Kendaraan ':name' sudah terdaftar.",
        'created' => "Kendaraan ':name' ditambahkan.",
        'updated' => "Kendaraan ':name' diperbarui.",
        'deleted' => "Kendaraan ':name' dihapus.",
        'in_use' => "Kendaraan ':name' tidak dapat dihapus karena masih dipakai oleh :count kartu QR.",
    ],

    'card' => [
        'plate_taken' => "Nomor plat ':plate' sudah dipakai oleh kartu yang masih berlaku.",
        'created' => 'Kartu QR :plate dibuat.',
        'deleted' => 'Kartu QR :plate dihapus. Riwayat pengisian tetap tersimpan.',
        'topped_up' => 'Top-up :liters L untuk :plate berhasil.',
    ],

    'import' => [
        'row' => 'Baris :row: :message',
        'failed' => 'Impor dibatalkan: :count kesalahan ditemukan. Tidak ada data yang ditambahkan.',
        'success_vehicles' => ':count kendaraan berhasil ditambahkan.',
        'success_cards' => ':count kartu QR berhasil ditambahkan.',
        'success_both' => ':vehicles kendaraan dan :cards kartu QR berhasil ditambahkan.',
        'no_sheets' => "Sheet 'Kendaraan' atau 'Kartu QR' tidak ditemukan. Gunakan file Contoh Batch Add sebagai acuan.",
        'required' => ':column wajib diisi.',
        'too_long' => ':column terlalu panjang (maksimal :max karakter).',
        'positive_number' => ':column harus berupa angka lebih dari 0.',
        'duplicate_in_file' => "':value' muncul lebih dari satu kali (juga di baris :row).",
        'invalid_choice' => ":column ':value' tidak dikenal. Gunakan salah satu dari: :choices.",
        'vehicle_not_found' => "Kendaraan ':name' tidak ditemukan.",
        'days' => ':column harus berupa bilangan bulat 1 sampai 3650.',
        'unreadable' => 'File tidak dapat dibaca. Gunakan file Excel (.xlsx) yang valid.',
        'missing_column' => "Kolom ':column' tidak ditemukan pada baris judul.",
        'too_many' => 'File berisi :count baris data; maksimal :max baris.',
    ],

    'settings' => [
        'saved' => 'Pengaturan disimpan.',
        'confirm_required' => 'Perubahan aturan reset berlaku untuk :count kartu dan harus dikonfirmasi.',
    ],

    'account' => [
        'created' => 'Akun :name dibuat.',
        'updated' => 'Akun :name diperbarui.',
        'password_reset' => 'Kata sandi :name direset.',
        'deleted' => 'Akun :name dihapus.',
    ],
];
