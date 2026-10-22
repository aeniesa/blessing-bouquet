<?php
/**
 * upload.php — validasi & simpan gambar (§22.1 no.8/9, §26.4, keputusan K-07).
 * Hanya JPEG/PNG/WebP, maksimum 2 MB, nama file diacak.
 */
declare(strict_types=1);

function upload_max_bytes(): int
{
    return 2 * 1024 * 1024; // 2 MB
}

/** Validasi berkas gambar. Mengembalikan pesan error, atau null bila valid. */
function validasi_gambar(array $file): ?string
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return 'Berkas tidak valid.';
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // tidak ada berkas diunggah (boleh dilewati)
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Gagal mengunggah berkas (kode ' . (int) $file['error'] . ').';
    }
    if ($file['size'] > upload_max_bytes()) {
        return 'Ukuran gambar maksimal 2 MB.';
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return 'Berkas tidak valid.';
    }

    $izinMime = ['image/jpeg', 'image/png', 'image/webp'];
    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !in_array($info['mime'] ?? '', $izinMime, true)) {
        return 'Hanya gambar JPEG, PNG, atau WebP yang diizinkan.';
    }

    return null;
}

/** Peta MIME -> ekstensi. */
function ekstensi_dari_mime(string $mime): ?string
{
    return match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => null,
    };
}

/**
 * Simpan berkas gambar ke folder tujuan dengan nama acak.
 * Mengembalikan nama file tersimpan, atau melempar RuntimeException.
 */
function simpan_gambar(array $file, string $tujuan): string
{
    $error = validasi_gambar($file);
    if ($error !== null) {
        throw new RuntimeException($error);
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('Tidak ada berkas untuk disimpan.');
    }

    $info = getimagesize($file['tmp_name']);
    $ext  = ekstensi_dari_mime($info['mime'] ?? '');
    if ($ext === null) {
        throw new RuntimeException('Jenis gambar tidak didukung.');
    }

    if (!is_dir($tujuan) && !mkdir($tujuan, 0775, true) && !is_dir($tujuan)) {
        throw new RuntimeException('Folder tujuan tidak dapat dibuat.');
    }

    $nama = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], rtrim($tujuan, '/\\') . DIRECTORY_SEPARATOR . $nama)) {
        throw new RuntimeException('Gagal menyimpan gambar.');
    }
    return $nama;
}

/** Hapus berkas gambar dari folder upload (nama file divalidasi). */
function hapus_gambar(string $folder, string $namaFile): void
{
    $namaFile = basename($namaFile);
    $path = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR . $namaFile;
    if (is_file($path)) {
        @unlink($path);
    }
}
