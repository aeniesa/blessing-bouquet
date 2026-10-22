<?php
/**
 * kartu-produk.php — partial kartu produk.
 * Variabel yang harus tersedia sebelum include:
 *   $p           (array) baris produk (memuat kategori_nama, kategori_slug)
 *   $variasi     (array) daftar variasi produk (boleh kosong)
 *   $fotoProduk  (?string) nama file foto utama produk (boleh null)
 */
if (!isset($p) || !is_array($p)) {
    return;
}
$variasi   = $variasi ?? [];
$harga     = harga_produk($p, $variasi);
$detailUrl = url_produk((string) $p['slug']);
$fotoProduk = $fotoProduk ?? null;
$fotoUrl    = $fotoProduk !== null && $fotoProduk !== '' ? url_upload($fotoProduk) : null;
?>
<article class="kartu-produk">
    <a class="kartu-gambar" href="<?= e($detailUrl) ?>" aria-hidden="true" tabindex="-1">
        <?php if ($fotoUrl !== null): ?>
            <img src="<?= e($fotoUrl) ?>" alt="<?= e($p['nama']) ?>" loading="lazy">
        <?php else: ?>
            <span class="kartu-gambar-teks"><?= e($p['nama']) ?></span>
        <?php endif; ?>
    </a>
    <div class="kartu-isi">
        <?php if (!empty($p['kategori_nama'])): ?>
            <a class="kartu-kategori" href="<?= e(base_url('katalog.php?kategori=' . rawurlencode((string) $p['kategori_slug']))) ?>"><?= e($p['kategori_nama']) ?></a>
        <?php endif; ?>
        <h3 class="kartu-nama"><a href="<?= e($detailUrl) ?>"><?= e($p['nama']) ?></a></h3>
        <p class="kartu-deskripsi"><?= e(mb_strimwidth((string) $p['deskripsi'], 0, 110, '…')) ?></p>
        <p class="kartu-harga"><?= e($harga) ?></p>
        <div class="kartu-aksi">
            <a class="tombol tombol-utama tombol-lebar" href="<?= e($detailUrl) ?>">Pesan Sekarang</a>
        </div>
    </div>
</article>
