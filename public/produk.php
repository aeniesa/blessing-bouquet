<?php
/**
 * produk.php — Detail produk (F-05) + formulir pesan per produk (F-06).
 * Parameter GET: ?slug=<slug produk aktif>
 * Form POST: aksi=pesan -> divalidasi server, lalu dialihkan ke WhatsApp.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$toko = ambil_pengaturan($pdo);
$wa   = $toko['whatsapp'] ?? '';

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$p    = $slug !== '' ? repo_produk_slug($slug) : null;

if ($p === null) {
    http_response_code(404);
    $judulHalaman = 'Produk Tidak Ditemukan';
    require dirname(__DIR__) . '/includes/header.php';
    ?>
    <section class="bagian">
        <div class="wadah" style="text-align:center; max-width:640px;">
            <p class="hero-eyebrow">Error 404</p>
            <h1 class="hero-judul" style="font-size: clamp(2rem, 5vw, 3rem);">Produk tidak ditemukan</h1>
            <p class="hero-deskripsi" style="margin-inline:auto;">Produk yang Anda cari tidak tersedia atau sudah tidak aktif.</p>
            <p><a class="tombol tombol-utama" href="<?= e(base_url('katalog.php')) ?>">Lihat Katalog</a></p>
        </div>
    </section>
    <?php
    require dirname(__DIR__) . '/includes/footer.php';
    return;
}

$variasi  = repo_variasi_produk((int) $p['id']);
$aksesori = repo_aksesori_produk((int) $p['id']);
$harga    = harga_produk($p, $variasi);
$catatan  = catatan_harga_produk($p);

// Foto utama produk (bila admin sudah mengunggah).
$fotoPerProduk = repo_gambar_per_produk();
$namaFotoUtama = $fotoPerProduk[(int) $p['id']] ?? null;
$gambarUtama   = $namaFotoUtama !== null && $namaFotoUtama !== '' ? url_upload($namaFotoUtama) : null;

/** Cocokkan id variasi terhadap daftar variasi produk. */
$cariVariasi = static function (?int $id) use ($variasi): ?array {
    if ($id === null) {
        return null;
    }
    foreach ($variasi as $v) {
        if ((int) $v['id'] === $id) {
            return $v;
        }
    }
    return null;
};

/** Cocokkan daftar id aksesori terhadap aksesori produk. */
$cariAksesori = static function (array $idsMasuk) use ($aksesori): array {
    $hasil = [];
    if ($idsMasuk === []) {
        return $hasil;
    }
    $idsStr = array_map('strval', $idsMasuk);
    foreach ($aksesori as $a) {
        if (in_array((string) $a['id'], $idsStr, true)) {
            $hasil[] = $a;
        }
    }
    return $hasil;
};

$pesanError  = '';
$nilaiNama   = '';
$nilaiCatatan = '';
$nilaiVariasiId = $variasi !== [] ? (int) $variasi[0]['id'] : null;
$nilaiAksesoriId = [];

// ---- Proses formulir pesan (POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['aksi'] ?? '') === 'pesan') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $pesanError = 'Sesi keamanan tidak valid. Silakan muat ulang halaman.';
    } else {
        $nilaiNama    = trim((string) ($_POST['nama_pemesan'] ?? ''));
        $nilaiCatatan = trim((string) ($_POST['catatan'] ?? ''));

        $variasiIdInput = (isset($_POST['variasi_id']) && $_POST['variasi_id'] !== '')
            ? (int) $_POST['variasi_id'] : null;
        $variasiTerpilih = $cariVariasi($variasiIdInput);
        if ($variasi !== []) {
            $nilaiVariasiId = $variasiTerpilih !== null ? (int) $variasiTerpilih['id'] : (int) $variasi[0]['id'];
        }

        $idsAksesori = $_POST['aksesori'] ?? [];
        $aksesoriTerpilih = is_array($idsAksesori) ? $cariAksesori($idsAksesori) : [];
        $nilaiAksesoriId = array_map(static fn ($a) => (int) $a['id'], $aksesoriTerpilih);

        if ($nilaiNama === '') {
            $pesanError = 'Nama lengkap wajib diisi.';
        } elseif (mb_strlen($nilaiNama) > 100) {
            $pesanError = 'Nama lengkap terlalu panjang (maksimal 100 karakter).';
        } elseif ($variasi !== [] && $variasiTerpilih === null) {
            $pesanError = 'Silakan pilih varian produk.';
        } elseif ($wa === '') {
            $pesanError = 'Nomor WhatsApp toko belum diatur.';
        } else {
            // Susun pesan (§23) — harga memakai aturan yang sudah ada.
            $hargaPasti = harga_pasti_produk($p, $variasiTerpilih);
            $label = keranjang_label_harga([
                'jenis_harga'  => $p['jenis_harga'],
                'harga_tampil' => $hargaPasti,
            ]);

            $baris = [];
            $baris[] = 'Halo, saya ingin memesan dari ' . ($toko['nama_toko'] ?: 'Blessing Bouquet') . '.';
            $baris[] = '';
            $baris[] = 'Nama: ' . $nilaiNama;
            $baris[] = 'Produk: ' . $p['nama'];
            if ($variasiTerpilih !== null) {
                $baris[] = 'Varian: ' . $variasiTerpilih['nama_variasi'] . ' — ' . $variasiTerpilih['nilai'];
            }
            if ($aksesoriTerpilih !== []) {
                $namaAks = [];
                foreach ($aksesoriTerpilih as $a) {
                    $namaAks[] = $a['nama'] . ' (' . format_rupiah($a['harga']) . ')';
                }
                $baris[] = 'Aksesori: ' . implode(', ', $namaAks);
            }
            if ($nilaiCatatan !== '') {
                $baris[] = 'Catatan: ' . $nilaiCatatan;
            }
            $baris[] = '';
            $baris[] = $label['baris'];
            if ($label['harga'] !== null) {
                $baris[] = 'Subtotal: ' . format_rupiah($label['harga']);
            }
            $baris[] = '';
            $baris[] = 'Mohon konfirmasi ketersediaan produk dan total harga yang perlu dibayarkan. Terima kasih.';

            header('Location: ' . wa_link($wa, implode("\n", $baris)));
            exit;
        }
    }
}

// Produk lain di kategori yang sama (maks 4).
$sejenis = [];
foreach (repo_produk((string) $p['kategori_slug']) as $lain) {
    if ((int) $lain['id'] !== (int) $p['id']) {
        $sejenis[] = $lain;
    }
    if (count($sejenis) >= 4) {
        break;
    }
}

$judulHalaman = $p['nama'];
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="bagian">
    <div class="wadah">
        <p class="remah"><a href="<?= e(base_url('index.php')) ?>">Beranda</a> / <a href="<?= e(base_url('katalog.php')) ?>">Katalog</a> / <span><?= e($p['nama']) ?></span></p>

        <div class="detail-produk">
            <div class="detail-gambar">
                <?php if ($gambarUtama !== null): ?>
                    <img src="<?= e($gambarUtama) ?>" alt="<?= e($p['nama']) ?>">
                <?php else: ?>
                    <span><?= e($p['nama']) ?></span>
                <?php endif; ?>
            </div>
            <div class="detail-isi">
                <a class="kartu-kategori" href="<?= e(base_url('katalog.php?kategori=' . rawurlencode((string) $p['kategori_slug']))) ?>"><?= e($p['kategori_nama']) ?></a>
                <h1 class="detail-nama"><?= e($p['nama']) ?></h1>
                <p class="detail-harga"><?= e($harga) ?></p>
                <p class="detail-deskripsi"><?= nl2br(e((string) $p['deskripsi'])) ?></p>

                <?php if ($catatan): ?>
                    <p class="catatan-harga"><?= e($catatan) ?></p>
                <?php endif; ?>

                <?php if (!empty($p['ketentuan'])): ?>
                    <div class="kotak-ketentuan">
                        <h2>Ketentuan</h2>
                        <p><?= nl2br(e((string) $p['ketentuan'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <section class="form-pesan" aria-label="Pesan Sekarang">
            <h2>Pesan Sekarang</h2>
            <p class="form-pesan-intro">Isi formulir berikut, lalu lanjutkan pesanan Anda melalui WhatsApp.</p>

            <?php if ($pesanError !== ''): ?>
                <div class="notifikasi notifikasi-error" role="alert"><?= e($pesanError) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(base_url('produk.php?slug=' . rawurlencode((string) $p['slug']))) ?>" class="form-pesan-isi">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="pesan">

                <label class="medan">
                    <span>Nama Lengkap <abbr title="wajib">*</abbr></span>
                    <input type="text" name="nama_pemesan" value="<?= e($nilaiNama) ?>" required maxlength="100"
                           placeholder="Nama Anda" autocomplete="name">
                </label>

                <div class="medan">
                    <span>Produk</span>
                    <p class="medan-produk"><?= e($p['nama']) ?></p>
                </div>

                <?php if ($variasi !== []): ?>
                    <label class="medan">
                        <span><?= e($variasi[0]['nama_variasi'] ?: 'Varian') ?> <abbr title="wajib">*</abbr></span>
                        <select name="variasi_id" required>
                            <?php foreach ($variasi as $v): ?>
                                <option value="<?= (int) $v['id'] ?>" <?= $nilaiVariasiId === (int) $v['id'] ? 'selected' : '' ?>>
                                    <?= e($v['nilai']) ?><?= ($v['harga'] !== null && $v['harga'] !== '') ? ' — ' . e(format_rupiah($v['harga'])) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>

                <?php if ($aksesori !== []): ?>
                    <fieldset class="medan grup-aksesori">
                        <legend>Aksesori Tambahan (opsional)</legend>
                        <?php foreach ($aksesori as $a): ?>
                            <label class="kotak-centang">
                                <input type="checkbox" name="aksesori[]" value="<?= (int) $a['id'] ?>"
                                    <?= in_array((int) $a['id'], $nilaiAksesoriId, true) ? 'checked' : '' ?>>
                                <span><?= e($a['nama']) ?> — <?= e(format_rupiah($a['harga'])) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endif; ?>

                <label class="medan">
                    <span>Catatan / Permintaan Khusus (opsional)</span>
                    <textarea name="catatan" rows="3" maxlength="500"
                              placeholder="Contoh: warna pita, tanggal pengiriman, tulisan di kartu ucapan…"><?= e($nilaiCatatan) ?></textarea>
                </label>

                <?php if ($wa !== ''): ?>
                    <button type="submit" class="tombol tombol-wa tombol-wa-besar tombol-lebar">
                        <svg class="ikon-wa" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 18.15c-1.53 0-3.03-.41-4.34-1.19l-.31-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.34c0-4.54 3.7-8.24 8.24-8.24s8.24 3.7 8.24 8.24-3.7 8.24-8.24 8.24m4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.48-1.38-1.73-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.14.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.16 0-.43.06-.66.31-.23.25-.87.85-.87 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28"/></svg>
                        Pesan via WhatsApp
                    </button>
                <?php else: ?>
                    <p class="kosong">Nomor WhatsApp toko belum diatur.</p>
                <?php endif; ?>
            </form>
        </section>
    </div>
</section>

<?php if ($sejenis !== []): ?>
<section class="bagian bagian-pendukung">
    <div class="wadah">
        <header class="bagian-judul">
            <h2>Produk Lain di Kategori Ini</h2>
        </header>
        <div class="grid-produk">
            <?php
            $variasiPerProduk = repo_variasi_per_produk();
            foreach ($sejenis as $p):
                $variasiSejenis = $variasiPerProduk[(int) $p['id']] ?? [];
                $variasi = $variasiSejenis;
                $fotoProduk = $fotoPerProduk[(int) $p['id']] ?? null;
                require dirname(__DIR__) . '/includes/kartu-produk.php';
            endforeach;
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
