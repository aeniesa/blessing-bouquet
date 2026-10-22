<?php
/**
 * header.php — header publik.
 * Variabel opsional sebelum include: $judulHalaman (string).
 */
if (!function_exists('ambil_pengaturan')) {
    require_once __DIR__ . '/bootstrap.php';
}

$__toko     = ambil_pengaturan($pdo);
$__namaToko = $__toko['nama_toko'] ?: 'Blessing Bouquet';
$__palet    = palet_variabel($__toko['palet_warna'] ?? 'sage_green');
$__logo     = logo_toko($__toko);
$__judul    = isset($judulHalaman) && $judulHalaman !== ''
    ? $judulHalaman . ' — ' . $__namaToko
    : $__namaToko;
$__beranda  = base_url('index.php');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($__judul) ?></title>
    <meta name="description" content="Katalog dan pemesanan buket Blessing Bouquet melalui WhatsApp.">
    <meta name="theme-color" content="<?= e($__palet['--warna-aksen']) ?>">
    <link rel="manifest" href="<?= e(aset('manifest.json')) ?>">
    <link rel="icon" href="<?= e(aset('assets/img/icon-192.png')) ?>" sizes="192x192">
    <link rel="apple-touch-icon" href="<?= e(aset('assets/img/icon-192.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Nunito+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(aset_berversi('assets/css/style.css')) ?>">
    <style>:root {
        <?php foreach ($__palet as $__k => $__v): ?><?= e($__k) ?>: <?= e($__v) ?>;<?php endforeach; ?>
    }</style>
</head>
<body>
<a class="skip-link" href="#konten">Lewati ke konten</a>
<?php if (defined('MODE_CONTOH') && MODE_CONTOH): ?>
<div class="pita-contoh" role="status">
    <strong>MODE CONTOH</strong> — database belum tersedia. Menampilkan data contoh sementara (sumber: <code>database/database.sql</code>).
</div>
<?php endif; ?>
<header class="situs-header">
    <div class="wadah header-dalam">
        <a class="merek" href="<?= e($__beranda) ?>">
            <?php if ($__logo !== null): ?>
                <img class="merek-logo" src="<?= e($__logo) ?>" alt="Logo <?= e($__namaToko) ?>">
            <?php endif; ?>
            <span class="merek-teks">
                <span class="merek-nama"><?= e($__namaToko) ?></span>
                <span class="merek-tagline">Bouquet and Special Moments</span>
            </span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-utama">Menu</button>
        <nav id="nav-utama" class="nav-utama" aria-label="Navigasi utama">
            <a href="<?= e(base_url('index.php')) ?>">Beranda</a>
            <a href="<?= e(base_url('katalog.php')) ?>">Katalog</a>
            <a href="<?= e(base_url('tentang.php')) ?>">Tentang</a>
            <a href="<?= e(base_url('kontak.php')) ?>">Kontak</a>
        </nav>
    </div>
</header>
<main id="konten">
