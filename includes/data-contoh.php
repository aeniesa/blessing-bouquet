<?php
/**
 * data-contoh.php — DATA CONTOH SEMENTARA (MODE CONTOH).
 *
 * Sumber: `database/database.sql` (§11.2 & seed). Data ini BUKAN karangan;
 * isinya sama persis dengan seed resmi proyek. Hanya dipakai bila database
 * (MySQL/MariaDB) tidak tersedia, agar halaman publik tetap dapat ditampilkan
 * dan diuji. Tidak ada harga/toko fiktif yang ditambahkan.
 *
 * Kolom yang di database bernilai NULL (mis. alamat, instagram) tetap NULL
 * di sini dan TIDAK diisi karangan.
 */
declare(strict_types=1);

return [
    'pengaturan_toko' => [
        'id'                  => 1,
        'nama_toko'           => 'Blessing Bouquet',
        'logo'                => null,
        'deskripsi'           => 'Blessing Bouquet menyediakan aneka buket: buket snack, buket uang, buket pita satin, buket bunga artificial, buket request, dan lainnya. Pemesanan dilakukan melalui WhatsApp.',
        'whatsapp'            => '6285920064874',
        'instagram'           => null,
        'tiktok'              => null,
        'alamat'              => 'Pekalongan, Jawa Tengah',
        'embed_maps'          => null,
        'headline'            => 'Rangkaian Buket untuk Setiap Momen',
        'deskripsi_beranda'   => 'Dari hadiah sederhana hingga momen istimewa, temukan buket yang dibuat dengan penuh perhatian untuk orang tersayang.',
        'banner'              => null,
        'palet_warna'         => 'sage_green',
        'teks_tombol_katalog' => 'Lihat Katalog',
        'hero_blur'           => 1,
        'hero_transparansi'   => 45,
        'judul_produk_unggulan' => 'Produk Unggulan',
        'deskripsi_produk_unggulan' => 'Temukan buket favorit untuk orang tersayang dan jadikan setiap momen lebih berkesan.',
        'updated_at'          => null,
    ],

    'kategori' => [
        ['id' => 1, 'nama' => 'Buket Snack',            'slug' => 'buket-snack',            'deskripsi' => 'Aneka buket berisi snack.'],
        ['id' => 2, 'nama' => 'Buket Uang',             'slug' => 'buket-uang',             'deskripsi' => 'Buket uang (harga = biaya jasa, belum termasuk nominal uang).'],
        ['id' => 3, 'nama' => 'Snack Tower',            'slug' => 'snack-tower',            'deskripsi' => 'Tumpukan snack dengan tingkat dan anggaran menyesuaikan.'],
        ['id' => 4, 'nama' => 'Buket Pita Satin',       'slug' => 'buket-pita-satin',       'deskripsi' => 'Buket pita satin, tersedia tanpa/dengan glitter.'],
        ['id' => 5, 'nama' => 'Buket Hari Guru',        'slug' => 'buket-hari-guru',        'deskripsi' => 'Buket spesial untuk momen Hari Guru.'],
        ['id' => 6, 'nama' => 'Buket Bunga Artificial', 'slug' => 'buket-bunga-artificial', 'deskripsi' => 'Buket bunga artificial beberapa ukuran.'],
        ['id' => 7, 'nama' => 'Buket Request',          'slug' => 'buket-request',          'deskripsi' => 'Buket custom sesuai permintaan pelanggan.'],
        ['id' => 8, 'nama' => 'Ganci Kawat Bulu',       'slug' => 'ganci-kawat-bulu',       'deskripsi' => 'Gantungan kunci kawat bulu.'],
    ],

    'produk' => [
        ['id' => 1,  'kategori_id' => 1, 'nama' => 'Buket Mini Snack', 'slug' => 'buket-mini-snack', 'deskripsi' => 'Buket snack mini berisi kurang lebih 7 snack.', 'jenis_harga' => 'tetap', 'harga_tetap' => 20000.00, 'harga_mulai' => null, 'is_aktif' => 1, 'is_unggulan' => 1, 'ketentuan' => null],
        ['id' => 2,  'kategori_id' => 1, 'nama' => 'Buket Snack Custom', 'slug' => 'buket-snack-custom', 'deskripsi' => 'Snack bisa diminta atau dibawa sendiri; harga akhir menyesuaikan.', 'jenis_harga' => 'mulai_dari', 'harga_tetap' => null, 'harga_mulai' => 20000.00, 'is_aktif' => 1, 'is_unggulan' => 1, 'ketentuan' => null],
        ['id' => 3,  'kategori_id' => 2, 'nama' => 'Buket Uang Biasa', 'slug' => 'buket-uang-biasa', 'deskripsi' => 'Harga adalah biaya jasa pembuatan, belum termasuk nominal uang.', 'jenis_harga' => 'biaya_jasa', 'harga_tetap' => null, 'harga_mulai' => null, 'is_aktif' => 1, 'is_unggulan' => 0, 'ketentuan' => null],
        ['id' => 4,  'kategori_id' => 2, 'nama' => 'Buket Uang Bentuk', 'slug' => 'buket-uang-bentuk', 'deskripsi' => 'Harga adalah biaya jasa pembuatan, belum termasuk nominal uang.', 'jenis_harga' => 'biaya_jasa', 'harga_tetap' => null, 'harga_mulai' => null, 'is_aktif' => 1, 'is_unggulan' => 0, 'ketentuan' => null],
        ['id' => 5,  'kategori_id' => 3, 'nama' => 'Snack Tower', 'slug' => 'snack-tower', 'deskripsi' => 'Harga menyesuaikan tingkat dan anggaran, dikonfirmasi via WhatsApp.', 'jenis_harga' => 'berdasarkan_permintaan', 'harga_tetap' => null, 'harga_mulai' => null, 'is_aktif' => 1, 'is_unggulan' => 0, 'ketentuan' => null],
        ['id' => 6,  'kategori_id' => 4, 'nama' => 'Buket Pita Satin (Tanpa Glitter)', 'slug' => 'buket-pita-satin-tanpa-glitter', 'deskripsi' => 'Buket pita satin tanpa glitter. Estimasi pengerjaan kurang lebih 7 hari.', 'jenis_harga' => 'mulai_dari', 'harga_tetap' => null, 'harga_mulai' => 35000.00, 'is_aktif' => 1, 'is_unggulan' => 1, 'ketentuan' => 'Estimasi kurang lebih 7 hari; pilihan warna satin dan kertas; pesanan mendesak menyesuaikan stok; pesanan di atas Rp100.000 perlu DP 50%; pesanan tidak dapat dibatalkan.'],
        ['id' => 7,  'kategori_id' => 4, 'nama' => 'Buket Pita Satin (Dengan Glitter)', 'slug' => 'buket-pita-satin-dengan-glitter', 'deskripsi' => 'Buket pita satin dengan glitter. Estimasi pengerjaan kurang lebih 7 hari.', 'jenis_harga' => 'mulai_dari', 'harga_tetap' => null, 'harga_mulai' => 45000.00, 'is_aktif' => 1, 'is_unggulan' => 0, 'ketentuan' => 'Estimasi kurang lebih 7 hari; pilihan warna satin dan kertas; pesanan mendesak menyesuaikan stok; pesanan di atas Rp100.000 perlu DP 50%; pesanan tidak dapat dibatalkan.'],
        ['id' => 8,  'kategori_id' => 5, 'nama' => 'Buket Hari Guru', 'slug' => 'buket-hari-guru', 'deskripsi' => 'Buket spesial untuk momen Hari Guru.', 'jenis_harga' => 'tetap', 'harga_tetap' => 20000.00, 'harga_mulai' => null, 'is_aktif' => 1, 'is_unggulan' => 0, 'ketentuan' => null],
        ['id' => 9,  'kategori_id' => 6, 'nama' => 'Buket Bunga Artificial', 'slug' => 'buket-bunga-artificial', 'deskripsi' => 'Buket bunga artificial tersedia ukuran S, M, L, dan Jumbo.', 'jenis_harga' => 'mulai_dari', 'harga_tetap' => null, 'harga_mulai' => 55000.00, 'is_aktif' => 1, 'is_unggulan' => 1, 'ketentuan' => null],
        ['id' => 10, 'kategori_id' => 7, 'nama' => 'Buket Request', 'slug' => 'buket-request', 'deskripsi' => 'Buket custom sesuai permintaan; konsultasi via WhatsApp.', 'jenis_harga' => 'mulai_dari', 'harga_tetap' => null, 'harga_mulai' => 20000.00, 'is_aktif' => 1, 'is_unggulan' => 0, 'ketentuan' => null],
        ['id' => 11, 'kategori_id' => 8, 'nama' => 'Ganci Kawat Bulu', 'slug' => 'ganci-kawat-bulu', 'deskripsi' => 'Gantungan kunci kawat bulu, tersedia berbagai pilihan.', 'jenis_harga' => 'mulai_dari', 'harga_tetap' => null, 'harga_mulai' => 2000.00, 'is_aktif' => 1, 'is_unggulan' => 0, 'ketentuan' => null],
    ],

    'variasi_produk' => [
        ['id' => 1,  'produk_id' => 3, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '10 lembar', 'harga' => 35000.00,  'urutan' => 1],
        ['id' => 2,  'produk_id' => 3, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '15 lembar', 'harga' => 55000.00,  'urutan' => 2],
        ['id' => 3,  'produk_id' => 3, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '20 lembar', 'harga' => 70000.00,  'urutan' => 3],
        ['id' => 4,  'produk_id' => 3, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '25 lembar', 'harga' => 90000.00,  'urutan' => 4],
        ['id' => 5,  'produk_id' => 3, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '30 lembar', 'harga' => 105000.00, 'urutan' => 5],
        ['id' => 6,  'produk_id' => 3, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '35 lembar', 'harga' => 125000.00, 'urutan' => 6],
        ['id' => 7,  'produk_id' => 3, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '40 lembar', 'harga' => 140000.00, 'urutan' => 7],
        ['id' => 8,  'produk_id' => 3, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '45 lembar', 'harga' => 160000.00, 'urutan' => 8],
        ['id' => 9,  'produk_id' => 3, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '50 lembar', 'harga' => 175000.00, 'urutan' => 9],
        ['id' => 10, 'produk_id' => 4, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '10 lembar', 'harga' => 50000.00,  'urutan' => 1],
        ['id' => 11, 'produk_id' => 4, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '15 lembar', 'harga' => 60000.00,  'urutan' => 2],
        ['id' => 12, 'produk_id' => 4, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '20 lembar', 'harga' => 80000.00,  'urutan' => 3],
        ['id' => 13, 'produk_id' => 4, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '25 lembar', 'harga' => 100000.00, 'urutan' => 4],
        ['id' => 14, 'produk_id' => 4, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '30 lembar', 'harga' => 120000.00, 'urutan' => 5],
        ['id' => 15, 'produk_id' => 4, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '35 lembar', 'harga' => 140000.00, 'urutan' => 6],
        ['id' => 16, 'produk_id' => 4, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '40 lembar', 'harga' => 160000.00, 'urutan' => 7],
        ['id' => 17, 'produk_id' => 4, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '45 lembar', 'harga' => 180000.00, 'urutan' => 8],
        ['id' => 18, 'produk_id' => 4, 'nama_variasi' => 'Jumlah Lembar', 'nilai' => '50 lembar', 'harga' => 200000.00, 'urutan' => 9],
        ['id' => 19, 'produk_id' => 6, 'nama_variasi' => 'Tangkai', 'nilai' => '3 tangkai',  'harga' => 35000.00,  'urutan' => 1],
        ['id' => 20, 'produk_id' => 6, 'nama_variasi' => 'Tangkai', 'nilai' => '5 tangkai',  'harga' => 55000.00,  'urutan' => 2],
        ['id' => 21, 'produk_id' => 6, 'nama_variasi' => 'Tangkai', 'nilai' => '7 tangkai',  'harga' => 65000.00,  'urutan' => 3],
        ['id' => 22, 'produk_id' => 6, 'nama_variasi' => 'Tangkai', 'nilai' => '10 tangkai', 'harga' => 80000.00,  'urutan' => 4],
        ['id' => 23, 'produk_id' => 6, 'nama_variasi' => 'Tangkai', 'nilai' => '15 tangkai', 'harga' => 110000.00, 'urutan' => 5],
        ['id' => 24, 'produk_id' => 6, 'nama_variasi' => 'Tangkai', 'nilai' => '20 tangkai', 'harga' => 140000.00, 'urutan' => 6],
        ['id' => 25, 'produk_id' => 6, 'nama_variasi' => 'Tangkai', 'nilai' => '25 tangkai', 'harga' => 160000.00, 'urutan' => 7],
        ['id' => 26, 'produk_id' => 7, 'nama_variasi' => 'Tangkai', 'nilai' => '3 tangkai',  'harga' => 45000.00,  'urutan' => 1],
        ['id' => 27, 'produk_id' => 7, 'nama_variasi' => 'Tangkai', 'nilai' => '5 tangkai',  'harga' => 70000.00,  'urutan' => 2],
        ['id' => 28, 'produk_id' => 7, 'nama_variasi' => 'Tangkai', 'nilai' => '7 tangkai',  'harga' => 85000.00,  'urutan' => 3],
        ['id' => 29, 'produk_id' => 7, 'nama_variasi' => 'Tangkai', 'nilai' => '10 tangkai', 'harga' => 105000.00, 'urutan' => 4],
        ['id' => 30, 'produk_id' => 7, 'nama_variasi' => 'Tangkai', 'nilai' => '15 tangkai', 'harga' => 135000.00, 'urutan' => 5],
        ['id' => 31, 'produk_id' => 7, 'nama_variasi' => 'Tangkai', 'nilai' => '20 tangkai', 'harga' => 160000.00, 'urutan' => 6],
        ['id' => 32, 'produk_id' => 7, 'nama_variasi' => 'Tangkai', 'nilai' => '25 tangkai', 'harga' => 185000.00, 'urutan' => 7],
        ['id' => 33, 'produk_id' => 9, 'nama_variasi' => 'Ukuran', 'nilai' => 'S',     'harga' => 55000.00,  'urutan' => 1],
        ['id' => 34, 'produk_id' => 9, 'nama_variasi' => 'Ukuran', 'nilai' => 'M',     'harga' => 85000.00,  'urutan' => 2],
        ['id' => 35, 'produk_id' => 9, 'nama_variasi' => 'Ukuran', 'nilai' => 'L',     'harga' => 125000.00, 'urutan' => 3],
        ['id' => 36, 'produk_id' => 9, 'nama_variasi' => 'Ukuran', 'nilai' => 'Jumbo', 'harga' => 150000.00, 'urutan' => 4],
    ],

    'aksesori' => [
        ['id' => 1, 'nama' => 'Kupu-kupu kecil', 'harga' => 5000.00,  'is_aktif' => 1],
        ['id' => 2, 'nama' => 'Kupu-kupu besar', 'harga' => 7000.00,  'is_aktif' => 1],
        ['id' => 3, 'nama' => 'Pita tulisan',    'harga' => 10000.00, 'is_aktif' => 1],
        ['id' => 4, 'nama' => 'Kartu ucapan',    'harga' => 4000.00,  'is_aktif' => 1],
        ['id' => 5, 'nama' => 'Mahkota kecil',   'harga' => 15000.00, 'is_aktif' => 1],
        ['id' => 6, 'nama' => 'Mahkota besar',   'harga' => 20000.00, 'is_aktif' => 1],
        ['id' => 7, 'nama' => 'Boneka',          'harga' => 25000.00, 'is_aktif' => 1],
    ],

    'produk_aksesori' => [
        ['produk_id' => 6, 'aksesori_id' => 1], ['produk_id' => 6, 'aksesori_id' => 2],
        ['produk_id' => 6, 'aksesori_id' => 3], ['produk_id' => 6, 'aksesori_id' => 4],
        ['produk_id' => 6, 'aksesori_id' => 5], ['produk_id' => 6, 'aksesori_id' => 6],
        ['produk_id' => 6, 'aksesori_id' => 7],
        ['produk_id' => 7, 'aksesori_id' => 1], ['produk_id' => 7, 'aksesori_id' => 2],
        ['produk_id' => 7, 'aksesori_id' => 3], ['produk_id' => 7, 'aksesori_id' => 4],
        ['produk_id' => 7, 'aksesori_id' => 5], ['produk_id' => 7, 'aksesori_id' => 6],
        ['produk_id' => 7, 'aksesori_id' => 7],
    ],

    'gambar_produk' => [],

    // Ulasan sengaja kosong (belum ada ulasan asli, sesuai database.sql).
    'ulasan' => [],
];
