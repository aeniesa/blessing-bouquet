<?php
/**
 * katalog.php — Katalog produk (F-03) + filter kategori & pencarian (F-04).
 * Parameter GET: ?kategori=<slug>&q=<kata kunci>
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$toko = ambil_pengaturan($pdo);
$wa   = $toko['whatsapp'] ?? '';

$kategoriSlug = isset($_GET['kategori']) ? trim((string) $_GET['kategori']) : '';
$cari         = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$semuaKategori = repo_kategori();

// Validasi kategori terhadap daftar kategori yang ada.
$kategoriAktif = null;
if ($kategoriSlug !== '') {
    foreach ($semuaKategori as $k) {
        if ((string) $k['slug'] === $kategoriSlug) {
            $kategoriAktif = $k;
            break;
        }
    }
    if ($kategoriAktif === null) {
        $kategoriSlug = '';
    }
}

$produk           = repo_produk($kategoriSlug !== '' ? $kategoriSlug : null, $cari !== '' ? $cari : null);
$variasiPerProduk = repo_variasi_per_produk();
$fotoPerProduk    = repo_gambar_per_produk();

$judulHalaman = 'Katalog';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="bagian">
    <div class="wadah">
        <header class="bagian-judul">
            <h2>Katalog Buket</h2>
            <p>Telusuri koleksi buket kami. Gunakan filter kategori atau pencarian nama produk.</p>
        </header>

        <form class="form-cari" method="get" action="<?= e(base_url('katalog.php')) ?>" role="search">
            <label class="sr-only" for="q">Cari produk</label>
            <input type="search" id="q" name="q" value="<?= e($cari) ?>" placeholder="Cari nama produk…">
            <?php if ($kategoriSlug !== ''): ?>
                <input type="hidden" name="kategori" value="<?= e($kategoriSlug) ?>">
            <?php endif; ?>
            <button type="submit" class="tombol tombol-utama">Cari</button>
        </form>

        <nav id="kategori" class="filter-kategori" aria-label="Filter kategori">
            <a class="pil-filter <?= $kategoriSlug === '' ? 'aktif' : '' ?>"
               href="<?= e(base_url('katalog.php' . ($cari !== '' ? '?q=' . rawurlencode($cari) : ''))) ?>">Semua</a>
            <?php foreach ($semuaKategori as $k): ?>
                <?php
                    $params = ['kategori' => $k['slug']];
                    if ($cari !== '') {
                        $params['q'] = $cari;
                    }
                    $url = base_url('katalog.php?' . http_build_query($params));
                ?>
                <a class="pil-filter <?= $kategoriSlug === (string) $k['slug'] ? 'aktif' : '' ?>"
                   href="<?= e($url) ?>"><?= e($k['nama']) ?> <span class="hitungan"><?= (int) $k['jumlah'] ?></span></a>
            <?php endforeach; ?>
        </nav>

        <p class="hasil-info">
            <?php if ($kategoriAktif !== null): ?>
                Kategori: <strong><?= e($kategoriAktif['nama']) ?></strong>.
            <?php endif; ?>
            <?php if ($cari !== ''): ?>
                Pencarian: <strong><?= e($cari) ?></strong>.
            <?php endif; ?>
            <?= count($produk) ?> produk ditemukan.
        </p>

        <?php if ($produk === []): ?>
            <p class="kosong">Tidak ada produk yang cocok. Coba ubah kata kunci atau pilih kategori lain.</p>
        <?php else: ?>
            <div class="grid-produk">
                <?php foreach ($produk as $p): ?>
                    <?php $variasi = $variasiPerProduk[(int) $p['id']] ?? []; ?>
                    <?php $fotoProduk = $fotoPerProduk[(int) $p['id']] ?? null; ?>
                    <?php require dirname(__DIR__) . '/includes/kartu-produk.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
