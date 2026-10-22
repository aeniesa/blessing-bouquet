<?php
/**
 * admin/login.php — Login admin (A-01).
 * Username + password (K-03), CSRF, pesan error aman.
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if (auth_check()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$toko    = ambil_pengaturan($pdo);
$palet   = palet_variabel($toko['palet_warna'] ?? 'sage_green');
$error   = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = (string) ($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif (!db_tersedia() || !$pdo instanceof PDO) {
        $error = 'Database belum tersedia. Panel admin memerlukan database yang aktif.';
    } elseif (auth_login($pdo, $username, $password)) {
        header('Location: ' . base_url('admin/dashboard.php'));
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Admin — <?= e($toko['nama_toko'] ?: 'Blessing Bouquet') ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Nunito+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(aset('admin/assets/css/admin.css')) ?>">
    <style>:root {
        <?php foreach ($palet as $k => $v): ?><?= e($k) ?>: <?= e($v) ?>;<?php endforeach; ?>
    }</style>
</head>
<body class="admin-login-body">
    <main class="login-wrap">
        <section class="login-kartu" aria-labelledby="judul-login">
            <p class="login-eyebrow">Panel Admin</p>
            <h1 id="judul-login" class="login-judul"><?= e($toko['nama_toko'] ?: 'Blessing Bouquet') ?></h1>
            <p class="login-sub">Masuk untuk mengelola website.</p>

            <?php if ($error !== ''): ?>
                <div class="notis notis-galat" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(aset('admin/login.php')) ?>" class="form-login" autocomplete="on">
                <?= csrf_field() ?>
                <label class="form-baris">
                    <span>Username</span>
                    <input type="text" name="username" value="<?= e($username) ?>" required autofocus autocomplete="username">
                </label>
                <label class="form-baris">
                    <span>Password</span>
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <button type="submit" class="tombol tombol-utama tombol-lebar">Masuk</button>
            </form>

            <p class="login-kembali"><a href="<?= e(base_url('index.php')) ?>">&larr; Kembali ke website</a></p>
        </section>
    </main>
</body>
</html>
