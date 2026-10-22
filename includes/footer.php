<?php
/**
 * footer.php — footer publik: 3 kolom (identitas, jelajahi, hubungi).
 */
if (!function_exists('ambil_pengaturan')) {
    require_once __DIR__ . '/bootstrap.php';
}

$__toko      = ambil_pengaturan($pdo);
$__namaToko  = $__toko['nama_toko'] ?: 'Blessing Bouquet';
$__wa        = $__toko['whatsapp'] ?? '';
$__ig        = ltrim((string) ($__toko['instagram'] ?? ''), '@');
$__tiktokUrl = tiktok_url($__toko['tiktok'] ?? null);
$__logo      = logo_toko($__toko);
$__beranda   = base_url('index.php');
$__alamat    = trim((string) ($__toko['alamat'] ?? ''));
$__deskripsi = trim((string) ($__toko['deskripsi'] ?? ''));
?>
</main>
<footer class="situs-footer">
    <div class="wadah footer-dalam">
        <div class="footer-kolom footer-identitas">
            <a class="footer-merek" href="<?= e($__beranda) ?>">
                <?php if ($__logo !== null): ?>
                    <img class="footer-logo" src="<?= e($__logo) ?>" alt="Logo <?= e($__namaToko) ?>">
                <?php endif; ?>
                <span class="footer-nama"><?= e($__namaToko) ?></span>
            </a>
            <p class="footer-deskripsi"><?= e($__deskripsi !== '' ? $__deskripsi : 'Toko buket untuk hadiah dan momen spesial. Dibuat dengan penuh perhatian dan dipesan melalui WhatsApp.') ?></p>
        </div>
        <div class="footer-kolom">
            <h3 class="footer-judul">Jelajahi</h3>
            <ul class="footer-daftar">
                <li><a href="<?= e($__beranda) ?>">Beranda</a></li>
                <li><a href="<?= e(base_url('katalog.php')) ?>">Katalog</a></li>
                <li><a href="<?= e(base_url('tentang.php')) ?>">Tentang Kami</a></li>
                <li><a href="<?= e(base_url('kontak.php')) ?>">Kontak</a></li>
            </ul>
        </div>
        <div class="footer-kolom">
            <h3 class="footer-judul">Hubungi Kami</h3>
            <ul class="footer-kontak">
                <?php if (!empty($__wa)): ?>
                    <li>
                        <a class="footer-tautan" href="<?= e(wa_link($__wa, 'Halo, saya ingin bertanya tentang produk ' . $__namaToko . '.')) ?>" target="_blank" rel="noopener">
                            <span class="footer-ikon" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 18.15c-1.53 0-3.03-.41-4.34-1.19l-.31-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.34c0-4.54 3.7-8.24 8.24-8.24s8.24 3.7 8.24 8.24-3.7 8.24-8.24 8.24m4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.48-1.38-1.73-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.14.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.16 0-.43.06-.66.31-.23.25-.87.85-.87 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28"/></svg></span>
                            <span>WhatsApp</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($__ig !== ''): ?>
                    <li>
                        <a class="footer-tautan" href="https://instagram.com/<?= e(rawurlencode($__ig)) ?>" target="_blank" rel="noopener">
                            <span class="footer-ikon" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16m0-2.16C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63a5.9 5.9 0 0 0-2.13 1.38A5.9 5.9 0 0 0 .63 4.14C.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.31.79.72 1.46 1.38 2.13a5.9 5.9 0 0 0 2.13 1.38c.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56a5.9 5.9 0 0 0 2.13-1.38 5.9 5.9 0 0 0 1.38-2.13c.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91a5.9 5.9 0 0 0-1.38-2.13A5.9 5.9 0 0 0 19.86.63c-.76-.3-1.64-.5-2.91-.56C15.67.01 15.26 0 12 0Zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.41-10.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88Z"/></svg></span>
                            <span>Instagram</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($__tiktokUrl !== null): ?>
                    <li>
                        <a class="footer-tautan" href="<?= e($__tiktokUrl) ?>" target="_blank" rel="noopener">
                            <span class="footer-ikon" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 1 1-2.59-2.59c.27 0 .53.04.78.12V9.75a5.7 5.7 0 0 0-.78-.05A5.7 5.7 0 1 0 15.54 15.4V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3a4.28 4.28 0 0 1-3.24-1.48Z"/></svg></span>
                            <span>TikTok</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($__alamat !== ''): ?>
                    <li class="footer-alamat"><?= nl2br(e($__alamat)) ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="footer-dasar">
        <div class="wadah">
            <p>&copy; <?= date('Y') ?> <?= e($__namaToko) ?>. All rights reserved.</p>
        </div>
    </div>
</footer>
<script src="<?= e(aset('assets/js/main.js')) ?>"></script>
</body>
</html>
