<?php
/**
 * repositori.php — lapisan akses data publik.
 *
 * Setiap fungsi mencoba memakai database (PDO). Bila database tidak tersedia
 * (mis. MariaDB belum dijalankan), fungsi jatuh ke DATA CONTOH sementara
 * (`includes/data-contoh.php`) agar halaman publik tetap dapat ditampilkan.
 *
 * Mode aktif dapat dicek dengan `mode_contoh()`. Bila true, UI menampilkan
 * penanda "MODE CONTOH" agar tidak dikira data asli dari database.
 */
declare(strict_types=1);

if (!function_exists('data_contoh')) {
    /** Muat data contoh sekali saja. */
    function data_contoh(): array
    {
        static $data = null;
        if ($data === null) {
            $data = require __DIR__ . '/data-contoh.php';
        }
        return $data;
    }
}

if (!function_exists('db_tersedia')) {
    function db_tersedia(): bool
    {
        return !empty($GLOBALS['DB_TERSEDIA']);
    }
}

if (!function_exists('mode_contoh')) {
    /** True bila aplikasi memakai data contoh (database tidak tersedia). */
    function mode_contoh(): bool
    {
        return !db_tersedia();
    }
}

if (!function_exists('repo_pengaturan')) {
    /** Pengaturan toko (baris id=1). */
    function repo_pengaturan(): array
    {
        if (db_tersedia()) {
            try {
                $row = $GLOBALS['pdo']->query('SELECT * FROM pengaturan_toko WHERE id = 1 LIMIT 1')->fetch();
                if ($row) {
                    return $row;
                }
            } catch (Throwable $ex) {
                error_log('[repo] pengaturan: ' . $ex->getMessage());
            }
        }
        return data_contoh()['pengaturan_toko'];
    }
}

if (!function_exists('repo_kategori')) {
    /** Semua kategori + jumlah produk aktif. */
    function repo_kategori(): array
    {
        if (db_tersedia()) {
            try {
                $sql = "SELECT k.id, k.nama, k.slug, k.deskripsi, COUNT(p.id) AS jumlah
                        FROM kategori k
                        LEFT JOIN produk p ON p.kategori_id = k.id AND p.is_aktif = 1
                        GROUP BY k.id, k.nama, k.slug, k.deskripsi
                        ORDER BY k.nama ASC";
                return $GLOBALS['pdo']->query($sql)->fetchAll();
            } catch (Throwable $ex) {
                error_log('[repo] kategori: ' . $ex->getMessage());
            }
        }

        $d = data_contoh();
        $peta = [];
        foreach ($d['produk'] as $p) {
            if ((int) $p['is_aktif'] === 1) {
                $peta[(int) $p['kategori_id']] = ($peta[(int) $p['kategori_id']] ?? 0) + 1;
            }
        }
        $hasil = [];
        foreach ($d['kategori'] as $k) {
            $k['jumlah'] = $peta[(int) $k['id']] ?? 0;
            $hasil[] = $k;
        }
        usort($hasil, static fn ($a, $b) => strcmp((string) $a['nama'], (string) $b['nama']));
        return $hasil;
    }
}

if (!function_exists('repo_produk')) {
    /**
     * Produk aktif, dengan filter opsional.
     * @param string|null $slugKategori filter kategori
     * @param string|null $cari         pencarian nama
     * @return array
     */
    function repo_produk(?string $slugKategori = null, ?string $cari = null): array
    {
        if (db_tersedia()) {
            try {
                $sql = "SELECT p.*, k.nama AS kategori_nama, k.slug AS kategori_slug
                        FROM produk p
                        JOIN kategori k ON k.id = p.kategori_id
                        WHERE p.is_aktif = 1";
                $params = [];
                if ($slugKategori !== null && $slugKategori !== '') {
                    $sql .= ' AND k.slug = ?';
                    $params[] = $slugKategori;
                }
                if ($cari !== null && $cari !== '') {
                    $sql .= ' AND p.nama LIKE ?';
                    $params[] = '%' . $cari . '%';
                }
                $sql .= ' ORDER BY p.is_unggulan DESC, p.nama ASC';
                $stmt = $GLOBALS['pdo']->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll();
            } catch (Throwable $ex) {
                error_log('[repo] produk: ' . $ex->getMessage());
            }
        }

        $d = data_contoh();
        $namaKategori = [];
        $slugKategoriMap = [];
        foreach ($d['kategori'] as $k) {
            $namaKategori[(int) $k['id']] = $k['nama'];
            $slugKategoriMap[(int) $k['id']] = $k['slug'];
        }
        $hasil = [];
        foreach ($d['produk'] as $p) {
            if ((int) $p['is_aktif'] !== 1) {
                continue;
            }
            $p['kategori_nama'] = $namaKategori[(int) $p['kategori_id']] ?? '';
            $p['kategori_slug'] = $slugKategoriMap[(int) $p['kategori_id']] ?? '';
            if ($slugKategori !== null && $slugKategori !== '' && $p['kategori_slug'] !== $slugKategori) {
                continue;
            }
            if ($cari !== null && $cari !== '' && stripos((string) $p['nama'], $cari) === false) {
                continue;
            }
            $hasil[] = $p;
        }
        usort($hasil, static function ($a, $b) {
            $u = (int) $b['is_unggulan'] <=> (int) $a['is_unggulan'];
            return $u !== 0 ? $u : strcmp((string) $a['nama'], (string) $b['nama']);
        });
        return $hasil;
    }
}

if (!function_exists('repo_produk_id')) {
    /** Satu produk aktif berdasarkan id (beserta nama kategori). */
    function repo_produk_id(int $id): ?array
    {
        if (db_tersedia()) {
            try {
                $stmt = $GLOBALS['pdo']->prepare(
                    "SELECT p.*, k.nama AS kategori_nama, k.slug AS kategori_slug
                     FROM produk p JOIN kategori k ON k.id = p.kategori_id
                     WHERE p.id = ? AND p.is_aktif = 1 LIMIT 1"
                );
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                return $row ?: null;
            } catch (Throwable $ex) {
                error_log('[repo] produk_id: ' . $ex->getMessage());
            }
        }

        $d = data_contoh();
        $namaKategori = [];
        $slugKategoriMap = [];
        foreach ($d['kategori'] as $k) {
            $namaKategori[(int) $k['id']] = $k['nama'];
            $slugKategoriMap[(int) $k['id']] = $k['slug'];
        }
        foreach ($d['produk'] as $p) {
            if ((int) $p['id'] === $id && (int) $p['is_aktif'] === 1) {
                $p['kategori_nama'] = $namaKategori[(int) $p['kategori_id']] ?? '';
                $p['kategori_slug'] = $slugKategoriMap[(int) $p['kategori_id']] ?? '';
                return $p;
            }
        }
        return null;
    }
}

if (!function_exists('repo_produk_slug')) {
    /** Satu produk aktif berdasarkan slug (beserta nama kategori). */
    function repo_produk_slug(string $slug): ?array
    {
        if (db_tersedia()) {
            try {
                $stmt = $GLOBALS['pdo']->prepare(
                    "SELECT p.*, k.nama AS kategori_nama, k.slug AS kategori_slug
                     FROM produk p JOIN kategori k ON k.id = p.kategori_id
                     WHERE p.slug = ? AND p.is_aktif = 1 LIMIT 1"
                );
                $stmt->execute([$slug]);
                $row = $stmt->fetch();
                return $row ?: null;
            } catch (Throwable $ex) {
                error_log('[repo] produk_slug: ' . $ex->getMessage());
            }
        }

        $d = data_contoh();
        $namaKategori = [];
        $slugKategoriMap = [];
        foreach ($d['kategori'] as $k) {
            $namaKategori[(int) $k['id']] = $k['nama'];
            $slugKategoriMap[(int) $k['id']] = $k['slug'];
        }
        foreach ($d['produk'] as $p) {
            if ($p['slug'] === $slug && (int) $p['is_aktif'] === 1) {
                $p['kategori_nama'] = $namaKategori[(int) $p['kategori_id']] ?? '';
                $p['kategori_slug'] = $slugKategoriMap[(int) $p['kategori_id']] ?? '';
                return $p;
            }
        }
        return null;
    }
}

if (!function_exists('repo_variasi_per_produk')) {
    /**
     * Variasi dikelompokkan per produk_id.
     * @return array<int,array<int,array>>
     */
    function repo_variasi_per_produk(): array
    {
        $hasil = [];
        if (db_tersedia()) {
            try {
                foreach ($GLOBALS['pdo']->query('SELECT * FROM variasi_produk ORDER BY produk_id, urutan, id') as $v) {
                    $hasil[(int) $v['produk_id']][] = $v;
                }
                return $hasil;
            } catch (Throwable $ex) {
                error_log('[repo] variasi: ' . $ex->getMessage());
            }
        }
        foreach (data_contoh()['variasi_produk'] as $v) {
            $hasil[(int) $v['produk_id']][] = $v;
        }
        foreach ($hasil as $pid => $daftar) {
            usort($daftar, static fn ($a, $b) => ((int) $a['urutan'] <=> (int) $b['urutan']) ?: ((int) $a['id'] <=> (int) $b['id']));
            $hasil[$pid] = $daftar;
        }
        return $hasil;
    }
}

if (!function_exists('repo_gambar_per_produk')) {
    /**
     * Foto utama per produk: map produk_id -> nama_file.
     * Prioritas foto utama, lalu foto dengan urutan/id terkecil.
     * @return array<int,string>
     */
    function repo_gambar_per_produk(): array
    {
        $hasil = [];
        if (!db_tersedia()) {
            return $hasil;
        }
        try {
            $baris = $GLOBALS['pdo']->query(
                'SELECT produk_id, nama_file FROM gambar_produk
                 ORDER BY produk_id, is_utama DESC, urutan, id'
            )->fetchAll();
            foreach ($baris as $g) {
                $pid = (int) $g['produk_id'];
                if (!isset($hasil[$pid])) {
                    $hasil[$pid] = (string) $g['nama_file'];
                }
            }
        } catch (Throwable $ex) {
            error_log('[repo] gambar_produk: ' . $ex->getMessage());
        }
        return $hasil;
    }
}

if (!function_exists('repo_variasi_produk')) {
    /** Variasi untuk satu produk. */
    function repo_variasi_produk(int $produkId): array
    {
        $semua = repo_variasi_per_produk();
        return $semua[$produkId] ?? [];
    }
}

if (!function_exists('repo_aksesori_produk')) {
    /** Aksesori aktif yang terhubung ke satu produk. */
    function repo_aksesori_produk(int $produkId): array
    {
        if (db_tersedia()) {
            try {
                $stmt = $GLOBALS['pdo']->prepare(
                    'SELECT a.* FROM aksesori a
                     JOIN produk_aksesori pa ON pa.aksesori_id = a.id
                     WHERE pa.produk_id = ? AND a.is_aktif = 1
                     ORDER BY a.nama'
                );
                $stmt->execute([$produkId]);
                return $stmt->fetchAll();
            } catch (Throwable $ex) {
                error_log('[repo] aksesori: ' . $ex->getMessage());
            }
        }

        $d = data_contoh();
        $idAksesori = [];
        foreach ($d['produk_aksesori'] as $pa) {
            if ((int) $pa['produk_id'] === $produkId) {
                $idAksesori[(int) $pa['aksesori_id']] = true;
            }
        }
        $hasil = [];
        foreach ($d['aksesori'] as $a) {
            if ((int) $a['is_aktif'] === 1 && isset($idAksesori[(int) $a['id']])) {
                $hasil[] = $a;
            }
        }
        usort($hasil, static fn ($a, $b) => strcmp((string) $a['nama'], (string) $b['nama']));
        return $hasil;
    }
}

if (!function_exists('repo_ulasan_tampil')) {
    /** Ulasan yang ditampilkan (is_tampil=1). */
    function repo_ulasan_tampil(): array
    {
        if (db_tersedia()) {
            try {
                return $GLOBALS['pdo']->query('SELECT * FROM ulasan WHERE is_tampil = 1 ORDER BY created_at DESC')->fetchAll();
            } catch (Throwable $ex) {
                error_log('[repo] ulasan: ' . $ex->getMessage());
            }
        }
        return [];
    }
}
