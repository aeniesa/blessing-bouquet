<?php
/**
 * admin/akun.php — Manajemen akun admin (A-08):
 * daftar akun (Admin Pertama/Kedua/...), tambah akun, ubah username/password
 * per akun, dan hapus akun (akun yang sedang dipakai tidak bisa dihapus).
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/admin-fungsi.php';

require_login();

$pesanError = '';

function admin_daftar_akun(): array
{
    global $pdo;
    if (!db_tersedia() || !$pdo instanceof PDO) {
        return [];
    }
    try {
        $stmt = $pdo->query('SELECT id, username, created_at, updated_at FROM admin ORDER BY id');
        return $stmt->fetchAll() ?: [];
    } catch (Throwable $ex) {
        error_log('[akun] daftar: ' . $ex->getMessage());
        return [];
    }
}

function admin_label_urutan(int $urutan): string
{
    $kata = [1 => 'Pertama', 'Kedua', 'Ketiga', 'Keempat', 'Kelima', 'Keenam', 'Ketujuh', 'Kedelapan', 'Kesembilan', 'Kesepuluan'];
    return 'Admin ' . ($kata[$urutan] ?? '#' . $urutan);
}

function admin_username_terpakai(string $username, int $kecualiId): bool
{
    global $pdo;
    if (!db_tersedia() || !$pdo instanceof PDO) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT id FROM admin WHERE username = ? AND id <> ? LIMIT 1');
    $stmt->execute([$username, $kecualiId]);
    return (bool) $stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi    = (string) ($_POST['aksi'] ?? '');
    $akunId  = (int) ($_POST['akun_id'] ?? 0);
    $idLogin = (int) ($_SESSION['admin_id'] ?? 0);

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $pesanError = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif (!db_tersedia() || !$pdo instanceof PDO) {
        $pesanError = admin_pesan_tidak_simpan();
    } elseif ($aksi === 'hapus_akun') {
        if ($akunId === $idLogin) {
            $pesanError = 'Akun yang sedang dipakai tidak dapat dihapus.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT id FROM admin WHERE id = ?');
                $stmt->execute([$akunId]);
                if (!$stmt->fetchColumn()) {
                    $pesanError = 'Akun tidak ditemukan.';
                } else {
                    $pdo->prepare('DELETE FROM admin WHERE id = ?')->execute([$akunId]);
                    admin_flash_set('sukses', 'Akun dihapus.');
                    admin_redirect('admin/akun.php');
                }
            } catch (Throwable $ex) {
                $pesanError = 'Gagal menghapus akun.';
            }
        }
    } elseif ($aksi === 'simpan_akun') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $baru     = (string) ($_POST['password_baru'] ?? '');
        $konfirm  = (string) ($_POST['password_konfirmasi'] ?? '');
        $isBaru   = $akunId <= 0;

        if ($username === '') {
            $pesanError = 'Username wajib diisi.';
        } elseif (strlen($username) > 50) {
            $pesanError = 'Username maksimal 50 karakter.';
        } elseif (admin_username_terpakai($username, $akunId)) {
            $pesanError = 'Username sudah dipakai akun lain.';
        } elseif ($isBaru && $baru === '') {
            $pesanError = 'Password wajib diisi untuk akun baru.';
        } elseif ($baru !== '' && strlen($baru) < 8) {
            $pesanError = 'Password minimal 8 karakter.';
        } elseif ($baru !== '' && $baru !== $konfirm) {
            $pesanError = 'Konfirmasi password tidak cocok.';
        } else {
            try {
                if ($isBaru) {
                    $stmt = $pdo->prepare(
                        'INSERT INTO admin (username, password_hash, created_at, updated_at)
                         VALUES (?, ?, NOW(), NOW())'
                    );
                    $stmt->execute([$username, password_hash($baru, PASSWORD_DEFAULT)]);
                    admin_flash_set('sukses', 'Akun admin ditambahkan.');
                    admin_redirect('admin/akun.php');
                } else {
                    $stmt = $pdo->prepare('SELECT id FROM admin WHERE id = ?');
                    $stmt->execute([$akunId]);
                    if (!$stmt->fetchColumn()) {
                        $pesanError = 'Akun tidak ditemukan.';
                    } else {
                        if ($baru !== '') {
                            $pdo->prepare('UPDATE admin SET username = ?, password_hash = ?, updated_at = NOW() WHERE id = ?')
                                ->execute([$username, password_hash($baru, PASSWORD_DEFAULT), $akunId]);
                        } else {
                            $pdo->prepare('UPDATE admin SET username = ?, updated_at = NOW() WHERE id = ?')
                                ->execute([$username, $akunId]);
                        }
                        if ($akunId === $idLogin) {
                            $_SESSION['admin_username'] = $username;
                        }
                        admin_flash_set('sukses', 'Akun diperbarui.');
                        admin_redirect('admin/akun.php');
                    }
                }
            } catch (PDOException $ex) {
                $pesanError = (string) $ex->getCode() === '23000'
                    ? 'Username sudah dipakai akun lain.'
                    : 'Gagal menyimpan akun.';
            } catch (Throwable $ex) {
                $pesanError = 'Gagal menyimpan akun.';
            }
        }
    }
}

$akunSemua  = admin_daftar_akun();
$idLogin    = (int) ($_SESSION['admin_id'] ?? 0);
$editAkun   = null;
if (isset($_GET['edit'])) {
    foreach ($akunSemua as $a) {
        if ((int) $a['id'] === (int) $_GET['edit']) {
            $editAkun = $a;
            break;
        }
    }
}
$notis = admin_flash_ambil();

$judulAdmin = 'Akun Admin';
$aktif = 'akun';
require dirname(__DIR__, 2) . '/includes/admin-header.php';
?>
<?php if ($notis !== null): ?>
    <div class="notis notis-<?= e($notis['tipe']) ?>" role="status"><?= e($notis['pesan']) ?></div>
<?php endif; ?>
<?php if ($pesanError !== ''): ?>
    <div class="notis notis-galat" role="alert"><?= e($pesanError) ?></div>
<?php endif; ?>

<section class="admin-panel">
    <h2>Daftar Akun</h2>
    <?php if ($akunSemua === []): ?>
        <p class="kosong-baris">Belum ada akun.</p>
    <?php else: ?>
        <div class="tabel-gulir">
            <table class="tabel-admin">
                <thead>
                    <tr><th>No.</th><th>Username</th><th>Peran</th><th>Diperbarui</th><th class="kolom-aksi">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($akunSemua as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($a['username']) ?><?= (int) $a['id'] === $idLogin ? ' <small>(Anda)</small>' : '' ?></td>
                            <td><?= e(admin_label_urutan($i + 1)) ?></td>
                            <td><?= $a['updated_at'] !== null ? e((string) $a['updated_at']) : '—' ?></td>
                            <td class="kolom-aksi">
                                <a class="tautan-aksi" href="<?= e(base_url('admin/akun.php?edit=' . (int) $a['id'])) ?>">Ubah</a>
                                <?php if ((int) $a['id'] !== $idLogin): ?>
                                    <form method="post" action="<?= e(base_url('admin/akun.php')) ?>" onsubmit="return confirm('Hapus akun ini?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="hapus_akun">
                                        <input type="hidden" name="akun_id" value="<?= (int) $a['id'] ?>">
                                        <button type="submit" class="tautan-aksi tautan-bahaya">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="catatan">Urutan penomoran (Admin Pertama, Admin Kedua, ...) mengikuti urutan akun dibuat.
            Akun yang sedang dipakai tidak dapat dihapus.</p>
    <?php endif; ?>
</section>

<section class="admin-panel" style="max-width: 560px;">
    <h2><?= $editAkun !== null ? 'Ubah Akun' : 'Tambah Akun' ?></h2>
    <form method="post" action="<?= e(base_url('admin/akun.php')) ?>" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan_akun">
        <input type="hidden" name="akun_id" value="<?= (int) ($editAkun['id'] ?? 0) ?>">
        <label class="medan">
            <span>Username</span>
            <input type="text" name="username" maxlength="50" required
                   value="<?= e($editAkun['username'] ?? ($_SESSION['admin_username'] ?? '')) ?>">
        </label>
        <label class="medan">
            <span><?= $editAkun !== null ? 'Password Baru (kosongkan bila tidak diubah)' : 'Password' ?></span>
            <input type="password" name="password_baru" minlength="8" autocomplete="new-password"
                   <?= $editAkun === null ? 'required' : '' ?>>
        </label>
        <label class="medan">
            <span>Konfirmasi Password</span>
            <input type="password" name="password_konfirmasi" minlength="8" autocomplete="new-password"
                   <?= $editAkun === null ? 'required' : '' ?>>
        </label>
        <div class="admin-aksi">
            <button type="submit" class="tombol tombol-utama"><?= $editAkun !== null ? 'Simpan Perubahan' : 'Tambah Akun' ?></button>
            <?php if ($editAkun !== null): ?>
                <a class="tombol tombol-lembut" href="<?= e(base_url('admin/akun.php')) ?>">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</section>
<?php require dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
