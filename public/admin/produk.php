<?php
/**
 * admin/produk.php — Daftar & aksi produk (A-03, bagian daftar).
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/admin-fungsi.php';

require_login();

$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = (string) ($_POST['aksi'] ?? '');
    $id   = (int) ($_POST['id'] ?? 0);

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $pesanError = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif (!db_tersedia() || !$pdo instanceof PDO) {
        $pesanError = admin_pesan_tidak_simpan();
    } elseif ($id > 0) {
        try {
            if ($aksi === 'toggle_aktif') {
                $pdo->prepare('UPDATE produk SET is_aktif = 1 - is_aktif, updated_at = NOW() WHERE id = ?')->execute([$id]);
                admin_flash_set('sukses', 'Status aktif produk diperbarui.');
            } elseif ($aksi === 'toggle_unggulan') {
                $pdo->prepare('UPDATE produk SET is_unggulan = 1 - is_unggulan, updated_at = NOW() WHERE id = ?')->execute([$id]);
                admin_flash_set('sukses', 'Status unggulan produk diperbarui.');
            } elseif ($aksi === 'hapus') {
                $pdo->prepare('DELETE FROM produk WHERE id = ?')->execute([$id]);
                admin_flash_set('sukses', 'Produk dihapus.');
            }
            admin_redirect('admin/produk.php');
        } catch (Throwable $ex) {
            $pesanError = 'Aksi gagal: ' . $ex->getMessage();
        }
    }
}

$produk        = admin_produk_semua();
$variasiPer    = repo_variasi_per_produk();
$notis         = admin_flash_ambil();

$judulAdmin = 'Produk';
$aktif = 'produk';
require dirname(__DIR__, 2) . '/includes/admin-header.php';
?>
<?php if ($notis !== null): ?>
    <div class="notis notis-<?= e($notis['tipe']) ?>" role="status"><?= e($notis['pesan']) ?></div>
<?php endif; ?>
<?php if ($pesanError !== ''): ?>
    <div class="notis notis-galat" role="alert"><?= e($pesanError) ?></div>
<?php endif; ?>

<section class="admin-panel">
    <div class="panel-kepala">
        <h2>Daftar Produk</h2>
        <a class="tombol tombol-utama" href="<?= e(base_url('admin/produk-form.php')) ?>">Tambah Produk</a>
    </div>
    <div class="tabel-gulir">
        <table class="tabel-admin">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Aktif</th>
                    <th>Unggulan</th>
                    <th class="kolom-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($produk === []): ?>
                    <tr><td colspan="6" class="kosong-baris">Belum ada produk.</td></tr>
                <?php else: ?>
                    <?php foreach ($produk as $p): ?>
                        <?php $variasi = $variasiPer[(int) $p['id']] ?? []; ?>
                        <tr>
                            <td>
                                <strong><?= e($p['nama']) ?></strong><br>
                                <code><?= e($p['slug']) ?></code>
                            </td>
                            <td><?= e($p['kategori_nama'] ?? '') ?></td>
                            <td><?= e(harga_produk($p, $variasi)) ?></td>
                            <td>
                                <form method="post" action="<?= e(base_url('admin/produk.php')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="toggle_aktif">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="lencana-status <?= (int) $p['is_aktif'] === 1 ? 'on' : 'off' ?>">
                                        <?= (int) $p['is_aktif'] === 1 ? 'Aktif' : 'Nonaktif' ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="post" action="<?= e(base_url('admin/produk.php')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="toggle_unggulan">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="lencana-status <?= (int) $p['is_unggulan'] === 1 ? 'on' : 'off' ?>">
                                        <?= (int) $p['is_unggulan'] === 1 ? 'Ya' : 'Tidak' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="kolom-aksi">
                                <a class="tautan-aksi" href="<?= e(base_url('admin/produk-form.php?id=' . (int) $p['id'])) ?>">Ubah</a>
                                <form method="post" action="<?= e(base_url('admin/produk.php')) ?>" onsubmit="return confirm('Hapus produk ini beserta variasi & gambarnya?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="tautan-aksi tautan-bahaya">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
