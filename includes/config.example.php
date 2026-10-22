<?php
/**
 * config.example.php — CONTOH konfigurasi.
 * Salin file ini menjadi config.php lalu sesuaikan bila perlu.
 * JANGAN commit config.php berisi kredensial asli ke repository.
 */
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'blessing_bouquet',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'timezone' => 'Asia/Jakarta',
        'debug'    => false,
    ],
];
