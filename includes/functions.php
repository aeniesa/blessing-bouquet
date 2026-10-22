<?php
/**
 * functions.php — helper reusable (§26.1).
 */
declare(strict_types=1);

// Batas & nilai awal pengaturan latar hero beranda (dapat diubah admin).
const HERO_BLUR_MIN         = 0;
const HERO_BLUR_MAX         = 10;
const HERO_BLUR_DEFAULT     = 1;
const HERO_TRANSPARANSI_MIN = 0;
const HERO_TRANSPARANSI_MAX = 90;
const HERO_TRANSPARANSI_DEFAULT = 45;

/** Escape output (anti-XSS, §22.1 no.3). */
function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

/**
 * Format rupiah. Nilai NULL -> "perlu dikonfirmasi" (bukan Rp0), §26.1/§11.1.
 */
function format_rupiah($angka, bool $labelNull = true): string
{
    if ($angka === null || $angka === '') {
        return $labelNull ? 'perlu dikonfirmasi' : '';
    }
    return 'Rp' . number_format((float) $angka, 0, ',', '.');
}

/** Buat slug aman dari teks. */
function buat_slug(string $teks): string
{
    $slug = strtolower(trim($teks));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');
    return $slug === '' ? 'item' : $slug;
}

/** Buat slug unik untuk kolom unik (tambahkan -2, -3, ... bila sudah ada). */
function slug_unik(PDO $pdo, string $tabel, string $slugDasar, ?int $idKecuali = null): string
{
    $diizinkan = ['produk', 'kategori'];
    if (!in_array($tabel, $diizinkan, true)) {
        throw new InvalidArgumentException('Tabel tidak diizinkan untuk slug.');
    }

    $slugDasar = buat_slug($slugDasar);
    $slug = $slugDasar;
    $i = 1;

    while (true) {
        $sql = "SELECT id FROM `{$tabel}` WHERE slug = ?" . ($idKecuali ? ' AND id <> ?' : '') . ' LIMIT 1';
        $params = [$slug];
        if ($idKecuali) {
            $params[] = $idKecuali;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetch() === false) {
            return $slug;
        }
        $i++;
        $slug = $slugDasar . '-' . $i;
    }
}

/** Tautan WhatsApp (§26.1, §23). */
function wa_link(string $nomor, string $pesan = ''): string
{
    $nomor = preg_replace('/\D+/', '', $nomor) ?? '';
    if (str_starts_with($nomor, '0')) {
        $nomor = '62' . substr($nomor, 1);
    }
    $url = 'https://wa.me/' . $nomor;
    if ($pesan !== '') {
        $url .= '?text=' . rawurlencode($pesan);
    }
    return $url;
}

/** URL absolut (relatif terhadap APP_BASE) untuk aset/tautan. */
function base_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return APP_BASE . ($path === '' ? '/' : '/' . $path);
}

/** Alias eksplisit untuk aset. */
function aset(string $path): string
{
    return base_url($path);
}

/**
 * URL aset dengan penanda versi berbasis waktu ubah berkas (?v=...).
 * Mencegah browser/service worker menyajikan versi lama setelah berkas diubah.
 */
function aset_berversi(string $path): string
{
    $path = ltrim($path, '/');
    $berkas = PUBLIC_DIR . '/' . $path;
    $versi  = is_file($berkas) ? filemtime($berkas) : null;
    return base_url($path) . ($versi !== false && $versi !== null ? '?v=' . $versi : '');
}

/** Harga minimum dari daftar variasi (abaikan yang NULL). */
function min_variasi_harga(array $variasi): ?float
{
    $min = null;
    foreach ($variasi as $v) {
        if (isset($v['harga']) && $v['harga'] !== null && $v['harga'] !== '') {
            $val = (float) $v['harga'];
            if ($min === null || $val < $min) {
                $min = $val;
            }
        }
    }
    return $min;
}

/** Teks harga sesuai jenis (F-07, §11.1). */
function harga_produk(array $produk, array $variasi = []): string
{
    $jenis = $produk['jenis_harga'] ?? '';
    $min = min_variasi_harga($variasi);

    switch ($jenis) {
        case 'tetap':
            if (($produk['harga_tetap'] ?? null) !== null) {
                return format_rupiah($produk['harga_tetap']);
            }
            if ($min !== null) {
                return 'Mulai dari ' . format_rupiah($min);
            }
            return 'perlu dikonfirmasi';

        case 'mulai_dari':
            if (($produk['harga_mulai'] ?? null) !== null) {
                return 'Mulai dari ' . format_rupiah($produk['harga_mulai']);
            }
            if ($min !== null) {
                return 'Mulai dari ' . format_rupiah($min);
            }
            return 'perlu dikonfirmasi';

        case 'biaya_jasa':
            if ($min !== null) {
                return 'Biaya jasa mulai dari ' . format_rupiah($min);
            }
            return 'Biaya jasa (belum termasuk uang)';

        case 'berdasarkan_permintaan':
            return 'Harga berdasarkan permintaan';
    }

    return 'perlu dikonfirmasi';
}

/** Catatan tambahan pada detail produk (biaya jasa / custom). */
function catatan_harga_produk(array $produk): ?string
{
    return match ($produk['jenis_harga'] ?? '') {
        'biaya_jasa'              => 'Harga yang tertera adalah biaya jasa pembuatan, belum termasuk nominal uang.',
        'berdasarkan_permintaan'  => 'Harga akan dikonfirmasi melalui WhatsApp.',
        default                   => null,
    };
}

/**
 * Harga pasti untuk perhitungan subtotal (§11.1).
 * - `tetap` dengan harga_tetap terisi -> harga pasti.
 * - variasi terpilih dengan harga terisi -> harga pasti.
 * - selain itu -> NULL (belum pasti).
 */
function harga_pasti_produk(array $produk, ?array $variasi = null): ?float
{
    if (($produk['jenis_harga'] ?? '') === 'tetap' && ($produk['harga_tetap'] ?? null) !== null && $produk['harga_tetap'] !== '') {
        return (float) $produk['harga_tetap'];
    }
    if ($variasi !== null && isset($variasi['harga']) && $variasi['harga'] !== null && $variasi['harga'] !== '') {
        return (float) $variasi['harga'];
    }
    return null;
}

/** URL halaman detail produk untuk sebuah slug. */
function url_produk(string $slug): string
{
    return base_url('produk.php?slug=' . rawurlencode($slug));
}

/** Daftar 4 palet warna (§10.1, keputusan K-06). Sage green = warna dasar website. */
function daftar_palet(): array
{
    return [
        'sage_green' => [
            'label'         => 'Sage Green',
            'dasar'         => '#F5F7F2',
            'aksen'         => '#A9BC9B',
            'teks'          => '#37403A',
            'pendukung'     => '#E2EADD',
            'wa'            => '#25D366',
        ],
        'dusty_pink' => [
            'label'         => 'Dusty Pink',
            'dasar'         => '#FAF7F2',
            'aksen'         => '#D8A7A7',
            'teks'          => '#4A3F3A',
            'pendukung'     => '#E8DED1',
            'wa'            => '#25D366',
        ],
        'soft_beige' => [
            'label'         => 'Soft Beige',
            'dasar'         => '#FBF8F3',
            'aksen'         => '#D9C3A5',
            'teks'          => '#4A423A',
            'pendukung'     => '#EFE6D8',
            'wa'            => '#25D366',
        ],
        'muted_rose' => [
            'label'         => 'Muted Rose',
            'dasar'         => '#FBF6F5',
            'aksen'         => '#C48A8A',
            'teks'          => '#463A3A',
            'pendukung'     => '#EADAD8',
            'wa'            => '#25D366',
        ],
    ];
}

/**
 * Palet tampilan panel admin — TERPISAH dari palet website publik
 * sehingga perubahan warna admin tidak memengaruhi tampilan publik.
 */
function daftar_palet_admin(): array
{
    return [
        'sage_green' => [
            'label'     => 'Sage Green',
            'dasar'     => '#F5F7F2',
            'aksen'     => '#A9BC9B',
            'teks'      => '#37403A',
            'pendukung' => '#E2EADD',
            'sidebar'   => '#3C4A38',
        ],
        'dusty_pink' => [
            'label'     => 'Dusty Pink',
            'dasar'     => '#FAF7F2',
            'aksen'     => '#D8A7A7',
            'teks'      => '#4A3F3A',
            'pendukung' => '#E8DED1',
            'sidebar'   => '#4A3F3A',
        ],
        'soft_beige' => [
            'label'     => 'Soft Beige',
            'dasar'     => '#FBF8F3',
            'aksen'     => '#D9C3A5',
            'teks'      => '#4A423A',
            'pendukung' => '#EFE6D8',
            'sidebar'   => '#4A423A',
        ],
        'muted_rose' => [
            'label'     => 'Muted Rose',
            'dasar'     => '#FBF6F5',
            'aksen'     => '#C48A8A',
            'teks'      => '#463A3A',
            'pendukung' => '#EADAD8',
            'sidebar'   => '#463A3A',
        ],
        'soft_blue' => [
            'label'     => 'Soft Blue',
            'dasar'     => '#F4F7FA',
            'aksen'     => '#A9C1D4',
            'teks'      => '#35404A',
            'pendukung' => '#E1EAF2',
            'sidebar'   => '#36414D',
        ],
    ];
}

/** Kembalikan set variabel CSS untuk palet terpilih. */
function palet_variabel(string $palet): array
{
    $semua = daftar_palet();
    $p = $semua[$palet] ?? $semua['sage_green'];
    return [
        '--warna-dasar'     => $p['dasar'],
        '--warna-aksen'     => $p['aksen'],
        '--warna-teks'      => $p['teks'],
        '--warna-pendukung' => $p['pendukung'],
        '--warna-wa'        => $p['wa'],
    ];
}

/**
 * URL file hasil unggahan. Mengecek subfolder yang dipakai aplikasi
 * (uploads/toko, uploads/produk) lalu folder uploads sebagai cadangan.
 */
function url_upload(string $namaFile): string
{
    $namaFile = basename($namaFile);
    if ($namaFile === '') {
        return '';
    }
    foreach (['toko', 'produk', ''] as $sub) {
        $dir = UPLOAD_DIR . ($sub !== '' ? DIRECTORY_SEPARATOR . $sub : '');
        if (is_file($dir . DIRECTORY_SEPARATOR . $namaFile)) {
            return base_url('uploads/' . ($sub !== '' ? $sub . '/' : '') . rawurlencode($namaFile));
        }
    }
    return base_url('uploads/' . rawurlencode($namaFile));
}

/** URL logo toko dari pengaturan (null bila belum ada). */
function logo_toko(?array $toko): ?string
{
    $nama = (string) ($toko['logo'] ?? '');
    return $nama !== '' ? url_upload($nama) : null;
}

/** URL banner beranda dari pengaturan (null bila belum ada). */
function banner_toko(?array $toko): ?string
{
    $nama = (string) ($toko['banner'] ?? '');
    return $nama !== '' ? url_upload($nama) : null;
}

/**
 * Tingkat blur gambar latar hero (0-10 px).
 * Nilai di luar rentang dipotong ke batas terdekat supaya tampilan tidak rusak.
 */
function hero_blur(?array $toko): int
{
    $nilai = (int) ($toko['hero_blur'] ?? HERO_BLUR_DEFAULT);
    return max(HERO_BLUR_MIN, min(HERO_BLUR_MAX, $nilai));
}

/**
 * Transparansi lapisan putih di atas gambar latar hero (0-90 persen).
 * 0% = lapisan putih pekat (foto tertutup), 90% = lapisan hampir tak terlihat.
 */
function hero_transparansi(?array $toko): int
{
    $nilai = (int) ($toko['hero_transparansi'] ?? HERO_TRANSPARANSI_DEFAULT);
    return max(HERO_TRANSPARANSI_MIN, min(HERO_TRANSPARANSI_MAX, $nilai));
}

/**
 * Opasitas lapisan putih hero dalam persen (0-100).
 * Nilainya = 100 - transparansi, dipakai sebagai alpha gradient overlay.
 */
function hero_lapisan_opasitas(?array $toko): int
{
    return 100 - hero_transparansi($toko);
}

/**
 * URL profil TikTok dari pengaturan (null bila belum diisi).
 * Menerima tautan penuh (https://...) atau nama pengguna (mis. "@toko" / "toko").
 */
function tiktok_url(?string $nilai): ?string
{
    $nilai = trim((string) $nilai);
    if ($nilai === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $nilai)) {
        return $nilai;
    }
    return 'https://www.tiktok.com/@' . ltrim($nilai, '@');
}

/** Teks tampilan untuk tautan TikTok (nama pengguna bila bukan URL penuh). */
function tiktok_label(?string $nilai): ?string
{
    $nilai = trim((string) $nilai);
    if ($nilai === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $nilai)) {
        return $nilai;
    }
    return '@' . ltrim($nilai, '@');
}

/** Ambil pengaturan toko (baris id=1), dengan cache. */
function ambil_pengaturan(?PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    if ($pdo instanceof PDO) {
        try {
            $stmt = $pdo->query('SELECT * FROM pengaturan_toko WHERE id = 1 LIMIT 1');
            $row = $stmt->fetch();
            if ($row) {
                $cache = $row;
                return $row;
            }
        } catch (Throwable $ex) {
            error_log('[fungsi] pengaturan: ' . $ex->getMessage());
        }
    }

    // Database tidak tersedia -> pakai data contoh (mode_contoh()).
    if (function_exists('repo_pengaturan')) {
        $cache = repo_pengaturan();
        return $cache;
    }

    $cache = [
        'nama_toko'           => 'Blessing Bouquet',
        'whatsapp'            => '6285920064874',
        'palet_warna'         => 'sage_green',
        'teks_tombol_katalog' => 'Lihat Katalog',
        'headline'            => '',
        'deskripsi_beranda'   => '',
        'hero_blur'           => HERO_BLUR_DEFAULT,
        'hero_transparansi'   => HERO_TRANSPARANSI_DEFAULT,
        'judul_produk_unggulan' => 'Produk Unggulan',
        'deskripsi_produk_unggulan' => 'Temukan buket favorit untuk orang tersayang dan jadikan setiap momen lebih berkesan.',
    ];
    return $cache;
}
