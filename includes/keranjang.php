<?php
/**
 * keranjang.php — keranjang sementara berbasis session (§4.1 F-06, §26.3)
 * dan penyusun pesan WhatsApp (§23).
 */
declare(strict_types=1);

if (!function_exists('keranjang_ambil')) {
    /** Ambil seluruh item keranjang. */
    function keranjang_ambil(): array
    {
        $items = $_SESSION['keranjang'] ?? [];
        return is_array($items) ? $items : [];
    }
}

if (!function_exists('keranjang_simpan')) {
    function keranjang_simpan(array $items): void
    {
        $_SESSION['keranjang'] = array_values($items);
    }
}

if (!function_exists('keranjang_kunci')) {
    /** Kunci unik per produk + variasi + aksesori. */
    function keranjang_kunci(int $produkId, ?int $variasiId, array $aksesoriId): string
    {
        sort($aksesoriId);
        return $produkId . '|' . ($variasiId ?? 0) . '|' . implode(',', $aksesoriId);
    }
}

if (!function_exists('keranjang_tambah')) {
    /**
     * Tambah item. Bila kombinasi sama sudah ada, jumlahnya ditambahkan.
     *
     * @param array $item {
     *   produk_id:int, nama:string, jenis_harga:string,
     *   variasi:?array{nama:string,nilai:string,harga:?float},
     *   aksesori:array<int,array{nama:string,harga:float}>,
     *   jumlah:int, harga_tampil:?float
     * }
     */
    function keranjang_tambah(array $item): void
    {
        $items = keranjang_ambil();
        $produkId = (int) $item['produk_id'];
        $variasiId = $item['variasi']['id'] ?? null;
        $variasiId = $variasiId !== null ? (int) $variasiId : null;
        $aksesori = $item['aksesori'] ?? [];
        $aksesoriId = [];
        foreach ($aksesori as $a) {
            $aksesoriId[] = (int) $a['id'];
        }
        $kunci = keranjang_kunci($produkId, $variasiId, $aksesoriId);

        $jumlah = max(1, (int) ($item['jumlah'] ?? 1));
        $item['kunci'] = $kunci;
        $item['jumlah'] = $jumlah;

        foreach ($items as $i => $lama) {
            if (($lama['kunci'] ?? '') === $kunci) {
                $items[$i]['jumlah'] = max(1, (int) $lama['jumlah'] + $jumlah);
                keranjang_simpan($items);
                return;
            }
        }

        $items[] = $item;
        keranjang_simpan($items);
    }
}

if (!function_exists('keranjang_ubah_jumlah')) {
    function keranjang_ubah_jumlah(int $indeks, int $jumlah): void
    {
        $items = keranjang_ambil();
        if (!isset($items[$indeks])) {
            return;
        }
        if ($jumlah <= 0) {
            unset($items[$indeks]);
        } else {
            $items[$indeks]['jumlah'] = $jumlah;
        }
        keranjang_simpan($items);
    }
}

if (!function_exists('keranjang_hapus')) {
    function keranjang_hapus(int $indeks): void
    {
        $items = keranjang_ambil();
        if (isset($items[$indeks])) {
            unset($items[$indeks]);
        }
        keranjang_simpan($items);
    }
}

if (!function_exists('keranjang_kosongkan')) {
    function keranjang_kosongkan(): void
    {
        $_SESSION['keranjang'] = [];
        $_SESSION['keranjang_catatan'] = '';
    }
}

if (!function_exists('keranjang_jumlah')) {
    /** Jumlah baris item (bukan total kuantitas) — untuk lencana navigasi. */
    function keranjang_jumlah(): int
    {
        return count(keranjang_ambil());
    }
}

if (!function_exists('keranjang_total_kuantitas')) {
    function keranjang_total_kuantitas(): int
    {
        $n = 0;
        foreach (keranjang_ambil() as $it) {
            $n += (int) ($it['jumlah'] ?? 0);
        }
        return $n;
    }
}

if (!function_exists('keranjang_catatan')) {
    function keranjang_catatan(): string
    {
        return (string) ($_SESSION['keranjang_catatan'] ?? '');
    }
}

if (!function_exists('keranjang_simpan_catatan')) {
    function keranjang_simpan_catatan(string $catatan): void
    {
        $_SESSION['keranjang_catatan'] = trim($catatan);
    }
}

if (!function_exists('keranjang_subtotal_pasti')) {
    /**
     * Subtotal bila SEMUA item punya harga pasti (§11.1). Bila ada yang tidak
     * pasti, kembalikan null (total akhir dikonfirmasi via WhatsApp).
     */
    function keranjang_subtotal_pasti(): ?float
    {
        $total = 0.0;
        foreach (keranjang_ambil() as $it) {
            $harga = $it['harga_tampil'] ?? null;
            if ($harga === null) {
                return null;
            }
            $total += ((float) $harga) * (int) ($it['jumlah'] ?? 1);
        }
        return $total;
    }
}

if (!function_exists('keranjang_label_harga')) {
    /**
     * Label harga satu item sesuai jenis (§11.1/§23).
     * @return array{harga:?float,baris:string,catatan:?string}
     */
    function keranjang_label_harga(array $item): array
    {
        $jenis = $item['jenis_harga'] ?? '';
        $harga = $item['harga_tampil'] ?? null;

        if ($jenis === 'biaya_jasa') {
            return [
                'harga'   => $harga,
                'baris'   => $harga !== null ? 'Biaya jasa: ' . format_rupiah($harga) . ' (belum termasuk nominal uang)' : 'Biaya jasa (belum termasuk nominal uang)',
                'catatan' => 'biaya jasa',
            ];
        }
        if ($jenis === 'berdasarkan_permintaan') {
            return ['harga' => null, 'baris' => 'Harga berdasarkan permintaan (dikonfirmasi via WhatsApp)', 'catatan' => null];
        }
        if ($harga === null) {
            return ['harga' => null, 'baris' => 'Harga: perlu dikonfirmasi', 'catatan' => null];
        }
        return ['harga' => $harga, 'baris' => 'Harga: ' . format_rupiah($harga), 'catatan' => null];
    }
}

if (!function_exists('pesan_whatsapp')) {
    /** Susun teks pesanan WhatsApp dari keranjang (§23). */
    function pesan_whatsapp(array $toko): string
    {
        $namaToko = $toko['nama_toko'] ?: 'Blessing Bouquet';
        $items = keranjang_ambil();
        $baris = [];
        $baris[] = 'Halo, saya ingin memesan produk dari ' . $namaToko . '.';
        $baris[] = '';

        if ($items === []) {
            $baris[] = 'Saya ingin bertanya tentang produk yang tersedia.';
        } else {
            $baris[] = 'Daftar pesanan:';
            $no = 1;
            foreach ($items as $it) {
                $baris[] = $no . '. ' . ($it['nama'] ?? '');
                if (!empty($it['variasi']['nilai'])) {
                    $baris[] = '   - Variasi: ' . $it['variasi']['nilai'];
                }
                if (!empty($it['aksesori'])) {
                    $namaAks = [];
                    foreach ($it['aksesori'] as $a) {
                        $namaAks[] = $a['nama'] . ' (' . format_rupiah($a['harga']) . ')';
                    }
                    $baris[] = '   - Aksesori: ' . implode(', ', $namaAks);
                }
                $baris[] = '   - Jumlah: ' . (int) ($it['jumlah'] ?? 1);
                $label = keranjang_label_harga($it);
                $baris[] = '   - ' . $label['baris'];
                $no++;
            }

            $subtotal = keranjang_subtotal_pasti();
            if ($subtotal !== null) {
                $baris[] = '';
                $baris[] = 'Subtotal (harga pasti): ' . format_rupiah($subtotal);
            } else {
                $baris[] = '';
                $baris[] = 'Total akhir dikonfirmasi via WhatsApp.';
            }
        }

        $catatan = keranjang_catatan();
        if ($catatan !== '') {
            $baris[] = '';
            $baris[] = 'Catatan tambahan: ' . $catatan;
        }

        $baris[] = '';
        $baris[] = 'Mohon informasi ketersediaan produk, detail pesanan, dan total harga yang perlu dibayarkan. Terima kasih.';

        return implode("\n", $baris);
    }
}
