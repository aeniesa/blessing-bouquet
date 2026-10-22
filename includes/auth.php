<?php
/**
 * auth.php — autentikasi admin (§4.2 A-01/A-08, §22.1 no.4 & no.5).
 * Login memakai username + password (keputusan K-03).
 */
declare(strict_types=1);

function auth_login(PDO $pdo, string $username, string $password): bool
{
    $username = trim($username);
    if ($username === '' || $password === '') {
        return false;
    }

    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admin WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']       = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        return true;
    }
    return false;
}

function auth_check(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!auth_check()) {
        header('Location: ' . base_url('admin/login.php'));
        exit;
    }
}

function auth_admin(PDO $pdo): ?array
{
    if (!auth_check()) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id, username FROM admin WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['admin_id']]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}
