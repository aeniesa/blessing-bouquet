<?php
/**
 * admin-header.php — header panel admin.
 * Dipakai setelah require_login(). Variabel opsional: $judulAdmin (string), $aktif (string).
 */
if (!function_exists('ambil_pengaturan')) {
    require_once __DIR__ . '/bootstrap.php';
}
if (!function_exists('admin_palet_aktif')) {
    require_once __DIR__ . '/admin-fungsi.php';
}

$__toko      = ambil_pengaturan($pdo);
$__namaToko  = $__toko['nama_toko'] ?: 'Blessing Bouquet';
$__paletAdm  = admin_palet_variabel(admin_palet_aktif());
$__judul     = isset($judulAdmin) && $judulAdmin !== '' ? $judulAdmin : 'Panel Admin';
$__aktif     = $aktif ?? '';
$__username  = $_SESSION['admin_username'] ?? 'admin';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($__judul) ?> — <?= e($__namaToko) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Nunito+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(aset('admin/assets/css/admin.css')) ?>">
    <style>:root {
        <?php foreach ($__paletAdm as $__k => $__v): ?><?= e($__k) ?>: <?= e($__v) ?>;<?php endforeach; ?>
    }</style>
</head>
<body class="admin">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-merek">
            <span class="admin-merek-nama"><?= e($__namaToko) ?></span>
            <span class="admin-merek-sub">Panel Admin</span>
        </div>
        <nav class="admin-nav" aria-label="Navigasi admin">
            <a class="admin-nav-item <?= $__aktif === 'dashboard' ? 'aktif' : '' ?>" href="<?= e(base_url('admin/dashboard.php')) ?>">Dashboard</a>
            <a class="admin-nav-item <?= $__aktif === 'produk' ? 'aktif' : '' ?>" href="<?= e(base_url('admin/produk.php')) ?>">Produk</a>
            <a class="admin-nav-item <?= $__aktif === 'kategori' ? 'aktif' : '' ?>" href="<?= e(base_url('admin/kategori.php')) ?>">Kategori</a>
            <a class="admin-nav-item <?= $__aktif === 'pengaturan' ? 'aktif' : '' ?>" href="<?= e(base_url('admin/pengaturan.php')) ?>">Pengaturan Website</a>
            <a class="admin-nav-item <?= $__aktif === 'tampilan-admin' ? 'aktif' : '' ?>" href="<?= e(base_url('admin/tampilan-admin.php')) ?>">Tampilan Admin</a>
            <a class="admin-nav-item <?= $__aktif === 'akun' ? 'aktif' : '' ?>" href="<?= e(base_url('admin/akun.php')) ?>">Akun</a>
            <a class="admin-nav-item" href="<?= e(base_url('index.php')) ?>" target="_blank" rel="noopener">Lihat Website</a>
        </nav>
        <div class="admin-sidebar-bawah">
            <span class="admin-user">Masuk sebagai <strong><?= e($__username) ?></strong></span>
            <a class="admin-nav-item admin-nav-keluar" href="<?= e(base_url('admin/logout.php')) ?>">Keluar</a>
        </div>
    </aside>
    <div class="admin-konten">
        <header class="admin-topbar">
            <h1 class="admin-judul"><?= e($__judul) ?></h1>
            <?php if (defined('MODE_CONTOH') && MODE_CONTOH): ?>
                <p class="admin-contoh-notis">MODE CONTOH — database belum tersedia. Panel dapat dilihat, tetapi perubahan tidak dapat disimpan.</p>
            <?php endif; ?>
        </header>
        <div class="admin-isi">
