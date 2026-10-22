<?php
/**
 * admin/pengaturan.php — Pengaturan identitas/kontak toko (A-05) dan
 * tampilan beranda (A-06).
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/admin-fungsi.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';

require_login();

$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $pesanError = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif (!db_tersedia() || !$pdo instanceof PDO) {
        $pesanError = admin_pesan_tidak_simpan();
    } else {
        $namaToko  = trim((string) ($_POST['nama_toko'] ?? ''));
        $whatsapp  = trim((string) ($_POST['whatsapp'] ?? ''));
        $instagram = trim((string) ($_POST['instagram'] ?? ''));
        $tiktok    = trim((string) ($_POST['tiktok'] ?? ''));
        $alamat    = trim((string) ($_POST['alamat'] ?? ''));
        $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
        $embed     = sanitasi_embed_maps((string) ($_POST['embed_maps'] ?? ''));
        $headline  = trim((string) ($_POST['headline'] ?? ''));
        $deskBeranda = trim((string) ($_POST['deskripsi_beranda'] ?? ''));
        $teksTombol  = trim((string) ($_POST['teks_tombol_katalog'] ?? ''));
        $palet       = (string) ($_POST['palet_warna'] ?? 'sage_green');
        $judulUnggulan = trim((string) ($_POST['judul_produk_unggulan'] ?? ''));
        $deskUnggulan  = trim((string) ($_POST['deskripsi_produk_unggulan'] ?? ''));

        // Latar hero: nilai slider divalidasi server (tidak hanya Slider UI).
        $heroBlurRaw = trim((string) ($_POST['hero_blur'] ?? ''));
        $heroTransRaw = trim((string) ($_POST['hero_transparansi'] ?? ''));
        $heroBlur = ($heroBlurRaw === '' || !ctype_digit($heroBlurRaw)) ? -1 : (int) $heroBlurRaw;
        $heroTrans = ($heroTransRaw === '' || !ctype_digit($heroTransRaw)) ? -1 : (int) $heroTransRaw;

        if ($namaToko === '') {
            $pesanError = 'Nama toko wajib diisi.';
        } elseif ($whatsapp === '') {
            $pesanError = 'Nomor WhatsApp wajib diisi.';
        } elseif (!isset(daftar_palet()[$palet])) {
            $pesanError = 'Palet warna tidak valid.';
        } elseif ($embed === null) {
            $pesanError = 'Embed Google Maps tidak valid. Tempel kode iframe dari Google Maps.';
        } elseif ($heroBlur < HERO_BLUR_MIN || $heroBlur > HERO_BLUR_MAX) {
            $pesanError = 'Tingkat blur latar hero harus antara ' . HERO_BLUR_MIN . ' sampai ' . HERO_BLUR_MAX . ' px.';
        } elseif ($heroTrans < HERO_TRANSPARANSI_MIN || $heroTrans > HERO_TRANSPARANSI_MAX) {
            $pesanError = 'Transparansi lapisan putih hero harus antara ' . HERO_TRANSPARANSI_MIN . '% sampai ' . HERO_TRANSPARANSI_MAX . '%.';
        } elseif (mb_strlen($judulUnggulan) > 200) {
            $pesanError = 'Judul Produk Unggulan terlalu panjang (maksimal 200 karakter).';
        } else {
            $lama = repo_pengaturan();
            $logo = $lama['logo'] ?? null;
            $banner = $lama['banner'] ?? null;

            try {
                if (!empty($_FILES['logo']['name'])) {
                    $logo = simpan_gambar($_FILES['logo'], UPLOAD_DIR . DIRECTORY_SEPARATOR . 'toko');
                }
                if (!empty($_FILES['banner']['name'])) {
                    $banner = simpan_gambar($_FILES['banner'], UPLOAD_DIR . DIRECTORY_SEPARATOR . 'toko');
                }
            } catch (Throwable $ex) {
                $pesanError = 'Unggahan gagal: ' . $ex->getMessage();
            }

            if ($pesanError === '') {
                $sql = 'UPDATE pengaturan_toko SET
                            nama_toko = ?, logo = ?, deskripsi = ?, whatsapp = ?,
                            instagram = ?, tiktok = ?, alamat = ?, embed_maps = ?,
                            headline = ?, deskripsi_beranda = ?, banner = ?,
                            palet_warna = ?, teks_tombol_katalog = ?,
                            hero_blur = ?, hero_transparansi = ?,
                            judul_produk_unggulan = ?, deskripsi_produk_unggulan = ?,
                            updated_at = NOW()
                        WHERE id = 1';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $namaToko,
                    $logo !== '' ? $logo : null,
                    $deskripsi !== '' ? $deskripsi : null,
                    $whatsapp,
                    $instagram !== '' ? $instagram : null,
                    $tiktok !== '' ? $tiktok : null,
                    $alamat !== '' ? $alamat : null,
                    $embed !== '' ? $embed : null,
                    $headline !== '' ? $headline : null,
                    $deskBeranda !== '' ? $deskBeranda : null,
                    $banner !== '' ? $banner : null,
                    $palet,
                    $teksTombol !== '' ? $teksTombol : 'Lihat Katalog',
                    $heroBlur,
                    $heroTrans,
                    $judulUnggulan !== '' ? $judulUnggulan : 'Produk Unggulan',
                    $deskUnggulan !== '' ? $deskUnggulan : null,
                ]);
                admin_flash_set('sukses', 'Pengaturan disimpan.');
                admin_redirect('admin/pengaturan.php');
            }
        }
    }
}

$toko    = repo_pengaturan();
$palets  = daftar_palet();
$notis   = admin_flash_ambil();
$logoUrl = logo_toko($toko);
$bannerUrl = banner_toko($toko);

// Nilai pengaturan latar hero (dibatasi ke rentang slider).
$heroBlur     = hero_blur($toko);
$heroTrans    = hero_transparansi($toko);
$heroLapisan  = 100 - $heroTrans;
$heroPalet    = palet_variabel((string) ($toko['palet_warna'] ?? 'sage_green'));

$judulAdmin = 'Pengaturan Website';
$aktif = 'pengaturan';
require dirname(__DIR__, 2) . '/includes/admin-header.php';
?>
<?php if ($notis !== null): ?>
    <div class="notis notis-<?= e($notis['tipe']) ?>" role="status"><?= e($notis['pesan']) ?></div>
<?php endif; ?>
<?php if ($pesanError !== ''): ?>
    <div class="notis notis-galat" role="alert"><?= e($pesanError) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(base_url('admin/pengaturan.php')) ?>" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>

    <section class="admin-panel">
        <h2>Identitas &amp; Kontak</h2>
        <div class="form-grid">
            <label class="medan">
                <span>Nama Toko</span>
                <input type="text" name="nama_toko" value="<?= e($toko['nama_toko'] ?? '') ?>" required>
            </label>
            <label class="medan">
                <span>WhatsApp</span>
                <input type="text" name="whatsapp" value="<?= e($toko['whatsapp'] ?? '') ?>" placeholder="085920064874" required>
                <small>Format 08... atau 62...</small>
            </label>
            <label class="medan">
                <span>Instagram</span>
                <input type="text" name="instagram" value="<?= e($toko['instagram'] ?? '') ?>" placeholder="tanpa @">
            </label>
            <label class="medan">
                <span>TikTok</span>
                <input type="text" name="tiktok" value="<?= e($toko['tiktok'] ?? '') ?>" placeholder="tanpa @ atau tautan penuh">
                <small>Isi nama pengguna (mis. namatoko) atau tautan profil lengkap. Kosongkan bila tidak ada.</small>
            </label>
            <label class="medan">
                <span>Logo</span>
                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                <?php if ($logoUrl !== null): ?>
                    <small>Logo saat ini:</small>
                    <img class="pratinjau-gambar" src="<?= e($logoUrl) ?>" alt="Logo saat ini">
                <?php endif; ?>
                <small>Ditampilkan di header dan footer website. Format JPEG/PNG/WebP, maksimal 2 MB.</small>
            </label>
        </div>
        <label class="medan">
            <span>Deskripsi Usaha</span>
            <textarea name="deskripsi" rows="3"><?= e($toko['deskripsi'] ?? '') ?></textarea>
        </label>
        <label class="medan">
            <span>Alamat</span>
            <textarea name="alamat" rows="2"><?= e($toko['alamat'] ?? '') ?></textarea>
        </label>
        <label class="medan">
            <span>Embed Google Maps (iframe)</span>
            <textarea name="embed_maps" rows="3" placeholder='<iframe src="https://www.google.com/maps/embed?..."></iframe>'><?= e($toko['embed_maps'] ?? '') ?></textarea>
            <small>Hanya embed dari google.com/maps yang diterima.</small>
        </label>
    </section>

    <section class="admin-panel">
        <h2>Tampilan Beranda</h2>
        <div class="form-grid">
            <label class="medan">
                <span>Headline</span>
                <input type="text" name="headline" value="<?= e($toko['headline'] ?? '') ?>">
            </label>
            <label class="medan">
                <span>Teks Tombol Katalog</span>
                <input type="text" name="teks_tombol_katalog" value="<?= e($toko['teks_tombol_katalog'] ?? '') ?>">
            </label>
        </div>
        <label class="medan">
            <span>Deskripsi Beranda</span>
            <textarea name="deskripsi_beranda" rows="2"><?= e($toko['deskripsi_beranda'] ?? '') ?></textarea>
        </label>
        <div class="form-grid">
            <label class="medan">
                <span>Banner Beranda (opsional)</span>
                <input type="file" name="banner" accept="image/jpeg,image/png,image/webp">
                <?php if ($bannerUrl !== null): ?>
                    <small>Banner saat ini:</small>
                    <img class="pratinjau-gambar pratinjau-banner" src="<?= e($bannerUrl) ?>" alt="Banner saat ini">
                <?php endif; ?>
                <small>Tampil sebagai latar hero beranda. Saran lebar minimal 1200 px dengan rasio sekitar 3:1; gambar resolusi rendah akan tampil biasa karena memang sumbernya kecil.</small>
            </label>
            <label class="medan">
                <span>Palet Warna</span>
                <select name="palet_warna">
                    <?php foreach ($palets as $slug => $p): ?>
                        <option value="<?= e($slug) ?>" <?= ($toko['palet_warna'] ?? '') === $slug ? 'selected' : '' ?>>
                            <?= e($p['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
    </section>

    <section class="admin-panel">
        <h2>Teks Produk Unggulan</h2>
        <p class="catatan">
            Judul dan deskripsi pada bagian <strong>Produk Unggulan</strong> di beranda.
            Kosongkan salah satu kolom untuk memakai teks bawaan.
        </p>
        <label class="medan">
            <span>Judul Produk Unggulan</span>
            <input type="text" name="judul_produk_unggulan" maxlength="200"
                   value="<?= e($toko['judul_produk_unggulan'] ?? '') ?>"
                   placeholder="Produk Unggulan">
            <small>Teks bawaan: &ldquo;Produk Unggulan&rdquo;.</small>
        </label>
        <label class="medan">
            <span>Deskripsi Produk Unggulan</span>
            <textarea name="deskripsi_produk_unggulan" rows="2"
                      placeholder="Temukan buket favorit untuk orang tersayang dan jadikan setiap momen lebih berkesan."><?= e($toko['deskripsi_produk_unggulan'] ?? '') ?></textarea>
            <small>Teks bawaan: &ldquo;Temukan buket favorit untuk orang tersayang dan jadikan setiap momen lebih berkesan.&rdquo;</small>
        </label>
    </section>

    <section class="admin-panel">
        <h2>Latar Hero Beranda</h2>
        <p class="catatan">
            Atur tampilan gambar latar hero (foto buket &amp; uang). Efek blur dan lapisan putih
            hanya berlaku pada <strong>gambar latar</strong> &mdash; judul, deskripsi, dan tombol
            tetap tajam dan mudah dibaca.
        </p>

        <label class="medan">
            <span>
                Blur Latar Hero:
                <output id="nilai-hero-blur" for="hero_blur"><?= $heroBlur ?> px</output>
            </span>
            <input type="range" id="hero_blur" name="hero_blur"
                   min="<?= HERO_BLUR_MIN ?>" max="<?= HERO_BLUR_MAX ?>" step="1"
                   value="<?= $heroBlur ?>" data-hero-blur data-satuan=" px">
            <small>0 px = foto tetap tajam, 10 px = foto sangat blur (lebih abstrak).</small>
        </label>

        <label class="medan">
            <span>
                Transparansi Lapisan Putih:
                <output id="nilai-hero-transparansi" for="hero_transparansi"><?= $heroTrans ?> %</output>
            </span>
            <input type="range" id="hero_transparansi" name="hero_transparansi"
                   min="<?= HERO_TRANSPARANSI_MIN ?>" max="<?= HERO_TRANSPARANSI_MAX ?>" step="1"
                   value="<?= $heroTrans ?>" data-hero-transparansi data-satuan=" %">
            <small>
                0 % = lapisan putih pekat sehingga foto tertutup, 90 % = lapisan hampir tak terlihat
                sehingga foto paling jelas. Di balik teks tetap ada halo lembut agar tulisan terbaca.
            </small>
        </label>

        <div class="pratinjau-hero-wrap">
            <span class="pratinjau-judul">Pratinjau</span>
            <div class="pratinjau-hero" id="pratinjau-hero"
                 style="--hero-blur: <?= $heroBlur ?>; --hero-lapisan: <?= $heroLapisan ?>%; <?php foreach ($heroPalet as $k => $v): ?><?= e($k) ?>: <?= e($v) ?>;<?php endforeach; ?>">
                <div class="pratinjau-hero-teks">
                    <strong>Judul Contoh</strong>
                    <span>Deskripsi singkat di atas foto latar.</span>
                </div>
            </div>
        </div>
    </section>

    <div class="admin-aksi">
        <button type="submit" class="tombol tombol-utama">Simpan Pengaturan</button>
    </div>
</form>
<?php require dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
