<?php
/**
 * index.php — Beranda (F-01).
 * Susunan: sambutan/hero, produk unggulan, cara pemesanan, kontak toko.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$toko = ambil_pengaturan($pdo);
$wa   = $toko['whatsapp'] ?? '';

// Produk aktif (via lapisan repositori; otomatis jatuh ke data contoh bila DB off)
$produk = repo_produk();

// Variasi per produk (untuk tampilan harga) + foto utama per produk
$variasiPerProduk = repo_variasi_per_produk();
$fotoPerProduk    = repo_gambar_per_produk();

$unggulan = array_values(array_filter($produk, static fn ($p) => (int) $p['is_unggulan'] === 1));
if ($unggulan === []) {
    $unggulan = array_slice($produk, 0, 4);
}

$banner = banner_toko($toko);

// Pengaturan latar hero (dapat diubah admin di panel pengaturan).
$heroBlur    = hero_blur($toko);
$heroLapisan = hero_lapisan_opasitas($toko);

$judulHalaman = '';
require dirname(__DIR__) . '/includes/header.php';
?>

<section id="beranda" class="hero<?= $banner !== null ? ' hero-banner' : '' ?>"
         style="--hero-blur: <?= $heroBlur ?>; --hero-lapisan: <?= $heroLapisan ?>%;">
    <?php if ($banner !== null): ?>
        <img class="hero-banner-gambar" src="<?= e($banner) ?>" alt="">
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="wadah hero-dalam">
        <p class="hero-eyebrow">Selamat Datang</p>
        <h1 class="hero-judul"><?= e($toko['headline'] ?: 'Setiap Bunga Punya Cerita') ?></h1>
        <p class="hero-deskripsi"><?= e($toko['deskripsi_beranda'] ?: 'Dari hadiah sederhana hingga momen istimewa, temukan buket yang dibuat dengan penuh perhatian untuk orang tersayang.') ?></p>
        <div class="hero-aksi">
            <a class="tombol tombol-utama" href="<?= e(base_url('katalog.php')) ?>"><?= e($toko['teks_tombol_katalog'] ?: 'Lihat Katalog') ?></a>
            <?php if ($wa): ?>
                <a class="tombol tombol-wa" href="<?= e(wa_link($wa, 'Halo, saya ingin bertanya tentang produk ' . ($toko['nama_toko'] ?: 'Blessing Bouquet') . '.')) ?>" target="_blank" rel="noopener">Tanya via WhatsApp</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="produk" class="bagian">
    <div class="wadah">
        <header class="bagian-judul">
            <p class="bagian-eyebrow">Pilihan Kami</p>
            <h2><?= e($toko['judul_produk_unggulan'] ?: 'Produk Unggulan') ?></h2>
            <p><?= e($toko['deskripsi_produk_unggulan'] ?: 'Temukan buket favorit untuk orang tersayang dan jadikan setiap momen lebih berkesan.') ?></p>
        </header>

        <?php if ($unggulan === []): ?>
            <p class="kosong">Belum ada produk unggulan yang ditampilkan.</p>
        <?php else: ?>
            <div class="grid-produk">
                <?php foreach ($unggulan as $p): ?>
                    <?php $variasi = $variasiPerProduk[(int) $p['id']] ?? []; ?>
                    <?php $fotoProduk = $fotoPerProduk[(int) $p['id']] ?? null; ?>
                    <?php require dirname(__DIR__) . '/includes/kartu-produk.php'; ?>
                <?php endforeach; ?>
            </div>
            <p class="bagian-aksi"><a class="tombol tombol-lembut" href="<?= e(base_url('katalog.php')) ?>">Lihat Semua Produk</a></p>
        <?php endif; ?>
    </div>
</section>

<section id="cara-pesan" class="bagian bagian-pendukung">
    <div class="wadah">
        <header class="bagian-judul">
            <p class="bagian-eyebrow">Mudah &amp; Cepat</p>
            <h2>Cara Pemesanan</h2>
            <p>Proses pemesanan dilakukan melalui WhatsApp.</p>
        </header>
        <ol class="langkah">
            <li><span class="langkah-nomor">1</span><div><h3>Lihat Katalog</h3><p>Temukan produk dan variasi yang Anda sukai.</p></div></li>
            <li><span class="langkah-nomor">2</span><div><h3>Hubungi via WhatsApp</h3><p>Klik tombol WhatsApp dan sebutkan produk pilihan Anda.</p></div></li>
            <li><span class="langkah-nomor">3</span><div><h3>Konfirmasi Pesanan</h3><p>Detail dan total harga dikonfirmasi langsung oleh penjual.</p></div></li>
        </ol>
    </div>
</section>

<section id="kontak" class="bagian">
    <div class="wadah kontak-dalam">
        <div class="kontak-info">
            <header class="bagian-judul">
                <p class="bagian-eyebrow">Ada yang Bisa Dibantu?</p>
                <h2>Kontak Toko</h2>
                <p>Hubungi kami untuk pertanyaan, harga, atau pemesanan buket.</p>
            </header>
            <ul class="kontak-daftar">
                <?php if ($wa): ?>
                    <li><strong>WhatsApp</strong><a href="<?= e(wa_link($wa, 'Halo, saya ingin bertanya tentang produk Blessing Bouquet.')) ?>" target="_blank" rel="noopener"><?= e($wa) ?></a></li>
                <?php endif; ?>
                <?php if (!empty($toko['instagram'])): ?>
                    <li><strong>Instagram</strong><a href="https://instagram.com/<?= e(ltrim((string) $toko['instagram'], '@')) ?>" target="_blank" rel="noopener">@<?= e(ltrim((string) $toko['instagram'], '@')) ?></a></li>
                <?php endif; ?>
                <?php if (($tiktokUrl = tiktok_url($toko['tiktok'] ?? null)) !== null): ?>
                    <li><strong>TikTok</strong><a href="<?= e($tiktokUrl) ?>" target="_blank" rel="noopener"><?= e(tiktok_label($toko['tiktok'] ?? null) ?? $tiktokUrl) ?></a></li>
                <?php endif; ?>
                <?php if (!empty($toko['alamat'])): ?>
                    <li><strong>Alamat</strong><span><?= nl2br(e($toko['alamat'])) ?></span></li>
                <?php endif; ?>
            </ul>
            <?php if ($wa): ?>
                <p class="kontak-aksi">
                    <a class="tombol tombol-wa" href="<?= e(wa_link($wa, 'Halo, saya ingin bertanya tentang produk Blessing Bouquet.')) ?>" target="_blank" rel="noopener">
                        <svg class="ikon-wa" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 18.15c-1.53 0-3.03-.41-4.34-1.19l-.31-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.34c0-4.54 3.7-8.24 8.24-8.24s8.24 3.7 8.24 8.24-3.7 8.24-8.24 8.24m4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.48-1.38-1.73-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.14.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.16 0-.43.06-.66.31-.23.25-.87.85-.87 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28"/></svg>
                        Chat Sekarang
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php if (!empty($toko['embed_maps'])): ?>
            <div class="kontak-peta"><?= $toko['embed_maps'] /* diisi admin; host diwhitelist saat admin dibangun */ ?></div>
        <?php endif; ?>
    </div>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
