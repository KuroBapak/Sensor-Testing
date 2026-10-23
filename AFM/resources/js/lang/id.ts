/**
 * All screen text of the app (Indonesian only). Edit wording here without
 * touching components. Server messages live in lang/id/*.php.
 */
export const id = {
    nav: {
        menu: 'Menu',
        tank: 'Tangki Utama',
        usage: 'Pemakaian BBM',
        vehicles: 'Kendaraan',
        qr: 'Generator Kode QR',
        settings: 'Pengaturan',
        accounts: 'Akun Admin',
    },

    common: {
        save: 'Simpan',
        saving: 'Menyimpan…',
        cancel: 'Batal',
        close: 'Tutup',
        add: 'Tambah',
        edit: 'Ubah',
        delete: 'Hapus',
        confirm: 'Konfirmasi',
        search: 'Cari',
        reset: 'Atur ulang',
        clearFilters: 'Hapus filter',
        all: 'Semua',
        yes: 'Ya',
        no: 'Tidak',
        none: 'Tidak ada',
        loading: 'Memuat…',
        retry: 'Coba lagi',
        error: 'Terjadi kesalahan. Silakan coba lagi.',
        noData: 'Belum ada data.',
        noResults: 'Tidak ada data yang cocok.',
        actions: 'Aksi',
        litersUnit: 'L',
        previous: 'Sebelumnya',
        next: 'Berikutnya',
        page: 'Halaman {page} dari {last}',
        showing: 'Menampilkan {from}–{to} dari {total}',
        batchAdd: 'Batch Add',
        export: 'Ekspor Excel',
    },

    categories: {
        internal: 'Internal',
        external: 'Eksternal',
    },

    validity: {
        lifetime: 'Lifetime',
        temporary: 'Sementara',
    },

    periods: {
        day: 'Hari',
        week: 'Minggu',
        month: 'Bulan',
    },

    weekdays: {
        1: 'Senin',
        2: 'Selasa',
        3: 'Rabu',
        4: 'Kamis',
        5: 'Jumat',
        6: 'Sabtu',
        7: 'Minggu',
    },

    tank: {
        title: 'Tangki Utama',
        subtitle:
            'Level tangki berdasarkan pembacaan ATG: total, BBM, dan air.',
        capacity: 'Kapasitas {liters} L',
        noReading: 'Belum ada pembacaan untuk tangki ini.',
        gaugeLabel: 'Level {name}: BBM {fuel}%, air {water}%, kosong {empty}%',
        total: 'Total',
        fuel: 'BBM',
        water: 'Air',
        empty: 'Kosong',
        chartTitle: 'Level 24 jam terakhir',
        chartEmpty: 'Belum ada data grafik.',
        readingsTitle: 'Pembacaan 5 jam terakhir',
        readingsEmpty: 'Tidak ada pembacaan dalam 5 jam terakhir.',
        time: 'Waktu',
        totalPct: 'Total (%)',
        fuelPct: 'BBM (%)',
        waterPct: 'Air (%)',
        latest: 'Pembacaan terakhir {time}',
    },

    usage: {
        title: 'Pemakaian BBM',
        subtitle:
            'Konsumsi BBM per kendaraan untuk kendaraan internal dan eksternal.',
        sideLabel: 'Sisi',
        totalFor: 'Total konsumsi {side}',
        chartLabel: 'Grafik konsumsi {side} per {unit}',
        chartEmpty: 'Belum ada konsumsi pada periode ini.',
        currentPeriod: 'Periode berjalan',
        earlierPeriods: 'Periode sebelumnya',
        period: 'Periode',
        periodRange: '{from} – {to}',
        displayOnly:
            'Pilihan periode hanya mengubah tampilan; aturan reset diatur di Pengaturan.',
        plate: 'Nomor Plat',
        vehicleType: 'Kendaraan',
        used: 'Digunakan (L)',
        quota: 'Kuota (L / {unit})',
        remaining: 'Sisa (L)',
        subtotal: 'Subtotal',
        cardsCount: '{count} kartu',
        empty: 'Belum ada kendaraan terdaftar pada sisi ini.',
        deletedCard: 'Kartu dihapus',
        expand: 'Buka grup',
        collapse: 'Tutup grup',
        openDetail: 'Lihat riwayat {plate}',
        detail: {
            title: 'Riwayat pemakaian',
            graph: 'Liter terpakai',
            recent: 'Pengisian terakhir',
            time: 'Waktu',
            liters: 'Liter',
            noFuelings: 'Belum ada pengisian.',
            noGraph: 'Belum ada data pemakaian.',
            failed: 'Riwayat tidak dapat dimuat.',
        },
    },

    vehicles: {
        title: 'Kendaraan',
        subtitle:
            'Jenis kendaraan dengan kapasitas tangki dan kuota BBM. Satu jenis dapat memiliki banyak kartu QR.',
        search: 'Cari kendaraan…',
        add: 'Tambah Kendaraan',
        name: 'Kendaraan',
        capacity: 'Kapasitas Maks. (L)',
        quota: 'Kuota (L / {unit})',
        cards: 'Kartu QR',
        empty: 'Belum ada kendaraan. Tambahkan satu atau impor dari Excel.',
        noResults: 'Tidak ada kendaraan yang cocok dengan pencarian.',
        addTitle: 'Tambah Kendaraan',
        editTitle: 'Ubah Kendaraan',
        nameLabel: 'Nama kendaraan',
        namePlaceholder: 'Mis. Daihatsu Gran Max',
        capacityLabel: 'Kapasitas maksimal tangki (L)',
        quotaLabel: 'Kuota BBM (L per {unit})',
        quotaHint:
            'Kuota berlaku untuk setiap kartu QR dengan jenis kendaraan ini.',
        quotaChangeWarning:
            'Mengubah kuota langsung memperbarui {count} kartu QR dengan jenis ini.',
        deleteTitle: 'Hapus kendaraan?',
        deleteBody: 'Kendaraan "{name}" akan dihapus.',
        deleteBlocked:
            'Kendaraan ini dipakai oleh {count} kartu QR dan tidak dapat dihapus selama kartunya masih ada.',
    },

    qr: {
        title: 'Generator Kode QR',
        subtitle:
            'Kartu QR adalah identitas satu kendaraan. Kuota dan masa berlaku menentukan apakah kendaraan boleh mengisi BBM.',
        counters: {
            active: 'Kartu Aktif',
            lifetime: 'Kartu Lifetime',
            expired: 'Kartu Kedaluwarsa',
            depleted: 'Kartu Habis',
            hint: 'Klik untuk memfilter tabel',
        },
        create: 'Buat Kartu',
        createTitle: 'Buat Kartu QR',
        createHelp:
            'Isi berurutan: kategori, kendaraan, nomor plat, lalu masa berlaku.',
        category: 'Kategori',
        vehicleType: 'Kendaraan',
        vehiclePlaceholder: 'Pilih kendaraan',
        noVehicleTypes: 'Belum ada kendaraan. Tambahkan di halaman Kendaraan.',
        plate: 'Nomor plat',
        platePlaceholder: 'Mis. KT 1234 AB',
        validity: 'Masa berlaku',
        validDays: 'Jumlah hari berlaku',
        validDaysWarning:
            'Isi jumlah hari berlaku (minimal 1) sebelum menyimpan kartu sementara.',
        expiresOn: 'Berlaku sampai {date} pukul 23:59',
        lifetimeNote: 'Lifetime tidak pernah kedaluwarsa.',
        quota: 'Kuota (dari kendaraan)',
        quotaValue: '{liters} L per {unit}',
        resetLabel: 'Reset per {unit}',
        resetHelpOn:
            'Kuota terisi penuh lagi setiap {unit}. Sisa tidak dibawa ke periode berikutnya.',
        resetHelpOff:
            'Kuota menjadi satu kantong untuk seluruh masa berlaku kartu.',
        saveCard: 'Simpan Kartu',
        searchPlate: 'Cari nomor plat…',
        filters: {
            validity: 'Masa berlaku',
            state: 'Status',
            reset: 'Reset',
            category: 'Kategori',
            vehicleType: 'Kendaraan',
            stateActive: 'Aktif',
            stateExpired: 'Kedaluwarsa',
            stateDepleted: 'Habis',
            stateLow: 'Hampir habis',
            resetOn: 'Reset aktif',
            resetOff: 'Reset mati',
        },
        columns: {
            plate: 'Nomor Plat',
            category: 'Kategori',
            vehicleType: 'Kendaraan',
            validity: 'Masa Berlaku / Kedaluwarsa',
            reset: 'Reset',
            quota: 'Kuota (L / {unit})',
            remaining: 'Sisa (L)',
            status: 'Status',
        },
        resetOn: 'Ya (per {unit})',
        resetOff: 'Tidak',
        lifetime: 'Lifetime',
        expiresAt: 'Sampai {date}',
        badges: {
            active: 'Aktif',
            lifetime: 'Lifetime',
            expired: 'Kedaluwarsa',
            depleted: 'Habis',
            low: 'Hampir habis',
        },
        refillAt: 'Terisi lagi {time}',
        needsTopUp: 'Menunggu top-up',
        empty: 'Belum ada kartu QR. Buat satu atau impor dari Excel.',
        noResults: 'Tidak ada kartu yang cocok dengan filter.',
        showQr: 'Tampilkan QR',
        topUp: 'Top-up',
        deleteCard: 'Hapus',
        deleteTitle: 'Hapus kartu QR?',
        deleteBody:
            'Kartu {plate} tidak dapat dipakai lagi. Riwayat pengisian tetap tersimpan di laporan dan ekspor.',
        popup: {
            title: 'Kartu QR',
            description:
                'Pratinjau ini adalah gambar yang disalin, diunduh, dan dicetak. Kode QR hanya berisi token acak; aturan kartu dicek di server.',
            copy: 'Salin',
            download: 'Unduh',
            print: 'Cetak',
            copied: 'Gambar kartu disalin ke clipboard.',
            copyBlocked:
                'Browser memblokir penyalinan gambar (butuh HTTPS atau localhost). Gunakan tombol Unduh.',
            downloaded: 'Kartu QR diunduh.',
            imageLabel: 'Kartu QR untuk {plate}',
        },
        card: {
            created: 'Dibuat {date}',
            capacity: 'Kapasitas tangki maks. {liters} L',
            quotaReset: 'Kuota {liters} L per {unit}',
            quotaPool: 'Kuota {liters} L (satu kantong)',
            validLifetime: 'Berlaku: Lifetime',
            validUntil: 'Berlaku sampai {date} pukul 23:59',
            notes: [
                '* Kuota dan masa berlaku kartu selalu dicek di server saat pengisian BBM.',
                '* Kartu ini hanya berlaku untuk kendaraan dengan nomor polisi tertera.',
                '* Jaga kerahasiaan QR Code ini; jangan dibagikan atau difoto sembarangan.',
            ],
        },
        topUpDialog: {
            title: 'Top-up {plate}',
            liters: 'Liter',
            note: 'Catatan (opsional)',
            helpOn: 'Top-up hanya berlaku untuk periode saat ini.',
            helpOff: 'Top-up ditambahkan ke kantong kuota kartu ini.',
            submit: 'Tambah Liter',
        },
    },

    settings: {
        title: 'Pengaturan',
        subtitle:
            'Pengaturan global yang memengaruhi banyak layar, jadi tidak perlu diisi ulang per kartu atau kendaraan.',
        reset: {
            title: 'Aturan reset kuota',
            description:
                'Berlaku untuk semua kartu yang reset-nya aktif. Perubahan langsung berlaku tanpa menulis ulang riwayat.',
            period: 'Periode reset',
            time: 'Jam reset',
            weekStart: 'Hari mulai minggu',
            monthDay: 'Tanggal reset bulanan (1–28)',
        },
        identity: {
            title: 'Identitas lokasi',
            description:
                'Nama dan logo tampil di sidebar, header, dan halaman masuk.',
            logo: 'Logo',
            logoHint:
                'PNG, JPG, WEBP, atau SVG, maksimal 1 MB. Logo persegi paling pas. Logo menggantikan ikon bawaan di sidebar.',
            logoRemove: 'Hapus logo',
            logoPreviewAlt: 'Pratinjau logo',
            logoNone: 'Belum ada logo',
        },
        site: {
            title: 'Lokasi dan tangki',
            siteName: 'Nama lokasi',
            siteNamePlaceholder: 'Mis. Site Tambang Utara',
            timezone: 'Zona waktu',
            tankCapacity: 'Kapasitas {name} (L)',
        },
        defaults: {
            title: 'Pemantauan kuota',
            lowQuota: 'Ambang kuota hampir habis (% dari kuota)',
            lowQuotaHint:
                'Kartu yang sisanya sama dengan atau di bawah persen ini dari kuota diberi badge "Hampir habis". Sisa 0 tampil "Habis".',
        },
        save: 'Simpan Pengaturan',
        confirmTitle: 'Ubah aturan reset?',
        confirmBody:
            'Perubahan ini berlaku segera untuk {count} kartu dengan reset aktif. Sisa liter dihitung ulang dari awal periode baru.',
        confirmQuota:
            'Kuota berarti liter per {unit}. Angka yang sama kini mencakup satu {unit} penuh.',
        confirmSubmit: 'Ya, ubah aturan reset',
    },

    accounts: {
        title: 'Akun Admin',
        subtitle: 'Kelola akun Admin. Akun Super Admin tidak dikelola di sini.',
        add: 'Tambah Akun',
        name: 'Nama',
        email: 'Email',
        createdAt: 'Dibuat',
        empty: 'Belum ada akun Admin.',
        addTitle: 'Tambah Akun Admin',
        editTitle: 'Ubah Akun Admin',
        password: 'Kata sandi',
        passwordHelp: 'Minimal 8 karakter.',
        resetPassword: 'Reset kata sandi',
        resetTitle: 'Reset kata sandi {name}',
        newPassword: 'Kata sandi baru',
        deleteTitle: 'Hapus akun?',
        deleteBody:
            'Akun {name} ({email}) akan dihapus. Nama tetap tampil pada riwayat yang pernah dibuatnya.',
    },

    batchAdd: {
        title: 'Batch Add Kendaraan & Kartu QR',
        help: 'Tambahkan banyak kendaraan dan kartu QR sekaligus dari satu file Excel (.xlsx, maksimal 2 MB dan 1.000 baris). Sheet "Kendaraan" dan "Kartu QR" diunggah bersama karena kartu bergantung pada kendaraannya. Jika ada satu baris yang salah, tidak ada data yang ditambahkan.',
        exampleHelp:
            'Belum punya formatnya? Unduh "Contoh Batch Add": satu file Excel berisi sheet Kendaraan dan Kartu QR yang sudah terisi contoh, serta sheet Petunjuk yang menjelaskan setiap kolom. Salin formatnya untuk data Anda.',
        example: 'Contoh Batch Add',
        removeExample:
            'Hapus baris contoh sebelum mengunggah data Anda sendiri.',
        file: 'File Excel (.xlsx)',
        submit: 'Impor',
        importing: 'Mengimpor…',
        errorsTitle:
            'Impor dibatalkan. Perbaiki baris berikut lalu unggah ulang:',
        sheet: 'Sheet',
        row: 'Baris',
        column: 'Kolom',
        message: 'Pesan',
    },

    exportAll: {
        button: 'Export Semua Data',
        title: 'Export Semua Data',
        description:
            'Satu file Excel berisi semua tab. Periode berlaku untuk tab pemakaian, tangki, dan riwayat.',
        from: 'Dari tanggal',
        to: 'Sampai tanggal',
        thisMonth: 'Bulan ini',
        submit: 'Unduh Excel',
        invalidRange: 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
    },

    header: {
        site: 'Lokasi',
        clock: 'Waktu lokasi',
        switchToLight: 'Ganti ke tema terang',
        switchToDark: 'Ganti ke tema gelap',
    },

    auth: {
        loginTitle: 'Masuk ke akun Anda',
        loginDescription: 'Masukkan email dan kata sandi Anda di bawah ini',
        login: 'Masuk',
        email: 'Alamat email',
        password: 'Kata sandi',
        remember: 'Ingat saya',
        forgot: 'Lupa kata sandi?',
        forgotTitle: 'Lupa kata sandi',
        forgotDescription:
            'Masukkan email Anda untuk menerima tautan reset kata sandi',
        sendLink: 'Kirim tautan reset kata sandi',
        returnTo: 'Atau, kembali ke',
        loginLower: 'halaman masuk',
        resetTitle: 'Reset kata sandi',
        resetDescription: 'Masukkan kata sandi baru Anda di bawah ini',
        confirmPassword: 'Konfirmasi kata sandi',
        resetSubmit: 'Reset kata sandi',
    },

    account: {
        menuSettings: 'Pengaturan akun',
        logout: 'Keluar',
        settingsTitle: 'Pengaturan akun',
        settingsDescription: 'Kelola profil dan keamanan akun Anda',
        profile: 'Profil',
        profileTitle: 'Pengaturan profil',
        profileDescription: 'Ubah nama dan alamat email Anda',
        name: 'Nama',
        fullName: 'Nama lengkap',
        security: 'Keamanan',
        securityTitle: 'Pengaturan keamanan',
        passwordTitle: 'Ubah kata sandi',
        passwordDescription:
            'Gunakan kata sandi yang panjang dan acak agar akun tetap aman',
        currentPassword: 'Kata sandi saat ini',
        newPassword: 'Kata sandi baru',
        confirmPassword: 'Konfirmasi kata sandi',
        appearance: 'Tampilan',
        appearanceTitle: 'Pengaturan tampilan',
        appearanceDescription: 'Ubah tampilan aplikasi untuk akun Anda',
        light: 'Terang',
        dark: 'Gelap',
        system: 'Sistem',
        save: 'Simpan',
    },
} as const;

/** Replace {placeholders} in a text with values. */
export function fill(
    template: string,
    params: Record<string, string | number>,
): string {
    return template.replace(/\{(\w+)\}/g, (match, key: string) =>
        key in params ? String(params[key]) : match,
    );
}
