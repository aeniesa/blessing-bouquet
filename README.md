# Blessing Bouquet

Website katalog dan pemesanan buket berbasis WhatsApp (PHP native + MySQL/MariaDB, HTML/CSS/JS).
Dijalankan **lokal via XAMPP**, tanpa layanan berbayar.

> Blueprint acuan: `docs/BLUEPRINT-REVISI.md` (versi final 2.2). Seluruh harga, data produk,
> dan ketentuan pemesanan mengikuti blueprint dan **tidak diubah**.

---

## Status tahap saat ini (fondasi ~25%)

Selesai:

- Struktur folder sesuai blueprint (`public/` doc root, `includes/`, `database/`).
- Skema database + seed (`database/database.sql`): 9 tabel + relasi, 11 produk, 36 variasi, 7 aksesori.
- Koneksi database via **PDO** (`includes/db.php`) — sudah diuji.
- Halaman awal publik elegan (ivory / dusty pink / beige) — `public/index.php`.
- Login admin (username + password, hash bcrypt, CSRF, sesi aman) + logout + dashboard.
- PWA dasar (manifest, service worker, ikon, halaman offline), `.htaccess` proteksi, halaman 404.

Belum dikerjakan (tahap berikutnya): katalog, detail produk, daftar pesanan (session),
keranjang & pesan WhatsApp, dan seluruh CRUD panel admin (produk, kategori, pengaturan, ulasan, akun).

---

## Kebutuhan

- **XAMPP** (Apache + PHP 8 + MySQL/MariaDB), terpasang di `C:\xampp` (default).
- Browser modern (Chrome/Firefox/Edge).
- Tanpa layanan berbayar, tanpa framework.

---

## Cara Menjalankan (lokal XAMPP)

### 1. Jalankan Apache & MySQL
Buka **XAMPP Control Panel** → klik **Start** pada **Apache** dan **MySQL**.

### 2. Siapkan database
Database `blessing_bouquet` + seluruh tabel & data sudah diimpor. Bila perlu ulang:

- Buka `http://localhost/phpmyadmin` → import `database/database.sql`, atau
- Terminal:
  ```
  C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS blessing_bouquet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  C:\xampp\mysql\bin\mysql.exe -u root blessing_bouquet < "D:\Blessing Bouqet\database\database.sql"
  ```

### 3. Konfigurasi koneksi
`includes/config.php` sudah diisi untuk XAMPP default (`root`, tanpa password).
Bila kredensial berbeda, salin dari `includes/config.example.php` lalu sesuaikan.

### 4. Akses website
Proyek dilayani melalui sebuah **junction** di `C:\xampp\htdocs\blessing-bouquet`
yang mengarah ke `D:\Blessing Bouqet\public`. Jika junction belum ada, buat dengan:
```powershell
New-Item -ItemType Junction -Path "C:\xampp\htdocs\blessing-bouquet" -Target "D:\Blessing Bouqet\public"
```
Lalu buka:

| Halaman | URL |
|---|---|
| Beranda | `http://localhost/blessing-bouquet/` |
| Login Admin | `http://localhost/blessing-bouquet/admin/login.php` |

**Alternatif tanpa junction (virtual host, disarankan blueprint §27.1):**
tambahkan pada `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    DocumentRoot "D:/Blessing Bouqet/public"
    ServerName blessing-bouquet.local
    <Directory "D:/Blessing Bouqet/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Tambahkan `127.0.0.1 blessing-bouquet.local` ke `C:\Windows\System32\drivers\etc\hosts`,
lalu ubah `ErrorDocument` di `public/.htaccess` menjadi `/404.php`.

### 5. Akun admin
Akun admin sudah dibuat:
```
Username: admin
Password: admin12345
```
> Segera ganti password pada tahap panel akun. Untuk membuat akun baru:
> ```
> C:\xampp\php\php.exe "D:\Blessing Bouqet\database\install.php"
> ```

### 6. Keamanan
Setelah yakin tidak diperlukan lagi, **hapus** `database/install.php` (§22.1 no.16).

---

## Struktur Folder

```
Blessing Bouqet/
├── public/                       # DOCUMENT ROOT
│   ├── index.php                 # Beranda
│   ├── 404.php                   # Halaman tidak ditemukan
│   ├── manifest.json, sw.js      # PWA
│   ├── offline.html
│   ├── admin/                    # Panel admin (login, logout, dashboard)
│   ├── assets/                   # css, js, img (ikon)
│   └── uploads/                  # unggahan (produk/, toko/)
├── includes/                     # DI LUAR doc root
│   ├── config.php / config.example.php
│   ├── bootstrap.php, db.php, functions.php, csrf.php, auth.php, upload.php
│   └── header.php, footer.php, admin-header.php
├── database/
│   ├── database.sql              # Skema + seed (idempotent)
│   └── install.php               # Buat admin pertama (hapus setelah dipakai)
├── docs/                         # Blueprint & diagram
└── README.md
```

---

## Pengujian yang Sudah Dilakukan

| Uji | Hasil |
|---|---|
| Koneksi PDO ke `blessing_bouquet` | OK (MariaDB 10.4.32) |
| Beranda `http://localhost/blessing-bouquet/` | HTTP 200, produk dari DB tampil |
| Aset (CSS, ikon, manifest) | HTTP 200 |
| Halaman 404 kustom | HTTP 404 + tombol beranda |
| Login valid → dashboard | OK |
| Login tanpa sesi pada dashboard | dialihkan ke login |
| Password salah | pesan aman, tanpa bocor detail |
| POST form tanpa CSRF | ditolak |
| Akses `config.php` / `database.sql` / `includes/` via URL | HTTP 404 (tertolak) |

---

## Catatan

- Pesan pemesanan WhatsApp mengarah ke `wa.me/6285920064874` (dari `pengaturan_toko.whatsapp`).
- Harga belum diketahui disimpan `NULL` dan ditampilkan "perlu dikonfirmasi" (bukan Rp0).
- Dokumen blueprint asli di root proyek tidak diubah; salinannya ada di `docs/`.
