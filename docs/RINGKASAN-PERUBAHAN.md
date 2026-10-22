# RINGKASAN PERUBAHAN & PEMERIKSAAN KONSISTENSI

> **Dokumen acuan:** `BLUEPRINT-REVISI.md` (versi 2.1)
> **Blueprint asli:** `60225086_Anisah Nurul Hidayah_Blueprint.pdf` (tidak diubah)
> **Sifat pekerjaan:** revisi dokumen saja — **tidak ada kode aplikasi, init proyek, atau build**.
> **Status:** seluruh butir konfirmasi (K-01…K-10) **telah disetujui** dan diterapkan.

---

## A. Pemeriksaan Konsistensi (hasil "cek silang")

Metode: membandingkan **Kebutuhan Fungsional** ↔ **Skema Tabel** ↔ **ERD** ↔ **Diagram (Use Case/Activity/Sequence/Class/Component/Deployment)** ↔ **Rancangan Keamanan** ↔ **Ruang Lingkup**, lalu menandai setiap ketidakselarasan.

| # | Area diperiksa | Temuan pada blueprint asli | Status setelah revisi |
|---|---|---|---|
| C-01 | F-01 beranda ↔ tabel `pengaturan_toko` | Ada kebutuhan "teks tombol" (A-06) tetapi tidak ada kolomnya | ✅ Ditambah `teks_tombol_katalog` (§13.8) |
| C-02 | A-01 login ↔ tabel `admin` | Teks "email/username", tetapi tabel hanya punya `username` | ✅ Diselaraskan jadi **username** — **disetujui K-03** (§4.2) |
| C-03 | A-07 edit ulasan ↔ tabel `ulasan` | Tidak ada `updated_at` meski diedit | ✅ Ditambah `updated_at` (§13.9) |
| C-04 | ERD `ADMIN → PRODUK` ↔ tabel | Relasi digambar, tetapi tak ada kolom/tabel penghubung/log | ✅ Relasi dihapus; **audit log tidak digunakan — disetujui K-08** (§12) |
| C-05 | `jenis_harga` enum | ERD tidak menulis isi enum; §13 menulis | ✅ Diselaraskan (nilai sama di §11.1 & §13.3) |
| C-06 | Struktur folder ↔ document root | `admin/` di luar `public/` → tak bisa diakses bila doc root = `public/` | ✅ `admin/` dipindah ke dalam `public/` (§6, §21) |
| C-07 | Harga variasi (Pita Satin, Buket Uang) ↔ `variasi_produk` | Harga berbentuk **matriks 2 dimensi**, sedangkan tabel hanya 1 dimensi | ✅ Aturan pemodelan ditetapkan: pisah jadi produk terpisah — **disetujui K-04** (§11.3) |
| C-08 | Palet warna §10.1 ↔ `palet_warna` | 4 palet disebut, tetapi hanya 1 set kode warna diberi | ✅ Whitelist + kode warna 4 palet ditetapkan — **disetujui K-06** (§10.1) |
| C-09 | Subtotal §20 ↔ aturan harga | "Harga pasti" tidak didefinisikan | ✅ Definisi "harga pasti" ditetapkan (§11.1) |
| C-10 | Keranjang (session) | Struktur session tidak dijelaskan | ✅ Struktur item keranjang + catatan ditetapkan (§26.3) |
| C-11 | F-06 "catatan" ↔ pesan WhatsApp | Pesan memuat "Catatan tambahan" tanpa tempat penyimpanan | ✅ Catatan ditambahkan ke struktur session (§26.3) |
| C-12 | Use Case Diagram | Hanya daftar teks tanpa aktor/relasi | ✅ Diformalkan menjadi diagram + `include` (§14) |
| C-13 | Activity/Sequence Diagram | Teks ASCII, belum tergambar | ✅ Diformalkan ke Mermaid (§15–16) |
| C-14 | Class/Component/Deployment Diagram | **Tidak ada** di blueprint asli | ✅ **[DITAMBAHKAN]** (§17–19) |
| C-15 | Fitur PWA | **Tidak ada** di blueprint asli | ✅ **[DITAMBAHKAN]** (§24) |
| C-16 | Keamanan §19 ↔ lokal/online | Semua disamaratakan tanpa pisah lingkungan | ✅ Dipisah: §22.1 lokal wajib vs §22.2 online (§22) |
| C-17 | Test case §21 | Terpotong pada T-02 | ✅ Dilengkapi T-01…T-30 (§25) |
| C-18 | `install.php` | Wajib dibuat tetapi risiko jika lupa dihapus, belum ditegaskan | ✅ Ditegaskan hapus setelah dipakai (§13, §22.1, §27.1) |
| C-19 | `config.php` | Kredensial hardcoded tanpa contoh untuk Git | ✅ `config.example.php` ditambahkan (§21, §22.1) |
| C-20 | Nomor WhatsApp | Konsisten `085920064874` → `wa.me/6285920064874` | ✅ Dipertahankan apa adanya |
| C-21 | Harga & data katalog §11 | — | ✅ Dipertahankan apa adanya (tidak diubah) |
| C-22 | Ketentuan Pita Satin (durasi, DP, dll.) | — | ✅ Dipertahankan apa adanya |
| C-23 | Ruang lingkup & fitur utama | — | ✅ Tidak ada fitur utama yang dihapus |

**Kesimpulan pemeriksaan:** tidak ditemukan kontradiksi yang tersisa pada tingkat *kebutuhan–skema–diagram*. Semua ketidakselarasan di atas sudah diperbaiki atau ditandai eksplisit untuk konfirmasi. Harga, data produk, ketentuan, dan informasi toko **tidak berubah**.

---

## B. Ringkasan Perubahan & Alasannya

### B-1. Struktur folder & document root (dampak besar)
- **Diubah:** `admin/` dipindah ke dalam `public/admin/`; `includes/` & `database/` tetap di luar `public/`.
- **Alasan:** pada blueprint asli, bila `public/` menjadi document root (seperti disarankan struktur "public/"), panel admin yang berada di luar `public/` menjadi **tidak dapat diakses** — kontradiksi. Memindahkannya membuat struktur konsisten dan `includes/`/`database/` tetap terlindungi dari akses URL.
- **Penanda:** WAJIB-LOKAL.

### B-2. Skema database
- **Diubah:** tambah kolom `pengaturan_toko.teks_tombol_katalog`; tambah `ulasan.updated_at`; hapus relasi `ADMIN → PRODUK` di ERD.
- **Alasan:** menutup C-01, C-03, C-04. Tidak mengubah data/harga, hanya melengkapi struktur agar kebutuhan fungsional punya tempat penyimpanan atau dibersihkan dari relasi yang mustahil.
- **Disetujui:** tanpa audit log admin (K-08).

### B-3. Model harga & variasi
- **Diubah:** menetapkan bahwa `variasi_produk` = pemilih harga **satu dimensi**; kasus dua dimensi (Pita Satin, Buket Uang) dipisah menjadi produk terpisah.
- **Alasan:** tabel `variasi_produk` tidak dapat menampung matriks harga; memaksakannya akan membuat kode rapuh. Harga/nilai **tidak diubah**.
- **Disetujui:** pemisahan produk Pita Satin (Tanpa/Dengan Glitter) & Buket Uang (Biasa/Bentuk) (K-04).

### B-4. Keamanan (dipisah lingkungan)
- **Diubah:** §22 dibagi menjadi **wajib lokal XAMPP** dan **rekomendasi online**.
- **Alasan:** memenuhi ketentuan "terapkan keamanan lokal tanpa infrastruktur produksi" dan "bedakan wajib-lokal vs online". Tidak mewajibkan Docker, framework, atau layanan berbayar.
- **Catatan:** `Secure` cookie, HSTS, CSP, rate-limit, hide error → ONLINE-REKOMENDASI (butuh HTTPS).

### B-5. Diagram
- **Diubah:** Use Case & Activity/Sequence diformalkan ke Mermaid; **ditambahkan** Class, Component, Deployment Diagram.
- **Alasan:** memenuhi ketentuan §4 instruksi revisi (selaraskan semua diagram) dan menutup C-12, C-13, C-14.

### B-6. PWA
- **Diubah:** ditambahkan §24 (manifest + service worker + ikon).
- **Alasan:** diminta pada instruksi revisi. Tidak memakai framework/layanan berbayar.
- **Disetujui (K-01):** **PWA dasar** — manifest, service worker, cache aset statis, halaman fallback offline, kemampuan instalasi; keranjang & data dinamis tetap online.

### B-7. Rencana pengujian
- **Diubah:** melengkapi test case T-01…T-30 (termasuk keamanan & PWA).
- **Alasan:** dokumen sumber terpotong di T-02.

### B-8. Konvensi implementasi
- **Diubah:** menambahkan §26 (helper, struktur keranjang session, upload, 404, slug unik).
- **Alasan:** blueprint asli menyebut fungsi/berkas tanpa penjelasan implementasi yang memadai.

### B-9. Yang **tidak** diubah
- Nomor WhatsApp, seluruh harga, data produk, aksesori, ketentuan Pita Satin (estimasi ±7 hari, DP 50%, tidak dapat dibatalkan), tujuan, dan fitur utama. Stack tetap PHP native + MySQL/MariaDB + HTML/CSS/JS + XAMPP.

---

## C. Keputusan yang Telah Disetujui & Sisa Konfirmasi

### C.1 Keputusan yang telah disetujui (K-01…K-10)

| # | Keputusan | Ringkas | Terdampak pada |
|---|---|---|---|
| K-01 | PWA **dasar** | manifest, service worker, cache aset statis, halaman fallback offline, instalasi; keranjang & data dinamis tetap online | §3.1, §4.1 F-11, §6, §24, §25.4 |
| K-02 | Lengkapi **Class/Component/Deployment Diagram** | sesuai arsitektur PHP native + MySQL/MariaDB + XAMPP | §17–§19 |
| K-03 | Login admin **username + password** | tanpa email | §4.2, §13.1 |
| K-04 | Pisah **Pita Satin** (Tanpa/Dengan Glitter) & **Buket Uang** (Biasa/Bentuk) | harga & ketentuan dipertahankan | §11.2, §11.3 |
| K-05 | Teks tombol beranda default **"Lihat Katalog"** | dapat diubah di panel admin | §4.2 A-06, §13.8 |
| K-06 | Palet **Soft Beige, Muted Rose, Sage Green** | serasi Dusty Pink, kontras terjaga | §10.1 |
| K-07 | Upload gambar **maks 2 MB** | validasi sisi server | §22.1, §26.4 |
| K-08 | **Tanpa audit log** | di luar kebutuhan proyek | §12 |
| K-09 | Produk unggulan dari **`is_unggulan`** | tanpa mekanisme pemilihan terpisah | §4.2, §13.3 |
| K-10 | **Hosting online ditunda** | fokus demo lokal XAMPP, tanpa layanan berbayar | §1, §3.3, §27.2 |

### C.2 Sisa konfirmasi

**Tidak ada keputusan yang masih memblokir implementasi.** Butir berikut hanya opsional dan dapat diputuskan saat coding (tidak menghalangi demo lokal):

| # | Item opsional | Catatan |
|---|---|---|
| O-1 | Warna final tiap palet | Nilai pada §10.1 sudah cukup untuk implementasi; dapat disesuaikan belakangan |
| O-2 | Teks tombol & headline beranda | Default "Lihat Katalog" sudah tersedia; teks final milik pemilik |
| O-3 | Ikon & foto asli (logo, produk) | Placeholder dipakai sampai aset asli tersedia |
| O-4 | Publikasi online/hosting | Ditunda (K-10); bukan syarat demo |

---

## D. Verifikasi Akhir

- [x] `BLUEPRINT-REVISI.md` dibuat sebagai **dokumen baru**.
- [x] Blueprint asli (`60225086_Anisah Nurul Hidayah_Blueprint.pdf`) **tidak ditimpa/diubah**.
- [x] **Tidak ada** kode aplikasi yang dibuat.
- [x] **Tidak ada** inisialisasi proyek / `git init` / proses build dijalankan.
- [x] Tidak ada ketergantungan pada layanan berbayar, framework tambahan, atau Docker.
- [x] Harga, data produk, ketentuan, dan informasi toko dipertahankan.

**Tindak lanjut:** seluruh butir K-01…K-10 telah disetujui dan diterapkan pada `BLUEPRINT-REVISI.md`. Dokumen siap dijadikan dasar implementasi kode. Tidak ada keputusan penting yang tersisa.

---

## E. Perbaikan kecil (v2.2) — dari laporan validasi akhir

| # | Perbaikan | Lokasi |
|---|---|---|
| B-1 | Contoh pesan & contoh `variasi_produk` tidak lagi memperlakukan Glitter sebagai variasi (selaras K-04) | §23, §13.4 |
| B-2 | Opsi akses subfolder XAMPP kini mensyaratkan root `.htaccess` menolak `includes/` & `database/`, atau wajib vhost | §27.1 |
| B-3 | Ditambah test case validasi input T-31 (jumlah ≤ 0/non-angka) & T-32 (jenis_harga/slug) | §25.2 |
| B-4 | Catatan fallback font lokal + font stack untuk demo offline | §10.2 |
| C-1…C-3 | Keputusan pemilik dipakai: nama kurung `(Tanpa/Dengan Glitter)`, catatan pelanggan tetap disertakan, font dilayani lokal dengan fallback | §11.3, §23, §10.2 |
