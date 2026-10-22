<?php
/**
 * admin/kategori.php — CRUD kategori (A-04) dengan proteksi hapus
 * saat masih dipakai produk (ON DELETE RESTRICT).
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
        $id       = (int) ($_POST['id'] ?? 0);
        $nama     = trim((string) ($_POST['nama'] ?? ''));
        $slugIn   = trim((string) ($_POST['slug'] ?? ''));
        $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));

        if ($nama === '') {
            $pesanError = 'Nama kategori wajib diisi.';
        } else {
            $slug = $slugIn !== '' ? buat_slug($slugIn) : buat_slug($nama);
            $slug = slug_unik($pdo, 'kategori', $slug, $id > 0 ? $id : null);

            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE kategori SET nama = ?, slug = ?, deskripsi = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$nama, $slug, $deskripsi !== '' ? $deskripsi : null, $id]);
                admin_flash_set('sukses', 'Kategori diperbarui.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO kategori (nama, slug, deskripsi) VALUES (?, ?, ?)');
                $stmt->execute([$nama, $slug, $deskripsi !== '' ? $deskripsi : null]);
                admin_flash_set('sukses', 'Kategori ditambahkan.');
            }
            admin_redirect('admin/kategori.php');
        }
    } elseif ($aksi === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        $cek = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE kategori_id = ?');
        $cek->execute([$id]);
        if ((int) $cek->fetchColumn() > 0) {
            $pesanError = 'Kategori tidak dapat dihapus karena masih dipakai produk.';
        } else {
            try {
                $stmt = $pdo->prepare('DELETE FROM kategori WHERE id = ?');
                $stmt->execute([$id]);
                admin_flash_set('sukses', 'Kategori dihapus.');
            } catch (Throwable $ex) {
                $pesanError = 'Kategori tidak dapat dihapus.';
            }
            admin_redirect('admin/kategori.php');
        }
    }
}

if (isset($_GET['edit'])) {
    $idEdit = (int) $_GET['edit'];
    foreach (repo_kategori() as $k) {
        if ((int) $k['id'] === $idEdit) {
            $edit = $k;
            break;
        }
    }
}

$daftar = repo_kategori();
$notis  = admin_flash_ambil();

$judulAdmin = 'Kategori';
$aktif = 'kategori';
require dirname(__DIR__, 2) . '/includes/admin-header.php';
?>
<?php if ($notis !== null): ?>
    <div class="notis notis-<?= e($notis['tipe']) ?>" role="status"><?= e($notis['pesan']) ?></div>
<?php endif; ?>
<?php if ($pesanError !== ''): ?>
    <div class="notis notis-galat" role="alert"><?= e($pesanError) ?></div>
<?php endif; ?>

<section class="admin-panel">
    <h2><?= $edit ? 'Ubah Kategori' : 'Tambah Kategori' ?></h2>
    <form method="post" action="<?= e(base_url('admin/kategori.php')) ?>" class="admin-form admin-form-inline">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
        <div class="form-grid">
            <label class="medan">
                <span>Nama</span>
                <input type="text" name="nama" value="<?= e($edit['nama'] ?? '') ?>" required>
            </label>
            <label class="medan">
                <span>Slug (opsional)</span>
                <input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="otomatis dari nama">
            </label>
        </div>
        <label class="medan">
            <span>Deskripsi</span>
            <textarea name="deskripsi" rows="2"><?= e($edit['deskripsi'] ?? '') ?></textarea>
        </label>
        <div class="admin-aksi">
            <button type="submit" class="tombol tombol-utama"><?= $edit ? 'Simpan Perubahan' : 'Tambah' ?></button>
            <?php if ($edit): ?>
                <a class="tombol tombol-lembut" href="<?= e(base_url('admin/kategori.php')) ?>">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="admin-panel">
    <h2>Daftar Kategori</h2>
    <div class="tabel-gulir">
        <table class="tabel-admin">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Produk Aktif</th>
                    <th class="kolom-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftar === []): ?>
                    <tr><td colspan="4" class="kosong-baris">Belum ada kategori.</td></tr>
                <?php else: ?>
                    <?php foreach ($daftar as $k): ?>
                        <tr>
                            <td><strong><?= e($k['nama']) ?></strong></td>
                            <td><code><?= e($k['slug']) ?></code></td>
                            <td><?= (int) ($k['jumlah'] ?? 0) ?></td>
                            <td class="kolom-aksi">
                                <a class="tautan-aksi" href="<?= e(base_url('admin/kategori.php?edit=' . (int) $k['id'])) ?>">Ubah</a>
                                <form method="post" action="<?= e(base_url('admin/kategori.php')) ?>" onsubmit="return confirm('Hapus kategori ini?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id" value="<?= (int) $k['id'] ?>">
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
