<?php
/**
 * tentang.php — Halaman Tentang Kami (F-02).
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$toko = ambil_pengaturan($pdo);
$wa   = $toko['whatsapp'] ?? '';

$judulHalaman = 'Tentang Kami';
require dirname(__DIR__) . '/includes/header.php';

$kategori = repo_kategori();
?>
<section class="bagian">
    <div class="wadah">
        <header class="bagian-judul">
            <h2>Tentang <?= e($toko['nama_toko'] ?: 'Blessing Bouquet') ?></h2>
        </header>
        <div class="tentang-dalam">
            <div class="tentang-teks">
                <p><?= nl2br(e((string) ($toko['deskripsi'] ?? ''))) ?: 'Blessing Bouquet menyediakan aneka buket untuk melengkapi momen spesial Anda.' ?></p>
                <p>Pemesanan dilakukan dengan mudah melalui WhatsApp. Pilih produk, susun daftar pesanan, lalu kirim pesanan Anda — penjual akan mengonfirmasi detail dan total harga.</p>
            </div>
            <aside class="tentang-samping">
                <h3>Yang Kami Sediakan</h3>
                <ul class="daftar-pil">
                    <?php foreach ($kategori as $k): ?>
                        <li><a class="pil" href="<?= e(base_url('katalog.php?kategori=' . rawurlencode((string) $k['slug']))) ?>"><?= e($k['nama']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        </div>
        <p class="tentang-aksi">
            <a class="tombol tombol-utama" href="<?= e(base_url('katalog.php')) ?>">Lihat Katalog</a>
            <?php if ($wa): ?>
                <a class="tombol tombol-wa" href="<?= e(wa_link($wa, 'Halo, saya ingin bertanya tentang produk ' . ($toko['nama_toko'] ?: 'Blessing Bouquet') . '.')) ?>" target="_blank" rel="noopener">Tanya via WhatsApp</a>
            <?php endif; ?>
        </p>
    </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
