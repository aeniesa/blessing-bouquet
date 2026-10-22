<?php
/**
 * admin/tampilan-admin.php — Pengaturan Tampilan Admin (terpisah dari
 * Pengaturan Website): warna sidebar, latar, tombol, dan elemen panel admin.
 * Tidak memengaruhi warna website publik.
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/admin-fungsi.php';

require_login();

$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $palet = (string) ($_POST['palet'] ?? '');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $pesanError = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif (!db_tersedia() || !$pdo instanceof PDO) {
        $pesanError = admin_pesan_tidak_simpan();
    } elseif (!isset(daftar_palet_admin()[$palet])) {
        $pesanError = 'Pilihan warna tidak valid.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO pengaturan_admin (id, palet, updated_at) VALUES (1, ?, NOW())
                 ON DUPLICATE KEY UPDATE palet = VALUES(palet), updated_at = NOW()'
            );
            $stmt->execute([$palet]);
            admin_flash_set('sukses', 'Tampilan admin disimpan.');
            admin_redirect('admin/tampilan-admin.php');
        } catch (Throwable $ex) {
            $pesanError = 'Gagal menyimpan tampilan admin: ' . $ex->getMessage();
        }
    }
}

$paletAktif = admin_palet_aktif();
$palets     = daftar_palet_admin();
$notis      = admin_flash_ambil();

$judulAdmin = 'Tampilan Admin';
$aktif = 'tampilan-admin';
require dirname(__DIR__, 2) . '/includes/admin-header.php';
?>
<?php if ($notis !== null): ?>
    <div class="notis notis-<?= e($notis['tipe']) ?>" role="status"><?= e($notis['pesan']) ?></div>
<?php endif; ?>
<?php if ($pesanError !== ''): ?>
    <div class="notis notis-galat" role="alert"><?= e($pesanError) ?></div>
<?php endif; ?>

<section class="admin-panel" style="max-width: 760px;">
    <h2>Pengaturan Tampilan Admin</h2>
    <p class="catatan">
        Pilihan warna di bawah hanya berlaku untuk panel admin (sidebar, latar, tombol, dan elemen panel).
        Warna website publik diatur melalui menu <strong>Pengaturan Website</strong> dan tidak berpengaruh ke sini.
    </p>
    <form method="post" action="<?= e(base_url('admin/tampilan-admin.php')) ?>" class="admin-form">
        <?= csrf_field() ?>
        <div class="grup-palet-admin">
            <?php foreach ($palets as $slug => $p): ?>
                <label class="kartu-palet-admin<?= $paletAktif === $slug ? ' terpilih' : '' ?>">
                    <input type="radio" name="palet" value="<?= e($slug) ?>" <?= $paletAktif === $slug ? 'checked' : '' ?>>
                    <span class="pratinjau-warna" aria-hidden="true">
                        <span class="pratinjau-samping" style="background: <?= e($p['sidebar']) ?>"></span>
                        <span class="pratinjau-latar" style="background: <?= e($p['dasar']) ?>">
                            <span style="background: <?= e($p['aksen']) ?>"></span>
                        </span>
                    </span>
                    <span class="palet-label"><?= e($p['label']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="admin-aksi">
            <button type="submit" class="tombol tombol-utama">Simpan Tampilan Admin</button>
        </div>
    </form>
</section>
<?php require dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
