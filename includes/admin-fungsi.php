<?php
/**
 * admin-fungsi.php — helper panel admin.
 * Dipakai halaman admin setelah bootstrap.php.
 * Mendukung dua mode: database aktif (CRUD penuh) atau MODE CONTOH (baca saja).
 */
declare(strict_types=1);

if (!function_exists('admin_flash_set')) {
    function admin_flash_set(string $tipe, string $pesan): void
    {
        $_SESSION['admin_flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
    }
}

if (!function_exists('admin_flash_ambil')) {
    function admin_flash_ambil(): ?array
    {
        $f = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);
        return is_array($f) ? $f : null;
    }
}

if (!function_exists('admin_redirect')) {
    function admin_redirect(string $path): void
    {
        header('Location: ' . base_url($path));
        exit;
    }
}

if (!function_exists('admin_pesan_tidak_simpan')) {
    function admin_pesan_tidak_simpan(): string
    {
        return 'Perubahan tidak dapat disimpan karena database belum tersedia (MODE CONTOH).';
    }
}

if (!function_exists('harga_ke_desimal')) {
    /** Ubah input harga ("Rp45.000", "45000", "45000,50") menjadi float, atau null. */
    function harga_ke_desimal(?string $mentah): ?float
    {
        if ($mentah === null) {
            return null;
        }
        $s = trim($mentah);
        if ($s === '') {
            return null;
        }
        $s = preg_replace('/[^0-9.,]/', '', $s) ?? '';
        if ($s === '') {
            return null;
        }
        if (strpos($s, ',') !== false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (substr_count($s, '.') > 1) {
            $s = str_replace('.', '', $s);
        }
        if (!is_numeric($s)) {
            return null;
        }
        $nilai = (float) $s;
        return $nilai < 0 ? null : $nilai;
    }
}

if (!function_exists('admin_jenis_harga_valid')) {
    function admin_jenis_harga_valid(string $jenis): bool
    {
        return in_array($jenis, ['tetap', 'mulai_dari', 'berdasarkan_permintaan', 'biaya_jasa'], true);
    }
}

if (!function_exists('admin_produk_semua')) {
    /** Semua produk (termasuk non-aktif) untuk daftar admin. */
    function admin_produk_semua(): array
    {
        global $pdo;
        if (db_tersedia() && $pdo instanceof PDO) {
            try {
                return $pdo->query(
                    'SELECT p.*, k.nama AS kategori_nama
                     FROM produk p JOIN kategori k ON k.id = p.kategori_id
                     ORDER BY k.nama ASC, p.nama ASC'
                )->fetchAll();
            } catch (Throwable $ex) {
                error_log('[admin] produk_semua: ' . $ex->getMessage());
            }
        }

        $d = data_contoh();
        $nama = [];
        foreach ($d['kategori'] as $k) {
            $nama[(int) $k['id']] = $k['nama'];
        }
        $hasil = [];
        foreach ($d['produk'] as $p) {
            $p['kategori_nama'] = $nama[(int) $p['kategori_id']] ?? '';
            $hasil[] = $p;
        }
        return $hasil;
    }
}

if (!function_exists('admin_aksesori_semua')) {
    /** Semua aksesori (termasuk non-aktif). */
    function admin_aksesori_semua(): array
    {
        global $pdo;
        if (db_tersedia() && $pdo instanceof PDO) {
            try {
                return $pdo->query('SELECT * FROM aksesori ORDER BY nama ASC')->fetchAll();
            } catch (Throwable $ex) {
                error_log('[admin] aksesori_semua: ' . $ex->getMessage());
            }
        }
        $hasil = data_contoh()['aksesori'];
        usort($hasil, static fn ($a, $b) => strcmp((string) $a['nama'], (string) $b['nama']));
        return $hasil;
    }
}

if (!function_exists('admin_aksesori_terhubung')) {
    /** Daftar id aksesori yang terhubung ke satu produk. */
    function admin_aksesori_terhubung(int $produkId): array
    {
        global $pdo;
        if (db_tersedia() && $pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare('SELECT aksesori_id FROM produk_aksesori WHERE produk_id = ?');
                $stmt->execute([$produkId]);
                return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
            } catch (Throwable $ex) {
                error_log('[admin] aksesori_terhubung: ' . $ex->getMessage());
            }
        }
        $hasil = [];
        foreach (data_contoh()['produk_aksesori'] as $pa) {
            if ((int) $pa['produk_id'] === $produkId) {
                $hasil[] = (int) $pa['aksesori_id'];
            }
        }
        return $hasil;
    }
}

if (!function_exists('admin_gambar_bolos')) {
    /**
     * Validasi seluruh berkas foto pada formulir produk (array multi-file $_FILES['gambar']).
     * Mengembalikan pesan error pertama, atau '' bila semua valid/kosong.
     */
    function admin_gambar_bolos(mixed $gambar): string
    {
        if (!function_exists('validasi_gambar')) {
            return 'Modul unggahan belum dimuat.';
        }
        if (!is_array($gambar) || !is_array($gambar['name'] ?? null)) {
            return '';
        }
        foreach ($gambar['name'] as $i => $namaFile) {
            if ($namaFile === null || $namaFile === '') {
                continue;
            }
            $satu = [
                'name'     => $namaFile,
                'type'     => $gambar['type'][$i] ?? '',
                'tmp_name' => $gambar['tmp_name'][$i] ?? '',
                'error'    => $gambar['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $gambar['size'][$i] ?? 0,
            ];
            $err = validasi_gambar($satu);
            if ($err !== null) {
                return $err . ' (berkas: ' . basename((string) $namaFile) . ')';
            }
        }
        return '';
    }
}

if (!function_exists('admin_palet_aktif')) {
    /**
     * Slug palet tampilan panel admin (terpisah dari palet website publik).
     */
    function admin_palet_aktif(): string
    {
        global $pdo;
        if (db_tersedia() && $pdo instanceof PDO) {
            try {
                $v = $pdo->query('SELECT palet FROM pengaturan_admin WHERE id = 1')->fetchColumn();
                if (is_string($v) && $v !== '' && isset(daftar_palet_admin()[$v])) {
                    return $v;
                }
            } catch (Throwable $ex) {
                error_log('[admin] palet_aktif: ' . $ex->getMessage());
            }
        }
        return 'sage_green';
    }
}

if (!function_exists('admin_palet_variabel')) {
    /**
     * Variabel CSS panel admin untuk palet terpilih.
     * @return array<string,string>
     */
    function admin_palet_variabel(string $palet): array
    {
        $semua = daftar_palet_admin();
        $p = $semua[$palet] ?? $semua['sage_green'];
        return [
            '--warna-dasar'     => $p['dasar'],
            '--warna-aksen'     => $p['aksen'],
            '--warna-teks'      => $p['teks'],
            '--warna-pendukung' => $p['pendukung'],
            '--sidebar-warna'   => $p['sidebar'],
        ];
    }
}

if (!function_exists('admin_ulasan_semua')) {
    /** Semua ulasan (termasuk yang disembunyikan). */
    function admin_ulasan_semua(): array
    {
        global $pdo;
        if (db_tersedia() && $pdo instanceof PDO) {
            try {
                return $pdo->query('SELECT * FROM ulasan ORDER BY created_at DESC, id DESC')->fetchAll();
            } catch (Throwable $ex) {
                error_log('[admin] ulasan_semua: ' . $ex->getMessage());
            }
        }
        return data_contoh()['ulasan'];
    }
}

if (!function_exists('admin_gambar_produk')) {
    /** Gambar milik satu produk. */
    function admin_gambar_produk(int $produkId): array
    {
        global $pdo;
        if (db_tersedia() && $pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare('SELECT * FROM gambar_produk WHERE produk_id = ? ORDER BY is_utama DESC, urutan, id');
                $stmt->execute([$produkId]);
                return $stmt->fetchAll();
            } catch (Throwable $ex) {
                error_log('[admin] gambar_produk: ' . $ex->getMessage());
            }
        }
        return [];
    }
}

if (!function_exists('sanitasi_embed_maps')) {
    /**
     * Bersihkan HTML embed Google Maps. Mengembalikan:
     *  - ''   -> input kosong (menghapus embed),
     *  - '<iframe ...>' -> embed valid yang dibangun ulang,
     *  - null -> tidak valid / ditolak.
     */
    function sanitasi_embed_maps(string $html): ?string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }
        if (!preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $html, $m)) {
            return null;
        }
        $src = trim($m[1]);
        $bagian = parse_url($src);
        if (!$bagian || empty($bagian['scheme']) || empty($bagian['host'])) {
            return null;
        }
        if (!in_array(strtolower($bagian['scheme']), ['http', 'https'], true)) {
            return null;
        }
        $host = strtolower($bagian['host']);
        $diizinkan = in_array($host, ['google.com', 'www.google.com', 'maps.google.com'], true)
            || str_ends_with($host, '.google.com');
        if (!$diizinkan) {
            return null;
        }
        if (stripos((string) ($bagian['path'] ?? ''), '/maps') === false) {
            return null;
        }
        return '<iframe src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"'
            . ' loading="lazy" referrerpolicy="no-referrer-when-downgrade"'
            . ' style="width:100%;min-height:320px;border:0" allowfullscreen></iframe>';
    }
}
