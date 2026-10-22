<?php
/**
 * daftar-pesanan.php — DINONAKTIFKAN.
 * Alur pemesanan kini memakai formulir per produk di halaman detail
 * (produk.php). Halaman ini diarahkan ke katalog agar tautan lama tidak mati.
 * Kode keranjang lama dipertahankan di includes/keranjang.php untuk kompatibilitas.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Location: ' . base_url('katalog.php'), true, 302);
exit;
