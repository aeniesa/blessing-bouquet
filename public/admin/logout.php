<?php
/**
 * admin/logout.php — Hapus sesi admin (A-08).
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

auth_logout();

header('Location: ' . base_url('admin/login.php'));
exit;
