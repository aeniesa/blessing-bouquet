# Analisis Kesiapan Produksi & Ekosistem GitHub — Blessing Bouquet

> Disusun berdasarkan `60225086_Anisah Nurul Hidayah_Blueprint.pdf`.
> Keputusan pengguna: **target deploy = lokal XAMPP untuk sekarang**, **gaya kerja = sederhana sesuai blueprint apa adanya**, **deliverable = dokumen & analisis saja (tanpa membuat kode aplikasi)**.

---

## 0. Ringkasan Keputusan & Cara Membaca Dokumen

| Aspek | Keputusan | Implikasi |
|---|---|---|
| Lingkup saat ini | Website katalog + panel admin, PHP native + MySQL (XAMPP lokal) | Fokus pada kualitas kode & struktur, bukan infrastruktur cloud |
| Biaya | Rp0 | Tidak ada layanan berbayar, CI GitHub gratis, hosting opsional |
| Gaya | Sederhana sesuai blueprint | Tanpa framework/Composer wajib; tooling opsional |
| Deliverable | Dokumen analisis | Tak ada file kode yang ditulis |

Dokumen ini dibagi: **analisis blueprint**, **checklist produksi**, **ekosistem GitHub ringan**, **rekomendasi teknis**, dan **roadmap**.

---

## 1. Hasil Analisis Blueprint

### 1.1 Yang sudah baik
- **Ruang lingkup jelas**: bagian "termasuk" dan "tidak termasuk" tegas; batasan Rp0 eksplisit.
- **Model harga terperinci**: `tetap`, `mulai_dari`, `berdasarkan_permintaan`, `biaya_jasa` — menghindari jebakan "harga belum ada = Rp0".
- **Skema database lengkap**: ERD + struktur kolom + aturan relasi (`CASCADE`/`RESTRICT`) sudah benar.
- **Keamanan dasar sudah dipikirkan**: PDO prepared statements, `password_hash()`, CSRF, validasi upload, rename file acak, `.htaccess` di `uploads/`.
- **Dokumentasi diagram**: user flow, activity, sequence, arsitektur — sangat membantu implementasi.
- **Data seed katalog** (bagian 11) rinci dan siap dimasukkan ke `database.sql`.

### 1.2 Celah, risiko, dan prioritas

**Legenda prioritas:** 🔴 kritis · 🟠 penting · 🟡 sebaiknya
**Legenda waktu:** `SEKARANG` (relevan saat lokal) · `ONLINE` (wajib sebelum publikasi)

| # | Celah | Dampak | Prioritas | Kapan |
|---|---|---|---|---|
| 1 | Document root `public/` tapi `admin/`, `includes/`, `database/` di luarnya | Di shared hosting, `includes/config.php` (kredensial DB) bisa terekspos | 🔴 | ONLINE |
| 2 | Config hardcoded di `config.php`, tanpa file contoh & tanpa pemisahan dev/prod | Kredensial ikut ter-commit; sulit ganti setelan antar lingkungan | 🔴 | SEKARANG |
| 3 | `database/install.php` membuat admin | Jika lupa dihapus di produksi, siapa pun bisa membuat admin | 🔴 | ONLINE |
| 4 | HTTPS + header keamanan (HSTS, CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy) belum ada | Rentan clickjacking, MIME sniffing, bocor referrer | 🟠 | ONLINE |
| 5 | Cookie sesi belum diatur `HttpOnly`, `Secure`, `SameSite` | Rentan pencurian sesi (XSS/downgrade) | 🟠 | ONLINE |
| 6 | Tanpa rate-limit / proteksi brute-force login | Password admin bisa di-brute force | 🟠 | ONLINE |
| 7 | Penanganan error belum produksi (`display_errors`, logging terpusat) | Detail error bocor ke pengguna | 🟠 | ONLINE |
| 8 | Migrasi DB belum versioned (hanya `database.sql` idempotent) | Sulit update skema saat produksi | 🟡 | ONLINE |
| 9 | Tanpa strategi backup DB + `uploads/` | Kehilangan data tak bisa dipulihkan | 🟠 | ONLINE |
| 10 | Asumsi deploy XAMPP; belum ada panduan cPanel/hosting | Siap lokal, belum siap publik | 🟡 | ONLINE |
| 11 | Tanpa otomatisasi lint/test | Regresi mudah lolos | 🟡 | SEKARANG |
| 12 | SEO belum operasional: meta/OG tags, `sitemap.xml`, `robots.txt` | Produk sulit ditemukan mesin pencari | 🟡 | ONLINE |
| 13 | Optimasi gambar hanya disebut sekilas (tanpa thumbnail/WebP/lazy-load) | Halaman berat, terutama di HP | 🟡 | ONLINE |
| 14 | Belum ada Privacy Policy & Syarat Layanan | Risiko legal atas data pelanggan via WhatsApp | 🟡 | ONLINE |
| 15 | Validasi URL embed (wa.me, instagram, maps) baru rencana | Potensi injeksi iframe/redirectjahat | 🟠 | SEKARANG |

> Catatan: celah keamanan #1–#7, #15 sudah sebagian disebut di bagian 19 blueprint, tetapi belum menjadi **checklist operasional** dengan cara implementasi. Bagian 3–4 di bawah menutup itu.

---

## 2. Checklist Kesiapan Produksi

### A. Wajib sekarang (dipakai lokal)
- [ ] `git init` + `.gitignore` (kecualikan `config.php` asli, `.env`, isi `uploads/`, `.idea/`, `*.log`).
- [ ] Pisahkan konfigurasi: `config.example.php` (masuk git) vs `config.php` (tidak masuk git).
- [ ] Letakkan kredensial di satu tempat; jangan hardcode di banyak file.
- [ ] Buat `database.sql` idempotent (`CREATE TABLE IF NOT EXISTS`) + seed katalog bagian 11.
- [ ] Pastikan semua output di-escape `htmlspecialchars()`, semua query PDO prepared.
- [ ] Terapkan CSRF token di semua form POST (publik & admin).
- [ ] Validasi whitelist URL embed (`wa.me`, `instagram.com`, `google.com/maps`).
- [ ] Impor `uploads/.htaccess` yang menolak eksekusi PHP.

### B. Wajib sebelum online
- [ ] Perbaiki document root: satukan `admin/` ke dalam `public/` **atau** gunakan vhost yang menunjuk ke `public/` dan blokir akses `includes/` + `database/`.
- [ ] Hapus/lindungi `database/install.php` setelah instalasi (pindahkan ke CLI atau kunci ganda).
- [ ] Paksa HTTPS + kirim security headers.
- [ ] Set `session.cookie_httponly=1`, `session.cookie_secure=1`, `session.cookie_samesite=Lax`.
- [ ] Batasi percobaan login (mis. 5x per 15 menit per IP) + catat log.
- [ ] `display_errors=Off` + log ke file di luar document root.
- [ ] `.env.example` didokumentasikan di README.
- [ ] Siapkan backup terjadwal DB + `uploads/` dan prosedur restore.
- [ ] `robots.txt`, `sitemap.xml`, meta/OG tag di header.
- [ ] Halaman Privacy Policy & Syarat Layanan.
- [ ] Buat akun admin produksi (bukan admin demo) via `install.php` lalu hapus.

### C. Sebaiknya
- [ ] Migrasi DB versioned (`migrations/0001_...sql`).
- [ ] Thumbnail/WebP + `loading="lazy"` untuk gambar produk.
- [ ] `php -l` otomatis di CI dan/atau git pre-commit hook.
- [ ] Uji aksesibilitas dasar (kontras, alt text, fokus keyboard).

---

## 3. Ekosistem GitHub (Versi Ringan)

Disesuaikan dengan gaya "sederhana, tanpa tooling berat". Cukup GitHub gratis, tanpa Docker/cloud.

### 3.1 Struktur repo & `.gitignore`

```
blessing-bouquet/
├── .gitignore
├── .editorconfig
├── LICENSE
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
├── CODE_OF_CONDUCT.md
├── SECURITY.md
├── .github/
│   ├── ISSUE_TEMPLATE/
│   │   ├── bug_report.md
│   │   └── feature_request.md
│   ├── pull_request_template.md
│   └── workflows/
│       └── ci.yml
├── public/            # doc root
├── admin/
├── includes/          # config.php TIDAK di-commit
├── database/
└── docs/
```

Contoh `.gitignore`:

```gitignore
# Konfigurasi & rahasia
includes/config.php
.env
*.local.php

# Upload dinamis (jangan simpan konten pengguna)
public/uploads/produk/*
public/uploads/toko/*
!public/uploads/**/.gitkeep

# Log & OS/IDE
*.log
.idea/
.vscode/
.DS_Store
Thumbs.db
```

### 3.2 File wajib repo (isi ringkas)
- **README.md** — deskripsi, fitur, cara instalasi lokal (XAMPP), struktur folder, kredensial default (untuk dev).
- **LICENSE** — rekomendasi MIT agar sederhana (atau proprietary bila tidak ingin dibuka).
- **CONTRIBUTING.md** — cara membuat branch, aturan commit, cara menjalankan lint.
- **CODE_OF_CONDUCT.md** — pakai templat standar Contributor Covenant.
- **SECURITY.md** — cara melaporkan celah keamanan (jangan lewat Issue publik).
- **CHANGELOG.md** — format [Keep a Changelog](https://keepachangelog.com/) + SemVer.

### 3.3 Templates
- `.github/ISSUE_TEMPLATE/bug_report.md` — langkah reproduksi, versi PHP, ekspektasi vs aktual.
- `.github/ISSUE_TEMPLATE/feature_request.md` — kebutuhan, kriteria terima.
- `.github/pull_request_template.md` — deskripsi, jenis perubahan, checklist (lulus `php -l`, tidak commit `config.php`, dsb).

### 3.4 GitHub Actions minimal (tanpa Composer)
Cukup cek sintaks PHP — gratis dan cepat:

```yaml
# .github/workflows/ci.yml
name: CI
on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]
jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: PHP lint
        run: find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
```

Opsional bertahap: gitleaks (deteksi rahasia), PHPStan/PHP-CS-Fixer (bila kelak pakai Composer).

### 3.5 Alur kerja (workflow) usulan
- **Branch**: `main` (stabil) + `develop` (opsional) + `feat/*`, `fix/*`, `docs/*`.
- **Commit**: Conventional Commits (`feat:`, `fix:`, `docs:`, `chore:`).
- **PR**: wajib lulus CI sebelum merge; aktifkan **branch protection** pada `main`.
- **Release**: tag `v1.0.0` → tulis ringkasan di `CHANGELOG.md`.
- **Rahasia**: jangan pernah commit `config.php`/`.env`; gunakan **GitHub Environments + Secrets** hanya jika kelak auto-deploy.

### 3.6 Yang ditunda (belum perlu karena lokal)
Docker Compose, deploy otomatis SSH/FTP, CodeQL, semantic-release, GitHub Pages. Bisa ditambah saat pindah ke hosting online.

---

## 4. Rekomendasi Teknis Konkret

### 4.1 Struktur folder & document root
- **Masalah**: blueprint menaruh `admin/`, `includes/`, `database/` di luar `public/`. Bila doc root = root project, semuanya terekspos.
- **Opsi A (aman, tetap ramah XAMPP)**: buat vhost XAMPP yang `DocumentRoot`-nya menunjuk ke `.../public`, lalu `admin/`, `includes/`, `database/` di atasnya tidak terakses HTTP.
- **Opsi B (aman, ramah shared hosting)**: pindahkan `admin/` ke `public/admin/` dan `includes/` ke `public/includes/`, lalu blokir `public/includes/` via `.htaccess`.
- Rekomendasi: **Opsi A untuk lokal sekarang**, siapkan **Opsi B** saat publikasi.

### 4.2 Konfigurasi & rahasia
- Simpan kredensial hanya di `includes/config.php` (di-ignore git) + sediakan `includes/config.example.php`.
- Hindari hardcode nomor WhatsApp di kode; ambil dari `pengaturan_toko.whatsapp` (sesuai blueprint bagian 20).

### 4.3 Database & migrasi
- `database.sql` idempotent + seed. Tambahkan folder `database/migrations/` untuk perubahan bertahap dengan penomoran.
- Nominal selalu `DECIMAL(12,2)`; "harga belum diketahui" = `NULL` (bukan 0).

### 4.4 Keamanan (checklist implementasi)
| Kontrol | Implementasi |
|---|---|
| SQL Injection | PDO prepared (`db.php`) |
| XSS | `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` pada semua output |
| Password | `password_hash(PASSWORD_DEFAULT)` + `password_verify()` |
| Sesi | `session_regenerate_id(true)` setelah login; cookie `HttpOnly/Secure/SameSite` |
| CSRF | `csrf_token()` + `csrf_verify()` di semua form POST |
| Upload | whitelist MIME (jpeg/png/webp) + ekstensi + ukuran; `bin2hex(random_bytes(8))` |
| `.htaccess` | tolak eksekusi di `uploads/`; tolak akses langsung `includes/` & `database/` |
| Embed URL | whitelist host: `wa.me`, `instagram.com`, `google.com/maps` |
| Error DB | pesan umum ke pengguna; detail ke log |
| Redirect | cek `$_SERVER['REQUEST_METHOD'] === 'POST'` sebelum proses |

### 4.5 Error & logging
- `display_errors=Off` di produksi; `log_errors=On`, arahkan ke file di luar doc root.
- Buat helper `log_error()` agar format konsisten.

### 4.6 Backup
- Skrip dump DB (`mysqldump`) + arsip `uploads/`.
- Jadwal manual dulu (sebelum online: terjadwal via cron/Task Scheduler).

### 4.7 SEO, aksesibilitas, performa
- Meta title/description + Open Graph per halaman; `sitemap.xml`, `robots.txt`.
- `alt` pada gambar, kontras cukup, navigasi keyboard.
- `loading="lazy"`, kompresi gambar, hindari animasi berat (sesuai blueprint bagian 5).

---

## 5. Roadmap Bertahap

| Fase | Fokus | Hasil |
|---|---|---|
| 0 (sekarang) | Dokumen & analisis ini | Peta jalan disetujui |
| 1 | Repo GitHub + `.gitignore` + README/templates + CI `php -l` | Repo rapi, versi terkendali |
| 2 | Struktur folder + config terpisah + `db.php` + helper + `database.sql` | Fondasi siap koding |
| 3 | Situs publik (beranda → katalog → detail → daftar pesanan → WhatsApp → kontak → 404) | Fitur pelanggan jalan |
| 4 | Panel admin (login → dashboard → CRUD produk/kategori → pengaturan → ulasan → akun) | Konten bisa dikelola |
| 5 | Uji sesuai test case blueprint + perbaikan keamanan §4.4 | Siap demo lokal |
| 6 (online, opsional) | Perbaiki document root, HTTPS, header, rate-limit, backup, SEO, legal | Siap publikasi |

---

## 6. Lampiran

### 6.1 Sample `.editorconfig`
```ini
root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
indent_style = space
indent_size = 4

[*.{yml,yaml,md}]
indent_size = 2
```

### 6.2 Aturan commit (Conventional Commits)
`feat:`, `fix:`, `docs:`, `style:`, `refactor:`, `test:`, `chore:` — judul singkat, isi menjelaskan alasan.

### 6.3 Definition of Done satu fitur
- [ ] Lolos `php -l` (dan CI hijau).
- [ ] Query pakai prepared statement; output di-escape.
- [ ] Form POST punya CSRF token.
- [ ] Tidak ada kredensial yang ter-commit.
- [ ] Kondisi kosong punya tampilan informatif (sesuai prinsip UI blueprint).
- [ ] Diuji di mobile + desktop.

---

*Dokumen ini adalah analisis & rekomendasi. Folder proyek saat ini hanya berisi PDF blueprint; belum ada kode aplikasi.*
