<?php
/**
 * admin/ulasan.php — Manajemen ulasan (A-07): tambah, edit, tampil/sembunyi, hapus.
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/admin-fungsi.php';

require_login();

$pesanError = '';
$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = (string) ($_POST['aksi'] ?? '');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $pesanError = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif (!db_tersedia() || !$pdo instanceof PDO) {
        $pesanError = admin_pesan_tidak_simpan();
    } elseif ($aksi === 'simpan') {
        $id    = (int) ($_POST['id'] ?? 0);
        $nama  = trim((string) ($_POST['nama_tampilan'] ?? ''));
        $isi   = trim((string) ($_POST['isi'] ?? ''));
        $tampil = !empty($_POST['is_tampil']) ? 1 : 0;

        if ($nama === '' || $isi === '') {
            $pesanError = 'Nama tampilan dan isi ulasan wajib diisi.';
        } else {
            if ($id > 0) {
                $pdo->prepare('UPDATE ulasan SET nama_tampilan=?, isi=?, is_tampil=?, updated_at=NOW() WHERE id=?')
                    ->execute([$nama, $isi, $tampil, $id]);
                admin_flash_set('sukses', 'Ulasan diperbarui.');
            } else {
                $pdo->prepare('INSERT INTO ulasan (nama_tampilan, isi, is_tampil) VALUES (?, ?, ?)')
                    ->execute([$nama, $isi, $tampil]);
                admin_flash_set('sukses', 'Ulasan ditambahkan.');
            }
            admin_redirect('admin/ulasan.php');
        }
    } elseif ($aksi === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE ulasan SET is_tampil = 1 - is_tampil, updated_at = NOW() WHERE id = ?')->execute([$id]);
        admin_flash_set('sukses', 'Status tampil ulasan diperbarui.');
        admin_redirect('admin/ulasan.php');
    } elseif ($aksi === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM ulasan WHERE id = ?')->execute([$id]);
        admin_flash_set('sukses', 'Ulasan dihapus.');
        admin_redirect('admin/ulasan.php');
    }
}

if (isset($_GET['edit'])) {
    $idEdit = (int) $_GET['edit'];
    foreach (admin_ulasan_semua() as $u) {
        if ((int) $u['id'] === $idEdit) {
            $edit = $u;
            break;
        }
    }
}

$daftar = admin_ulasan_semua();
$notis  = admin_flash_ambil();

$judulAdmin = 'Ulasan';
$aktif = 'ulasan';
require dirname(__DIR__, 2) . '/includes/admin-header.php';
?>
<?php if ($notis !== null): ?>
    <div class="notis notis-<?= e($notis['tipe']) ?>" role="status"><?= e($notis['pesan']) ?></div>
<?php endif; ?>
<?php if ($pesanError !== ''): ?>
    <div class="notis notis-galat" role="alert"><?= e($pesanError) ?></div>
<?php endif; ?>

<section class="admin-panel">
    <h2><?= $edit ? 'Ubah Ulasan' : 'Tambah Ulasan' ?></h2>
    <form method="post" action="<?= e(base_url('admin/ulasan.php')) ?>" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
        <label class="medan">
            <span>Nama Tampilan</span>
            <input type="text" name="nama_tampilan" value="<?= e($edit['nama_tampilan'] ?? '') ?>" required>
        </label>
        <label class="medan">
            <span>Isi Ulasan</span>
            <textarea name="isi" rows="3" required><?= e($edit['isi'] ?? '') ?></textarea>
        </label>
        <label class="kotak-centang">
            <input type="checkbox" name="is_tampil" value="1" <?= ($edit === null || (int) $edit['is_tampil'] === 1) ? 'checked' : '' ?>>
            <span>Tampilkan di beranda</span>
        </label>
        <div class="admin-aksi">
            <button type="submit" class="tombol tombol-utama"><?= $edit ? 'Simpan Perubahan' : 'Tambah' ?></button>
            <?php if ($edit): ?>
                <a class="tombol tombol-lembut" href="<?= e(base_url('admin/ulasan.php')) ?>">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="admin-panel">
    <h2>Daftar Ulasan</h2>
    <div class="tabel-gulir">
        <table class="tabel-admin">
            <thead>
                <tr><th>Nama</th><th>Ulasan</th><th>Tampil</th><th class="kolom-aksi">Aksi</th></tr>
            </thead>
            <tbody>
                <?php if ($daftar === []): ?>
                    <tr><td colspan="4" class="kosong-baris">Belum ada ulasan.</td></tr>
                <?php else: ?>
                    <?php foreach ($daftar as $u): ?>
                        <tr>
                            <td><strong><?= e($u['nama_tampilan']) ?></strong></td>
                            <td><?= e(mb_strimwidth((string) $u['isi'], 0, 90, '…')) ?></td>
                            <td>
                                <form method="post" action="<?= e(base_url('admin/ulasan.php')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="toggle">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="lencana-status <?= (int) $u['is_tampil'] === 1 ? 'on' : 'off' ?>">
                                        <?= (int) $u['is_tampil'] === 1 ? 'Tampil' : 'Disembunyikan' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="kolom-aksi">
                                <a class="tautan-aksi" href="<?= e(base_url('admin/ulasan.php?edit=' . (int) $u['id'])) ?>">Ubah</a>
                                <form method="post" action="<?= e(base_url('admin/ulasan.php')) ?>" onsubmit="return confirm('Hapus ulasan ini?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
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
