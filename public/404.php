<?php
/**
 * 404.php — Halaman tidak ditemukan (F-10).
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

http_response_code(404);

$judulHalaman = 'Halaman Tidak Ditemukan';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="bagian">
    <div class="wadah" style="text-align:center; max-width:640px;">
        <p class="hero-eyebrow">Error 404</p>
        <h1 class="hero-judul" style="font-size: clamp(2rem, 5vw, 3rem);">Halaman tidak ditemukan</h1>
        <p class="hero-deskripsi" style="margin-inline:auto;">Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
        <p><a class="tombol tombol-utama" href="<?= e(base_url('index.php')) ?>">Kembali ke Beranda</a></p>
    </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
