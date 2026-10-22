<?php
/**
 * bootstrap.php — titik masuk bersama untuk semua halaman.
 * Memuat konfigurasi, basis URL, sesi aman, koneksi PDO, dan helper.
 */
declare(strict_types=1);

$__configFile = __DIR__ . '/config.php';
if (!is_file($__configFile)) {
    http_response_code(500);
    exit('Konfigurasi belum dibuat. Salin includes/config.example.php menjadi includes/config.php.');
}

$config = require $__configFile;
$GLOBALS['config'] = $config;

date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Jakarta');

// ---- Deteksi basis URL (mendukung subfolder XAMPP maupun doc root) ----
$__scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$__scriptDir = rtrim($__scriptDir, '/');
if (substr($__scriptDir, -6) === '/admin') {
    $__scriptDir = substr($__scriptDir, 0, -6);
}
define('APP_BASE', $__scriptDir);                 // contoh: '' atau '/blessing-bouquet'
define('APP_ROOT', dirname(__DIR__));             // .../Blessing Bouqet
define('PUBLIC_DIR', APP_ROOT . '/public');
define('UPLOAD_DIR', PUBLIC_DIR . '/uploads');

// ---- Sesi aman (§22.1 no.5 & no.6) ----
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('BB_SESSION');
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/repositori.php';
require_once __DIR__ . '/keranjang.php';

$pdo = db();
$GLOBALS['pdo'] = $pdo;

if (!defined('MODE_CONTOH')) {
    define('MODE_CONTOH', $pdo === null);
}
