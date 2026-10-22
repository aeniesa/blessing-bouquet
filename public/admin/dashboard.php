<?php
/**
 * admin/dashboard.php — Ringkasan data (A-02).
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/admin-fungsi.php';

require_login();

if (db_tersedia() && $pdo instanceof PDO) {
    $totalProduk   = (int) $pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn();
    $produkAktif   = (int) $pdo->query('SELECT COUNT(*) FROM produk WHERE is_aktif = 1')->fetchColumn();
    $totalKategori = (int) $pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
} else {
    $d = data_contoh();
    $totalProduk   = count($d['produk']);
    $produkAktif   = count(array_filter($d['produk'], static fn ($p) => (int) $p['is_aktif'] === 1));
    $totalKategori = count($d['kategori']);
}

$judulAdmin = 'Dashboard';
$aktif = 'dashboard';
require dirname(__DIR__, 2) . '/includes/admin-header.php';

$notis = admin_flash_ambil();
?>
<?php if ($notis !== null): ?>
    <div class="notis notis-<?= e($notis['tipe']) ?>" role="status"><?= e($notis['pesan']) ?></div>
<?php endif; ?>

<div class="grid-statistik">
    <div class="kartu-statistik">
        <span class="stat-label">Produk Aktif</span>
        <span class="stat-nilai"><?= $produkAktif ?></span>
    </div>
    <div class="kartu-statistik">
        <span class="stat-label">Total Produk</span>
        <span class="stat-nilai"><?= $totalProduk ?></span>
    </div>
    <div class="kartu-statistik">
        <span class="stat-label">Kategori</span>
        <span class="stat-nilai"><?= $totalKategori ?></span>
    </div>
</div>

<section class="admin-panel">
    <h2>Selamat datang</h2>
    <p>Kelola katalog dan pengaturan Blessing Bouquet dari panel ini.</p>
    <p class="catatan">Menu <strong>Produk</strong>, <strong>Kategori</strong>, <strong>Pengaturan Website</strong>,
        <strong>Tampilan Admin</strong>, dan <strong>Akun</strong> tersedia di bilah kiri.</p>
</section>
<?php require dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
