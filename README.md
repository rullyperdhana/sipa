# SIPA - Sistem Informasi Pengelolaan Aset
### Pemerintah Kabupaten Tapin &bull; BPKAD

[![Version](https://img.shields.io/badge/version-2.7.0-blue.svg)](application/config/config.php)
[![PHP](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3%20%7C%208.4-777BB4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![Framework](https://img.shields.io/badge/Framework-CodeIgniter%203-EF4444.svg?logo=codeigniter&logoColor=white)](https://codeigniter.com/)
[![Database](https://img.shields.io/badge/Database-MySQL%208.0%20%7C%20MariaDB-4479A1.svg?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Frontend-Bootstrap%205.3-7952B3.svg?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-Proprietary%20%2F%20BPKAD%20Tapin-green.svg)](#)

**SIPA** (Sistem Informasi Pengelolaan Aset) adalah platform web terpadu milik **Badan Pengelolaan Keuangan dan Aset Daerah (BPKAD) Pemerintah Kabupaten Tapin** yang dirancang untuk mengelola siklus perencanaan aset daerah (RKBMD), standarisasi harga belanja fisik (SSH), standarisasi biaya operasional dan tarif jasa (SBU), penjadwalan periode pengusulan standar harga, katalog master TA 2027, serta integrasi referensi rekening belanja daerah sesuai ketentuan **SIPD RI (Permendagri No. 90 Tahun 2019 dan Kepmendagri No. 050-5888)**.

---

## 📑 Daftar Isi

- [Fitur & Modul Utama](#-fitur--modul-utama)
- [Matriks Hak Akses Pengguna (RBAC & RLS)](#-matriks-hak-akses-pengguna-rbac--rls)
- [Arsitektur & Struktur Direktori](#-arsitektur--struktur-direktori)
- [Kebutuhan Sistem (System Requirements)](#-kebutuhan-sistem-system-requirements)
- [Panduan Instalasi Lokal (Localhost)](#-panduan-instalasi-lokal-localhost)
- [Panduan Deployment ke Server VPS (Production)](#-panduan-deployment-ke-server-vps-production)
- [Daftar Migrasi Database](#-daftar-migrasi-database)
- [Endpoint API AJAX](#-endpoint-api-ajax)
- [Riwayat Versi (Changelog)](#-riwayat-versi-changelog)
- [Kontak & Pengembang](#-kontak--pengembang)

---

## 🌟 Fitur & Modul Utama

### 1. Modul RKBMD (Rencana Kebutuhan Barang Milik Daerah)
Menangani 5 instrumen perencanaan aset daerah sesuai Permendagri No. 19 Tahun 2016:
- **Pengadaan:** Usulan pengadaan barang baru SKPD berdasarkan analisis kebutuhan riil dan data BMD eksisting.
- **Pemeliharaan:** Usulan pemeliharaan berkala dan perbaikan aset daerah.
- **Pemanfaatan:** Perencanaan sewa, pinjam pakai, KSP, BGS/BSG.
- **Pemindahtanganan:** Perencanaan penjualan, tukar-menukar, atau hibah aset.
- **Penghapusan:** Usulan penghapusan BMD yang rusak berat atau kedaluwarsa secara hukum.

### 2. Modul Standar Satuan Harga (SSH)
Standarisasi harga satuan barang fisik dan material baru:
- **Formulir Khusus SSH:** Input barang fisik, perlengkapan kantor, material bangunan, kendaraan dinas, alat kesehatan.
- **Alur 5 Tahapan:** `Draft` &rarr; `Diajukan` &rarr; `Direvisi` &rarr; `Diverifikasi` &rarr; `Ditetapkan`.
- **Row-Level Security (RLS):** SKPD hanya dapat melihat dan mengelola usulannya sendiri saat berstatus `Draft` atau `Direvisi`. Data terkunci otomatis saat berstatus `Diajukan`.
- **Verifikasi BPKAD:** Tim verifikator dapat menyetujui, memberi catatan revisi, atau menolak usulan.
- **Penetapan Harga:** Pimpinan/Penetap menetapkan harga final dan menerbitkan surat keputusan standar satuan harga.

### 3. Modul Standar Biaya Umum (SBU)
Standarisasi pos pengeluaran non-fisik dan tarif operasional:
- **Formulir Khusus SBU:** Standarisasi honorarium narasumber, tim pelaksana, konsultan/tenaga ahli, uang harian perjadin, sewa gedung/kendaraan, tarif jasa.
- **Satuan Berbasis Kegiatan/Waktu:** Mendukung satuan OB (Orang/Bulan), OH (Orang/Hari), OJ (Orang/Jam), OK (Orang/Kegiatan), Paket, dsb.
- **Alur Independen:** Memiliki submenu usulan, verifikasi, dan penetapan terpisah dari modul SSH.

### 4. Master Katalog Standar Harga TA 2027 (Baseline Usulan Tahun Mendatang)
- **11.519 Data Resmi Terintegrasi:** Memuat **5.786 item SSH** dan **5.733 item SBU** dari SIPD RI Kabupaten Tapin.
- **Katalog Rinci:** Setiap item memiliki kode standar, nama barang/jasa, spesifikasi teknis mendalam, satuan, kelompok akun belanja, dan harga acuan dasar 2027.
- **Server-Side Pagination:** Menampilkan ribuan item dengan latensi sangat rendah (**< 30ms**) dan konsumsi RAM efisien.
- **Pencarian Cepat & Filter:** Fulltext search dan filter kategori multi-kriteria.
- **Tombol Cepat "Usulkan":** SKPD dapat langsung mengajukan usulan penyesuaian dari baris katalog master.

### 5. Modul Penjadwalan Pengusulan Standar Harga (Jadwal BPKAD)
- **Kontrol Periode Terpusat:** BPKAD dapat membuka dan menutup jadwal pengusulan SSH & SBU per tahun anggaran (`/ssh/jadwal` dan `/sbu/jadwal`).
- **Mekanisme Penguncian Usulan:** Jika jadwal belum dibuka atau telah berakhir, formulir dan tombol tambah usulan otomatis terkunci (*disabled*) dengan keterangan *"Menunggu Pembuatan Jadwal oleh BPKAD"*.
- **Banner Status Dinamis:** Menampilkan status jadwal aktif (Buka/Tutup), batas tanggal mulai-selesai, dan catatan BPKAD pada halaman usulan SKPD.
- **One-Click Status Toggle:** Kemudahan verifikator/admin mengubah status aktifitas jadwal secara instan.

### 6. Interkoneksi Data Komprehensif & Otomatisasi Cerdas
- **Dual-Mode Pengusulan:** SKPD dapat memilih usulan dari **Katalog Master TA 2027** atau mengajukan **Item Standar Baru** secara manual.
- **Pencarian Cerdas Rekening Belanja SIPD RI Berdasarkan Nama:** Pengguna cukup mengetik nama kebutuhan belanja (contoh: *"Alat Tulis"*, *"Kertas"*, *"Honorarium"*, *"Perjalanan Dinas"*, *"Pemeliharaan"*), dan sistem akan secara otomatis memunculkan **Kode Rekening Belanja resmi** beserta nama akunnya dalam bentuk kartu badge visual interaktif.
- **Penyelarasan Kategori Barang & Jasa:** Dropdown kategori otomatis memuat klasifikasi aset BMD resmi (*Bahan & Persediaan Habis Pakai*, *Peralatan dan Mesin*, *Gedung dan Bangunan*, *Jalan, Irigasi dan Jaringan*, *Aset Tetap Lainnya*, *Tanah*, dll.) dan langsung ter-pilih otomatis (*auto-selected*) saat memilih item dari katalog master 2027.
- **Otomatisasi Input Form:** Memilih item master otomatis mengisi spesifikasi teknis, satuan, kategori, akun belanja SIPD RI, serta menampilkan harga dasar 2027 sebagai acuan.
- **Komparasi Harga Otomatis:** Perhitungan selisih dan persentase perubahan harga antara usulan SKPD terhadap harga acuan master secara real-time.
- **Sinkronisasi Otomatis ke Master:** Saat usulan disetujui dan ditetapkan oleh BPKAD, sistem otomatis memperbarui atau menambahkan item baru ke katalog `ref_standar_harga` untuk tahun anggaran berikutnya.

### 7. Modul Master Referensi Akun Belanja (SIPD RI)
Referensi kodefikasi rekening belanja terintegrasi SIPD RI:
- **Data Lengkap:** Memuat **9.617 akun belanja** (`5.x`) dan **30.669 seluruh akun** SIPD RI (`4.x`, `5.x`, `6.x`).
- **Klasifikasi Permendagri 90:** Dikelompokkan ke dalam Belanja Operasi (`5.1`), Belanja Modal (`5.2`), Belanja Tidak Terduga (`5.3`), dan Belanja Transfer (`5.4`).
- **Hirarki 6 Level:** Mulai dari Level 1 (Akun) hingga Level 6 (Sub Rincian Objek / Leaf).
- **Indikator Kesiapan Usulan:** Badge khusus akun yang berstatus *Siap Dianggarkan* (Sub Rincian Objek).
- **Pencarian Cepat:** Dilengkapi B-Tree Index dan Fulltext Search.

### 8. Modul Master Data Barang BMD (Kinerja Tinggi)
Katalog standarisasi kodefikasi barang daerah Kabupaten Tapin:
- **Kapasitas Besar:** Mengelola **13.367 barang terdaftar**.
- **Server-Side Pagination:** Menggunakan `LIMIT` dan `OFFSET` presisi sehingga respon halaman selalu instan (**< 50ms**) dan konsumsi RAM server **< 1 MB** (menghemat 99% memori dibandingkan client-side rendering).
- **Kartu Statistik KPI:** Menampilkan ringkasan jumlah Peralatan & Mesin, Gedung & Bangunan, Tanah, dan Barang Aktif.
- **Filter Multi-Kriteria:** Pencarian teks/kode barang, filter kategori, filter ketersediaan harga standar, dan status aktif.
- **Validasi Anti-Duplikasi:** Pengecekan otomatis di backend untuk mencegah database error 1062 saat menambah atau mengedit kode barang.
- **Import Excel:** Unggah data massal dari file `.xlsx` / `.xls`.

### 9. Antarmuka UI/UX Responsif & Modern
- **Responsive Sidebar Toggle:**
  - **Layar Desktop ($\ge 992\text{px}$):** Tombol hamburger melipat sidebar (*sidebar collapse*) dan memperluas canvas konten ke lebar penuh (*full-width*). Status tersimpan di `localStorage` (anti-FOUC).
  - **Layar Mobile ($< 992\text{px}$):** Sidebar berfungsi sebagai drawer samping (*off-canvas*) dengan latar belakang redup (*backdrop blur*), tombol tutup `X`, dan dukungan tombol `Escape`.
- **Auto DataTables Column Adjust:** Penyesuaian lebar kolom tabel otomatis saat sidebar dilipat/dibuka.

### 10. Pusat Laporan & Dashboard Eksekutif SIPA (`/laporan`)
- **4 Kartu KPI Eksekutif:** Ringkasan makro Total Usulan Terdata (RKBMD + Standar Harga), Total Pagu Diusulkan (Rp), Realisasi Nilai Disetujui/Ditetapkan (Rp) dengan indikator persentase *Approval Rate*, dan Tingkat Partisipasi SKPD aktif.
- **Visualisasi Interaktif (Chart.js):**
  - *Distribusi Pagu Anggaran RKBMD:* Diagram batang alokasi dana per instrumen perencanaan.
  - *Proporsi Status Seluruh Usulan:* Diagram lingkaran persentase usulan Disetujui/Ditetapkan, Menunggu Verifikasi, Draft, dan Direvisi/Ditolak.
- **Navigasi Multi-Tab Terpadu:**
  - **Tab 1: Rekapitulasi RKBMD:** 5 kartu instrumen seimbang (Pengadaan, Pemeliharaan, Pemanfaatan, Pemindahtanganan, Penghapusan) dan tabel usulan interaktif.
  - **Tab 2: Rekapitulasi Standar Harga (SSH & SBU):** Kartu komparasi SSH vs SBU dan tabel usulan standar harga lengkap dengan tombol unduh dokumen survey pasar.
  - **Tab 3: Matriks Kepatuhan SKPD:** Monitoring partisipasi dan kepatuhan 66 SKPD se-Kabupaten Tapin dengan status (*Lengkap*, *Sebagian*, *Belum Ada Usulan*).
- **Ekspor & Cetak Laporan Resmi:**
  - Ekspor Spreadsheet Excel/CSV per modul (`/laporan/export/{modul}/excel`).
  - Lembar cetak laporan resmi ber-kop Pemerintah Kabupaten Tapin & BPKAD (`/laporan/cetak`) siap ditandatangani Kepala BPKAD.

### 11. Modul Integrasi WhatsApp & Notifikasi Cepat Operator (`/wa`)
- **Pemberitahuan 1-Klik:** Tombol aksi langsung WhatsApp pada lembar verifikasi RKBMD (`/verifikasi/detail`) dan verifikasi Standar Harga (`/ssh/verifikasi` & `/sbu/verifikasi`).
- **Format Pesan Kedinasan Otomatis (Markdown WA):** Otomatis menyusun kop instansi resmi BPKAD Kab. Tapin, nama operator/SKPD, nomor usulan, status usulan (⚠️ *Perlu Perbaikan*, ✅ *Selesai / Disetujui*, ❌ *Ditolak*), catatan verifikator, serta tautan langsung untuk perbaikan data.
- **Pencarian Nomor Kontak Cerdas:** Mengambil nomor WhatsApp akun pengguna operator SKPD secara otomatis (`users.no_wa`) dengan fallback ke nomor kontak dinas SKPD (`skpd.telepon`).
- **Modal Interaktif Notifikasi WhatsApp Global (`templates/wa_modal`):** Memungkinkan verifikator memeriksa nomor tujuan, mengedit isi pesan leluasa sebelum dikirim, menyalin teks, atau langsung membuka WhatsApp Web / Desktop / HP.
- **Dua Mode Integrasi:** Mode *Direct Click-to-Chat* (100% gratis tanpa biaya gateway pihak ketiga) dan mode *WhatsApp Gateway API* (opsional latar belakang via Fonnte / Webhook).

### 12. Modul Pendaftaran Mandiri & Persetujuan Akun Operator (`/register`)
- **Kendali Penuh Administrator (Buka/Tutup Pendaftaran):** Admin BPKAD dapat mengaktifkan atau menonaktifkan registrasi mandiri publik sewaktu-waktu melalui modal pengaturan di `/master/user`.
- **Alur Persetujuan Bertingkat (Admin Approval Workflow):** Pendaftar mandiri otomatis berstatus *Nonaktif / Menunggu Verifikasi* (`is_active = 0`) agar keamanan data aset daerah tetap terjaga dari pihak yang tidak berwenang.
- **Aktivasi Cepat 1-Klik:** Tombol *Setujui & Aktifkan* langsung pada tabel pengguna admin untuk mengesahkan akun pemohon.
- **Notifikasi Sambutan Akun Aktif via WA:** Tombol WhatsApp khusus pada baris pengguna untuk mengirimkan konfirmasi aktivasi akun ke pemohon secara instan.
- **Formulir Pendaftaran Lengkap:** Menyediakan input Nama Lengkap, NIP, Username, Email, Nomor WhatsApp aktif, Pilihan SKPD, Password kuat (minimal 8 karakter), serta verifikasi Anti-Bot matematika dinamis.
- **Halaman Penanganan Adaptif:** Menampilkan pemberitahuan kedinasan yang ramah saat pendaftaran sedang ditutup oleh administrator.

### 13. Arsitektur Keamanan & Proteksi Sistem Online (Security Hardening)
- **Auto-Environment Detection (`index.php`):** Otomatis beralih ke `ENVIRONMENT = 'production'` pada domain online untuk menonaktifkan tampilan error trace PHP dan mencegah kebocoran informasi sistem (*Information Disclosure*).
- **Proteksi Brute-Force Berbasis IP & Akun (`Auth.php`):** Pemblokiran IP otomatis jika terdeteksi $\ge 10$ kegagalan login dalam kurun waktu 15 menit.
- **Mitigasi Timing Attack (`Auth.php`):** Menjalankan kalkulasi dummy bcrypt hash saat username tidak ditemukan untuk meratakan durasi respon server.
- **Tantangan Anti-Bot Captcha Dinamis pada Login (`Login.php`):** Otomatis memunculkan verifikasi matematika jika terjadi kegagalan login $\ge 3$ kali berturut-turut.
- **Header Keamanan HTTP Lengkap (`MY_Controller`):** Seluruh controller (termasuk Login & Register) diproteksi header `X-Frame-Options: SAMEORIGIN` (anti-clickjacking), `X-Content-Type-Options: nosniff`, `X-XSS-Protection: 1; mode=block`, `Referrer-Policy: strict-origin-when-cross-origin`, dan `Strict-Transport-Security` (HSTS).
- **Pengamanan Direktori Uploads (`uploads/.htaccess`):** Melarang eksekusi file script apapun (`.php`, `.phtml`, `.cgi`, `.sh`, `.exe`, dll.) di folder penyimpanan berkas unggahan dan menonaktifkan directory browsing.
- **Hardening Root Web Server (`.htaccess`):** Menonaktifkan *Directory Listing* (`Options -Indexes`) dan memblokir akses langsung ke file sensitif (`.env`, `.sql`, `.json`, `.lock`, `.log`, `database.local.php`).
- **Autentikasi Remember-Me Aman:** Verifikasi token hash Bcrypt dengan auto-rotasi token acak, `HttpOnly`, dan atribut cookie `SameSite=Lax`.

---

## 👥 Matriks Hak Akses Pengguna (RBAC & RLS)

| Role | RKBMD | Usulan SSH/SBU | Jadwal Pengusulan | Verifikasi SSH/SBU | Penetapan SSH/SBU | Master Data | Laporan |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **admin** | Full | Full (Semua SKPD) | Kelola Penuh | Ya | Ya | Full (Katalog, Barang, Akun, User) | Full |
| **skpd** / **operator_skpd** | Usulan SKPD | Usulan (Jika Jadwal Buka) | Lihat Status | Lihat Status | Lihat Status | Lihat Katalog & Akun | Laporan SKPD |
| **verifikator** | Lihat | Lihat | Kelola Penuh | Proses Verifikasi | Lihat | Lihat Katalog & Akun | Rekap Verifikasi |
| **penetap** | Lihat | Lihat | Lihat | Lihat | Proses Penetapan SK | Lihat Katalog & Akun | Rekap Penetapan |
| **pimpinan** | Monitoring | Monitoring | Monitoring | Monitoring | Pengesahan Akhir | Monitoring | Rekap Eksekutif |

---

## 🏗️ Arsitektur & Struktur Direktori

```text
sipa/
├── application/
│   ├── config/
│   │   ├── config.php               # Konfigurasi nama aplikasi, versi (v2.7.0), session, CSRF
│   │   ├── database.php             # Konfigurasi database default / production
│   │   ├── database.local.php       # Override koneksi database lokal (di-ignore oleh git)
│   │   └── routes.php               # Konfigurasi routing URL modular
│   ├── controllers/
│   │   ├── Ajax.php                 # Endpoint AJAX pencarian Select2 & notifikasi
│   │   ├── Dashboard.php            # Dashboard statistik dan grafik
│   │   ├── Laporan.php              # Pusat laporan, rekap, cetak berita acara & ekspor Excel
│   │   ├── Login.php                # Autentikasi pengguna, anti-bot captcha, auto remember-me
│   │   ├── Master.php               # CRUD SKPD, Barang BMD, Akun Belanja, Periode, User & RBAC
│   │   ├── Register.php             # Pendaftaran mandiri operator, captcha, kontrol approval
│   │   ├── Rkbmd.php                # Controller induk 5 modul perencanaan RKBMD
│   │   ├── Ssh.php                  # Controller modul Standar Satuan Harga fisik & jadwal
│   │   ├── Sbu.php                  # Controller modul Standar Biaya Umum non-fisik & jadwal
│   │   ├── Verifikasi.php           # Modul verifikasi RKBMD BPKAD
│   │   └── Wa.php                   # Controller integrasi WhatsApp & pengaturan gateway
│   ├── core/
│   │   └── MY_Controller.php        # Base controller dengan HTTP Security Headers & HSTS
│   ├── libraries/
│   │   ├── Auth.php                 # Library autentikasi, brute-force IP rate limit, RBAC
│   │   ├── Whatsapp.php             # Library WhatsApp URL generator & Gateway integration
│   │   ├── Ssh_service.php          # Layanan bisnis modul standar satuan harga
│   │   └── Logger.php               # Pencatatan audit trail aktivitas sistem
│   ├── models/
│   │   ├── Akun_model.php           # Model referensi akun belanja SIPD RI
│   │   ├── Master_model.php         # Model master data barang, SKPD, dan periode
│   │   ├── Ssh_model.php            # Model usulan, log audit, verifikasi, dan penetapan
│   │   └── User_model.php           # Model pengguna, RBAC permissions, kontak WA operator
│   └── views/
│       ├── auth/                    # Halaman masuk (login.php) dan registrasi (register.php)
│       ├── master/                  # Tampilan master barang, SKPD, user, WA settings
│       ├── ssh/                     # Tampilan modul SSH (usulan, verifikasi, penetapan, jadwal)
│       ├── sbu/                     # Tampilan modul SBU (usulan, verifikasi, penetapan, jadwal)
│       ├── verifikasi/              # Tampilan verifikasi usulan RKBMD
│       └── templates/
│           ├── header.php           # Navbar, brand, notifikasi, sidebar responsif
│           ├── footer.php           # Skrip JS global, CSRF token, library
│           └── wa_modal.php         # Modal dialog pengiriman pesan WhatsApp global
├── assets/
│   ├── css/
│   │   └── app.css                  # Custom CSS styling, transisi drawer, desktop collapse
│   └── js/
│       ├── app.js                   # Logika toggle hamburger, backdrop, notifikasi polling
│       └── ssh_module.js            # Format rupiah, validasi form usulan, integrasi tombol WA
├── database/
│   └── migrations/                  # Skrip SQL migrasi database modular
├── uploads/
│   └── .htaccess                    # Proteksi larangan eksekusi file script di folder upload
├── .htaccess                        # Hardening root web server, anti directory listing
├── CHANGELOG.md                     # Catatan riwayat rilis
└── README.md                        # Dokumentasi sistem ini
```

---

## 💻 Kebutuhan Sistem (System Requirements)

- **Web Server:** Nginx 1.20+ atau Apache 2.4+ (dengan modul `mod_rewrite` aktif)
- **PHP Version:** PHP 8.0, 8.1, 8.2, 8.3, atau 8.4
- **Ekstensi PHP Wajib:** `mysqli`, `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `zip`, `json`, `curl`
- **Database Server:** MySQL 8.0+ atau MariaDB 10.4+
- **Memori PHP:** `memory_limit` minimal 128 MB (disarankan 256 MB)
- **Upload File:** `upload_max_filesize` minimal 10 MB, `post_max_size` 12 MB

---

## 🔧 Panduan Instalasi Lokal (Localhost)

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/rullyperdhana/sipa.git
   cd sipa
   ```

2. **Konfigurasi Database Lokal:**
   Buat file `application/config/database.local.php` (file ini otomatis diabaikan oleh `.gitignore` sehingga tidak akan menimpa server VPS):
   ```php
   <?php
   defined('BASEPATH') OR exit('No direct script access allowed');

   $db['default']['hostname'] = '127.0.0.1';
   $db['default']['username'] = 'root';
   $db['default']['password'] = 'root'; // Sesuaikan password lokal
   $db['default']['database'] = 'sipa_db';
   $db['default']['port']     = 8889;   // Sesuaikan port (MAMP: 8889, XAMPP/MySQL default: 3306)
   ```

3. **Import Database & Migrasi:**
   Import skema dasar dan seluruh migrasi ke database lokal:
   ```bash
   mysql -u root -p sipa_db < database/migrations/create_ssh_sbu_module.sql
   mysql -u root -p sipa_db < database/migrations/create_ref_akun_belanja.sql
   mysql -u root -p sipa_db < database/migrations/optimize_barang_table.sql
   mysql -u root -p sipa_db < database/migrations/fix_duplicate_barang.sql
   ```

4. **Jalankan Web Server Lokal:**
   ```bash
   php -S 0.0.0.0:8000
   ```
   Buka di browser: `http://localhost:8000`

5. **Kredensial Default:**
   - **Username:** `admin`
   - **Password:** `password`

---

## 🚀 Panduan Deployment ke Server VPS (Production)

1. **Login ke Terminal VPS:**
   ```bash
   ssh root@ip-vps-anda
   cd /www/wwwroot/sipa.bkadtapinkab.online
   ```

2. **Tarik Pembaruan dari GitHub:**
   ```bash
   git pull origin main
   ```
   > [!TIP]
   > Jika terdapat pesan *error: Your local changes to the following files would be overwritten by merge*, jalankan perintah pembersihan perubahan lokal terlebih dahulu:
   > ```bash
   > git stash && git pull origin main
   > ```
   > *(atau bersihkan file spesifik: `git checkout -- application/controllers/Sbu.php application/controllers/Ssh.php application/models/Ssh_model.php && git pull origin main`)*

3. **Pastikan Izin Akses Folder (*File Permissions*):**
   ```bash
   chown -R www:www /www/wwwroot/sipa.bkadtapinkab.online
   chmod -R 755 /www/wwwroot/sipa.bkadtapinkab.online
   chmod -R 777 uploads/
   ```

4. **Jalankan Migrasi Database di VPS (Termasuk Master 2027, Jadwal & 3 Bukti Survey):**
   ```bash
   # Migrasi v2.0.0 (Jika belum dijalankan)
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/create_ssh_sbu_module.sql
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/create_ref_akun_belanja.sql
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/optimize_barang_table.sql
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/fix_duplicate_barang.sql

   # Migrasi v2.1.0 (Master Standar Harga SSH & SBU TA 2027 dan Jadwal Pengusulan)
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/create_master_ssh_sbu_and_jadwal.sql
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/import_master_ssh_sbu_2027.sql

   # Migrasi v2.2.0 (Mandatori 3 Berkas Bukti Survey Pasar / Brosur Resmi)
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/add_bukti_survey_3_files.sql

   # Migrasi v2.5.0 (Pengaturan Hak Akses Menu & Modul Granular Per-User)
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/add_menu_permissions_to_users.sql

   # Migrasi v2.6.0 (Fitur Notifikasi & Pemberitahuan WhatsApp ke Operator SKPD)
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/add_wa_notification_features.sql

   # Migrasi v2.7.0 (Pengaturan Pendaftaran Mandiri Pengguna & Security Hardening)
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/add_user_registration_settings.sql

   # Migrasi v2.8.0 (Manajemen Nomenklatur SKPD Per Tahun & Konteks Tahun Anggaran Global)
   mysql -u sql_sipa_bkadtapinkab_online -p sql_sipa_bkadtapinkab_online < database/migrations/add_global_fiscal_year_and_skpd_nomenklatur.sql
   ```
   *(Masukkan password database VPS saat diminta).*

---

## 🗃️ Daftar Migrasi Database

| File Migrasi | Deskripsi & Tujuan |
| :--- | :--- |
| [`add_global_fiscal_year_and_skpd_nomenklatur.sql`](database/migrations/add_global_fiscal_year_and_skpd_nomenklatur.sql) | Menambahkan tabel `skpd_nomenklatur` untuk mencatat riwayat nama SKPD, kode unit, dan Kepala SKPD per Tahun Anggaran agar dokumen cetak lampau tetap otentik. |
| [`add_user_registration_settings.sql`](database/migrations/add_user_registration_settings.sql) | Menambahkan konfigurasi default pendaftaran mandiri pengguna (`registration_enabled`, `registration_require_approval`, `registration_default_role`) pada tabel `ex_settings`. |
| [`add_wa_notification_features.sql`](database/migrations/add_wa_notification_features.sql) | Menambahkan kolom `no_wa` pada tabel `users`, tabel konfigurasi `ex_settings`, dan nilai bawaan notifikasi WhatsApp SIPA. |
| [`add_menu_permissions_to_users.sql`](database/migrations/add_menu_permissions_to_users.sql) | Menambahkan kolom `menu_permissions TEXT NULL` pada tabel `users` untuk mendukung konfigurasi hak akses modul terperinci per akun pengguna. |
| [`add_bukti_survey_3_files.sql`](database/migrations/add_bukti_survey_3_files.sql) | Menambahkan kolom `file_lampiran_2`, `file_nama_asli_2`, `file_lampiran_3`, `file_nama_asli_3` pada `standar_harga_usulan` untuk mandatori 3 berkas survey pasar / brosur resmi. |
| [`create_master_ssh_sbu_and_jadwal.sql`](database/migrations/create_master_ssh_sbu_and_jadwal.sql) | Membuat tabel `ref_standar_harga` (Fulltext & B-Tree index), tabel `standar_harga_jadwal`, seed jadwal awal, dan kolom relasi master pada `standar_harga_usulan`. |
| [`import_master_ssh_sbu_2027.sql`](database/migrations/import_master_ssh_sbu_2027.sql) | Impor 11.519 data resmi standar harga TA 2027 (5.786 item SSH + 5.733 item SBU) dari file SIPD RI Kab. Tapin. |
| [`create_ssh_sbu_module.sql`](database/migrations/create_ssh_sbu_module.sql) | Menyesuaikan enum role user, membuat tabel `standar_harga_usulan`, tabel audit log `standar_harga_log`, foreign keys, dan trigger status. |
| [`create_ref_akun_belanja.sql`](database/migrations/create_ref_akun_belanja.sql) | Membuat tabel `ref_akun_belanja` (9.617 akun belanja), view `akun_belanja`, dan tabel lengkap `ref_akun` (30.669 akun) dari master SIPD RI. |
| [`optimize_barang_table.sql`](database/migrations/optimize_barang_table.sql) | Skrip idempotent untuk menambahkan index pencarian `idx_nama_barang` dan `idx_is_active` pada tabel barang. |
| [`fix_duplicate_barang.sql`](database/migrations/fix_duplicate_barang.sql) | Memperbaiki whitespace kode barang dan mengalihkan relasi duplikat item Pipet Tetes secara aman. |

---

## 🔌 Endpoint API AJAX

Aplikasi menyediakan endpoint JSON terproteksi sesi untuk integrasi Select2 dan autocomplete form:

- **Pencarian Master Standar Harga (SSH & SBU TA 2027):**
  ```http
  GET /ajax/standar_harga/search?q={keyword}&tipe={ssh|sbu}&tahun={2027}
  ```
  *Response:* `{"results": [{"id": 1, "text": "Kertas HVS A4 70gr", "kode": "1.1.1...", "nama": "Kertas HVS", "spesifikasi": "A4 70gr", "satuan": "Rim", "harga": 55000, "kode_rekening": "5.1.02...", "nama_rekening": "Belanja ATK"}]}`

- **Detail Master Standar Harga:**
  ```http
  GET /ajax/standar_harga/detail/{id}
  ```

- **Pencarian Barang BMD:**
  ```http
  GET /ajax/barang/search?q={keyword}
  ```
  *Response:* `{"results": [{"id": 1, "text": "1.3.2... - Laptop", "kode": "1.3.2...", "nama": "Laptop", "satuan": "Unit", "harga": 0}]}`

- **Pencarian Cerdas Akun Belanja SIPD RI (Berdasarkan Nama atau Kode):**
  ```http
  GET /ajax/akun_belanja/search?q={keyword}&all={0|1}
  ```
  Mendukung pencarian multi-kata berdasarkan **nama akun belanja** (contoh: *"alat tulis"*, *"honorarium narasumber"*, *"kertas hvs"*, *"makanan minuman"*) maupun nomor **kode akun** (*"5.1.02..."*).
  *Response:*
  ```json
  {
    "results": [
      {
        "id": "5.1.02.01.001.00025",
        "text": "5.1.02.01.001.00025 - Belanja Alat/Bahan untuk Kegiatan Kantor- Kertas dan Cover",
        "kode": "5.1.02.01.001.00025",
        "nama": "Belanja Alat/Bahan untuk Kegiatan Kantor- Kertas dan Cover",
        "kelompok": "Belanja Operasi",
        "level": 6
      }
    ]
  }
  ```

- **Daftar Notifikasi:**
  ```http
  GET /ajax/notif/list
  ```

- **Tandai Notifikasi Terbaca:**
  ```http
  POST /ajax/notif/read/{id}
  ```

- **Persiapan Draf & Kontak Notifikasi WhatsApp:**
  ```http
  POST /wa/ajax_prepare
  ```
  *Payload:* `{"context": "rkbmd|ssh", "id": 1, "status": "revisi|disetujui|ditolak", "catatan": "..."}`
  *Response:* `{"success": true, "data": {"phone": "62812...", "recipient_name": "...", "message": "...", "whatsapp_url": "https://api.whatsapp.com/send?..."}}`

- **Kirim Notifikasi via WhatsApp Gateway API:**
  ```http
  POST /wa/ajax_send_gateway
  ```
  *Payload:* `{"phone": "62812...", "message": "..."}`
  *Response:* `{"success": true, "message": "Pesan berhasil dikirim via WhatsApp Gateway"}`

---

## 📜 Riwayat Versi (Changelog)

Lihat rincian lengkap riwayat pembaruan sistem di file [CHANGELOG.md](CHANGELOG.md).

- **v2.7.0 (2026-10-10):** Fitur & Pengaturan Pendaftaran Mandiri Operator SKPD (`/register`), Kontrol Buka/Tutup Registrasi di Admin (`master/user`), Alur Persetujuan Verifikasi Admin (Admin Approval Workflow), Verifikasi Anti-Bot Captcha, Notifikasi Aktivasi Akun via WhatsApp, Auto-Environment Detection Production (`index.php`), Rate Limiting IP Brute-Force Protection (`Auth.php`), Anti-Bot Login (`Login.php`), HTTP Security Headers Lengkap (`MY_Controller`), dan Hardening Folder Berkas (`uploads/.htaccess`).
- **v2.6.0 (2026-10-10):** Fitur Pemberitahuan & Notifikasi WhatsApp ke Operator SKPD (`/wa`), Integrasi 1-Klik pada Verifikasi RKBMD & Standar Harga (SSH & SBU), Draf Pesan Otomatis Kedinasan (Revisi, Disetujui, Ditolak, Ditetapkan), Modal WhatsApp Global, dan Pengaturan WA Gateway.
- **v2.5.0 (2026-10-10):** Pengaturan Hak Akses Menu Granular Per-User (Granular RBAC) via Admin Master User (`master/user`), Checklist Izin Modul & Presets Cepat (User A: SSH/SBU/Laporan, User B: RKBMD/Laporan), Proteksi Controller Level HTTP 403, Sidebar & Quick Action Adaptif.
- **v2.4.0 (2026-10-10):** Perombakan Total Dashboard Utama Terpadu SIPA (`/dashboard`) - Live Banner Jadwal, Quick Action Bar, 4 KPI Metrics, 2 Chart Interaktif (Chart.js), Tabulasi Usulan Terbaru & Monitoring SKPD.
- **v2.3.0 (2026-10-10):** Pusat Laporan & Dashboard Eksekutif SIPA (`/laporan`), Cetak Rekapitulasi Berita Acara & Export Excel Terpadu.
- **v2.2.0 (2026-10-10):** Mandatori 3 Berkas Unggah Bukti Survey Pasar / Brosur Resmi pada Usulan SSH & SBU.
- **v2.1.0 (2026-10-10):** Integrasi 11.519 Master Standar Harga TA 2027 (5.786 SSH + 5.733 SBU), Modul Penjadwalan Pengusulan (`standar_harga_jadwal`) dengan mekanisme "Menunggu Jadwal", Interkoneksi Form & Autocomplete Rekening Belanja SIPD RI, Sinkronisasi Otomatis Penetapan ke Katalog Master.
- **v2.0.0 (2026-10-10):** Rilis Mayor Pemisahan Modul SSH & SBU, Master Referensi Akun Belanja SIPD RI, Optimasi Server-Side Paging Barang BMD, Responsive Sidebar Drawer & Collapse, Idempotent Database Migrations.
- **v1.0.0 (2026-08-18):** Rilis Perdana Modul Perencanaan RKBMD (Pengadaan, Pemeliharaan, Pemanfaatan, Pemindahtanganan, Penghapusan), Master SKPD, Verifikasi BPKAD.

---

## 🏛️ Kontak & Pengembang

- **Instansi:** Badan Pengelolaan Keuangan dan Aset Daerah (BPKAD)
- **Pemerintah:** Pemerintah Kabupaten Tapin, Provinsi Kalimantan Selatan
- **Situs Resmi:** [https://sipa.bkadtapinkab.online](https://sipa.bkadtapinkab.online)
