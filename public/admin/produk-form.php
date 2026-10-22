<?php
/**
 * admin/produk-form.php — Tambah/ubah produk (A-03): foto, kategori,
 * jenis harga, variasi, aksesori, unggulan, aktif/nonaktif.
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/admin-fungsi.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';

require_login();

$pesanError = '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id'] ?? 0);

function ambil_produk_admin(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    foreach (admin_produk_semua() as $p) {
        if ((int) $p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

$kategoriList = repo_kategori();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = (string) ($_POST['aksi'] ?? 'simpan');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $pesanError = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif (!db_tersedia() || !$pdo instanceof PDO) {
        $pesanError = admin_pesan_tidak_simpan();
    } elseif ($aksi === 'hapus_gambar') {
        $gambarId = (int) ($_POST['gambar_id'] ?? 0);
        try {
            $stmt = $pdo->prepare('SELECT nama_file FROM gambar_produk WHERE id = ?');
            $stmt->execute([$gambarId]);
            $namaFile = (string) $stmt->fetchColumn();
            $pdo->prepare('DELETE FROM gambar_produk WHERE id = ?')->execute([$gambarId]);
            if ($namaFile !== '') {
                hapus_gambar(UPLOAD_DIR . DIRECTORY_SEPARATOR . 'produk', $namaFile);
                hapus_gambar(UPLOAD_DIR, $namaFile);
            }
            admin_flash_set('sukses', 'Gambar dihapus.');
        } catch (Throwable $ex) {
            $pesanError = 'Gagal menghapus gambar.';
        }
        if ($pesanError === '') {
            admin_redirect('admin/produk-form.php?id=' . $id);
        }
    } else {
        $kategoriId = (int) ($_POST['kategori_id'] ?? 0);
        $nama       = trim((string) ($_POST['nama'] ?? ''));
        $slugIn     = trim((string) ($_POST['slug'] ?? ''));
        $deskripsi  = trim((string) ($_POST['deskripsi'] ?? ''));
        $jenis      = (string) ($_POST['jenis_harga'] ?? 'tetap');
        $hargaTetap = harga_ke_desimal((string) ($_POST['harga_tetap'] ?? ''));
        $hargaMulai = harga_ke_desimal((string) ($_POST['harga_mulai'] ?? ''));
        $ketentuan  = trim((string) ($_POST['ketentuan'] ?? ''));
        $isAktif    = !empty($_POST['is_aktif']) ? 1 : 0;
        $isUnggulan = !empty($_POST['is_unggulan']) ? 1 : 0;

        $kategoriValid = false;
        foreach ($kategoriList as $k) {
            if ((int) $k['id'] === $kategoriId) {
                $kategoriValid = true;
                break;
            }
        }

        if ($nama === '') {
            $pesanError = 'Nama produk wajib diisi.';
        } elseif (!$kategoriValid) {
            $pesanError = 'Kategori wajib dipilih.';
        } elseif (!admin_jenis_harga_valid($jenis)) {
            $pesanError = 'Jenis harga tidak valid.';
        } elseif ($jenis === 'tetap' && $hargaTetap === null) {
            $pesanError = 'Harga tetap wajib diisi untuk jenis "Harga tetap".';
        } elseif ($jenis === 'mulai_dari' && $hargaMulai === null) {
            $pesanError = 'Harga mulai wajib diisi untuk jenis "Mulai dari".';
        } elseif (($gagalFoto = admin_gambar_bolos($_FILES['gambar'] ?? null)) !== '') {
            $pesanError = $gagalFoto;
        } else {
            $slug = $slugIn !== '' ? buat_slug($slugIn) : buat_slug($nama);
            $slug = slug_unik($pdo, 'produk', $slug, $id > 0 ? $id : null);

            if ($jenis !== 'tetap') {
                $hargaTetap = null;
            }
            if ($jenis !== 'mulai_dari') {
                $hargaMulai = null;
            }

            try {
                $pdo->beginTransaction();

                if ($id > 0) {
                    $stmt = $pdo->prepare(
                        'UPDATE produk SET kategori_id=?, nama=?, slug=?, deskripsi=?, jenis_harga=?,
                            harga_tetap=?, harga_mulai=?, is_aktif=?, is_unggulan=?, ketentuan=?, updated_at=NOW()
                         WHERE id=?'
                    );
                    $stmt->execute([
                        $kategoriId, $nama, $slug, $deskripsi, $jenis,
                        $hargaTetap, $hargaMulai, $isAktif, $isUnggulan,
                        $ketentuan !== '' ? $ketentuan : null, $id,
                    ]);
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO produk (kategori_id, nama, slug, deskripsi, jenis_harga,
                            harga_tetap, harga_mulai, is_aktif, is_unggulan, ketentuan)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        $kategoriId, $nama, $slug, $deskripsi, $jenis,
                        $hargaTetap, $hargaMulai, $isAktif, $isUnggulan,
                        $ketentuan !== '' ? $ketentuan : null,
                    ]);
                    $id = (int) $pdo->lastInsertId();
                }

                $pdo->prepare('DELETE FROM variasi_produk WHERE produk_id = ?')->execute([$id]);
                $vNama  = (array) ($_POST['variasi_nama'] ?? []);
                $vNilai = (array) ($_POST['variasi_nilai'] ?? []);
                $vHarga = (array) ($_POST['variasi_harga'] ?? []);
                $insV = $pdo->prepare('INSERT INTO variasi_produk (produk_id, nama_variasi, nilai, harga, urutan) VALUES (?, ?, ?, ?, ?)');
                $urutan = 0;
                foreach ($vNilai as $i => $nilai) {
                    $nilai = trim((string) $nilai);
                    if ($nilai === '') {
                        continue;
                    }
                    $urutan++;
                    $namaV = trim((string) ($vNama[$i] ?? ''));
                    $hargaV = harga_ke_desimal((string) ($vHarga[$i] ?? ''));
                    $insV->execute([$id, $namaV !== '' ? $namaV : 'Variasi', $nilai, $hargaV, $urutan]);
                }

                $pdo->prepare('DELETE FROM produk_aksesori WHERE produk_id = ?')->execute([$id]);
                $aksesoriDipilih = (array) ($_POST['aksesori'] ?? []);
                if ($aksesoriDipilih !== []) {
                    $insA = $pdo->prepare('INSERT INTO produk_aksesori (produk_id, aksesori_id) VALUES (?, ?)');
                    $sah = [];
                    foreach (admin_aksesori_semua() as $a) {
                        $sah[(int) $a['id']] = true;
                    }
                    foreach ($aksesoriDipilih as $aid) {
                        if (isset($sah[(int) $aid])) {
                            $insA->execute([$id, (int) $aid]);
                        }
                    }
                }

                if (is_array($_FILES['gambar']['name'] ?? null)) {
                    $cek = $pdo->prepare('SELECT COUNT(*) FROM gambar_produk WHERE produk_id = ? AND is_utama = 1');
                    $cek->execute([$id]);
                    $adaUtama = (int) $cek->fetchColumn() > 0;
                    $hitung = $pdo->prepare('SELECT COUNT(*) FROM gambar_produk WHERE produk_id = ?');
                    $hitung->execute([$id]);
                    $urutanGambar = (int) $hitung->fetchColumn();
                    $dirFoto = UPLOAD_DIR . DIRECTORY_SEPARATOR . 'produk';
                    $insG = $pdo->prepare('INSERT INTO gambar_produk (produk_id, nama_file, urutan, is_utama) VALUES (?, ?, ?, ?)');
                    $total = count($_FILES['gambar']['name']);
                    $fotoPertama = true;
                    for ($i = 0; $i < $total; $i++) {
                        if (($_FILES['gambar']['name'][$i] ?? '') === '') {
                            continue;
                        }
                        $satu = [
                            'name'     => $_FILES['gambar']['name'][$i],
                            'type'     => $_FILES['gambar']['type'][$i],
                            'tmp_name' => $_FILES['gambar']['tmp_name'][$i],
                            'error'    => $_FILES['gambar']['error'][$i],
                            'size'     => $_FILES['gambar']['size'][$i],
                        ];
                        $namaSimpan = simpan_gambar($satu, $dirFoto);
                        $urutanGambar++;
                        // Foto pertama diunggah menjadi utama bila produk belum punya foto utama.
                        $utama = (!$adaUtama && $fotoPertama) ? 1 : 0;
                        if ($utama === 1) {
                            $adaUtama = true;
                        }
                        $fotoPertama = false;
                        $insG->execute([$id, $namaSimpan, $urutanGambar, $utama]);
                    }
                }

                $pdo->commit();
                admin_flash_set('sukses', 'Produk disimpan.');
                admin_redirect('admin/produk.php');
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $pesanError = 'Gagal menyimpan produk: ' . $ex->getMessage();
            }
        }
    }
}

$produk   = ambil_produk_admin($id);
$variasi  = $produk ? repo_variasi_produk($id) : [];
$aksesori = admin_aksesori_semua();
$terhubung = $produk ? admin_aksesori_terhubung($id) : [];
$gambar   = $produk ? admin_gambar_produk($id) : [];

$barisVariasi = $variasi;
while (count($barisVariasi) < 5) {
    $barisVariasi[] = ['nama_variasi' => '', 'nilai' => '', 'harga' => null];
}

$notis = admin_flash_ambil();

$judulAdmin = $produk ? 'Ubah Produk' : 'Tambah Produk';
$aktif = 'produk';
require dirname(__DIR__, 2) . '/includes/admin-header.php';
?>
<?php if ($notis !== null): ?>
    <div class="notis notis-<?= e($notis['tipe']) ?>" role="status"><?= e($notis['pesan']) ?></div>
<?php endif; ?>
<?php if ($pesanError !== ''): ?>
    <div class="notis notis-galat" role="alert"><?= e($pesanError) ?></div>
<?php endif; ?>

<p><a class="tautan-aksi" href="<?= e(base_url('admin/produk.php')) ?>">&larr; Kembali ke daftar produk</a></p>

<form method="post" action="<?= e(base_url('admin/produk-form.php')) ?>" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id" value="<?= (int) ($produk['id'] ?? 0) ?>">

    <section class="admin-panel">
        <h2>Informasi Produk</h2>
        <div class="form-grid">
            <label class="medan">
                <span>Nama</span>
                <input type="text" name="nama" value="<?= e($produk['nama'] ?? '') ?>" required>
            </label>
            <label class="medan">
                <span>Slug (opsional)</span>
                <input type="text" name="slug" value="<?= e($produk['slug'] ?? '') ?>" placeholder="otomatis dari nama">
            </label>
            <label class="medan">
                <span>Kategori</span>
                <select name="kategori_id" required>
                    <option value="">— pilih —</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= (int) $k['id'] ?>" <?= (int) ($produk['kategori_id'] ?? 0) === (int) $k['id'] ? 'selected' : '' ?>>
                            <?= e($k['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="medan">
                <span>Jenis Harga</span>
                <select name="jenis_harga" id="jenis_harga">
                    <?php
                        $opsiJenis = [
                            'tetap'                   => 'Harga tetap',
                            'mulai_dari'              => 'Mulai dari',
                            'berdasarkan_permintaan'  => 'Berdasarkan permintaan',
                            'biaya_jasa'              => 'Biaya jasa',
                        ];
                        $jenisTerpilih = $produk['jenis_harga'] ?? 'tetap';
                    ?>
                    <?php foreach ($opsiJenis as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= $jenisTerpilih === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="medan" data-harga="tetap">
                <span>Harga Tetap (Rp)</span>
                <input type="number" name="harga_tetap" min="0" step="1"
                    value="<?= ($produk['harga_tetap'] ?? null) !== null ? (0 + $produk['harga_tetap']) : '' ?>">
            </label>
            <label class="medan" data-harga="mulai_dari">
                <span>Harga Mulai (Rp)</span>
                <input type="number" name="harga_mulai" min="0" step="1"
                    value="<?= ($produk['harga_mulai'] ?? null) !== null ? (0 + $produk['harga_mulai']) : '' ?>">
            </label>
        </div>
        <label class="medan">
            <span>Deskripsi</span>
            <textarea name="deskripsi" rows="3" required><?= e($produk['deskripsi'] ?? '') ?></textarea>
        </label>
        <label class="medan">
            <span>Ketentuan (opsional)</span>
            <textarea name="ketentuan" rows="3"><?= e($produk['ketentuan'] ?? '') ?></textarea>
        </label>
        <div class="form-grid">
            <label class="kotak-centang">
                <input type="checkbox" name="is_aktif" value="1" <?= ($produk === null || (int) $produk['is_aktif'] === 1) ? 'checked' : '' ?>>
                <span>Produk aktif</span>
            </label>
            <label class="kotak-centang">
                <input type="checkbox" name="is_unggulan" value="1" <?= ($produk !== null && (int) $produk['is_unggulan'] === 1) ? 'checked' : '' ?>>
                <span>Tampilkan sebagai unggulan</span>
            </label>
        </div>
    </section>

    <section class="admin-panel">
        <h2>Variasi</h2>
        <p class="catatan">Kosongkan <strong>Nilai</strong> bila produk tidak memiliki variasi; baris dengan nilai kosong diabaikan. Kolom <strong>Harga</strong> adalah harga total untuk nilai tersebut (Rp), bukan tambahan harga.</p>
        <div class="tabel-gulir">
            <table class="tabel-admin tabel-variasi">
                <thead>
                    <tr><th>Nama Variasi <small>(mis. Tangkai, Ukuran)</small></th><th>Nilai <small>(mis. 10 tangkai, L)</small></th><th>Harga Total (Rp)</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($barisVariasi as $b): ?>
                        <tr>
                            <td><input type="text" name="variasi_nama[]" value="<?= e($b['nama_variasi'] ?? '') ?>" placeholder="mis. Tangkai"></td>
                            <td><input type="text" name="variasi_nilai[]" value="<?= e($b['nilai'] ?? '') ?>" placeholder="mis. 10 tangkai"></td>
                            <td><input type="number" name="variasi_harga[]" min="0" step="1" value="<?= ($b['harga'] ?? null) !== null ? (0 + $b['harga']) : '' ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-panel">
        <h2>Aksesori Terkait</h2>
        <?php if ($aksesori === []): ?>
            <p class="kosong-baris">Belum ada aksesori.</p>
        <?php else: ?>
            <div class="grup-centang">
                <?php foreach ($aksesori as $a): ?>
                    <label class="kotak-centang">
                        <input type="checkbox" name="aksesori[]" value="<?= (int) $a['id'] ?>" <?= in_array((int) $a['id'], $terhubung, true) ? 'checked' : '' ?>>
                        <span><?= e($a['nama']) ?> — <?= e(format_rupiah($a['harga'])) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="admin-panel">
        <h2>Foto Produk</h2>
        <label class="medan">
            <span>Tambah foto (bisa lebih dari satu; JPEG/PNG/WebP, maksimal 2 MB per foto)</span>
            <input type="file" name="gambar[]" accept="image/jpeg,image/png,image/webp" multiple>
            <small>Foto disimpan ke uploads/produk. Foto pertama otomatis menjadi foto utama bila produk belum punya foto utama.</small>
        </label>
        <?php if ($gambar !== []): ?>
            <div class="grid-gambar-admin">
                <?php foreach ($gambar as $g): ?>
                    <figure class="kartu-gambar-admin">
                        <img src="<?= e(url_upload($g['nama_file'])) ?>" alt="" loading="lazy">
                        <?php if ((int) $g['is_utama'] === 1): ?>
                            <figcaption>Utama</figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="admin-aksi">
        <button type="submit" class="tombol tombol-utama">Simpan Produk</button>
        <a class="tombol tombol-lembut" href="<?= e(base_url('admin/produk.php')) ?>">Batal</a>
    </div>
</form>

<?php if ($gambar !== []): ?>
    <section class="admin-panel">
        <h2>Hapus Foto</h2>
        <div class="grup-centang">
            <?php foreach ($gambar as $g): ?>
                <form method="post" action="<?= e(base_url('admin/produk-form.php')) ?>" onsubmit="return confirm('Hapus foto ini?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="hapus_gambar">
                    <input type="hidden" name="id" value="<?= (int) $id ?>">
                    <input type="hidden" name="gambar_id" value="<?= (int) $g['id'] ?>">
                    <button type="submit" class="tautan-aksi tautan-bahaya">Hapus <?= e($g['nama_file']) ?></button>
                </form>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php require dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
