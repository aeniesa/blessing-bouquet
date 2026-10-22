<?php
/**
 * db.php — satu koneksi PDO dipakai ulang (§26.2).
 * Mengembalikan instance PDO singleton dengan mode exception + utf8mb4.
 */
declare(strict_types=1);

function db(): ?PDO
{
    static $pdo  = null;
    static $dicek = false;

    if ($dicek) {
        return $pdo;
    }
    $dicek = true;

    $config = $GLOBALS['config'] ?? require __DIR__ . '/config.php';
    $c = $config['db'];

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $c['host'],
        (int) $c['port'],
        $c['name'],
        $c['charset'] ?? 'utf8mb4'
    );

    try {
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 3,
        ]);
        $GLOBALS['DB_TERSEDIA'] = true;
    } catch (PDOException $ex) {
        // Database belum siap/tidak tersedia. Jangan hentikan halaman publik;
        // lapisan repositori akan memakai data contoh (mode_contoh()).
        error_log('[DB] ' . $ex->getMessage());
        $pdo = null;
        $GLOBALS['DB_TERSEDIA'] = false;
    }

    return $pdo;
}
