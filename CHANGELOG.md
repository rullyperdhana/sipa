# Changelog
Semua perubahan penting pada proyek **SIPA (Sistem Informasi Pengelolaan Aset) - Kabupaten Tapin** didokumentasikan dalam file ini.

Format changelog ini mengacu pada prinsip [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mematuhi [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] - 2026-10-10

### 🚀 Ditambahkan (Added)
- **Modul Standar Satuan Harga (SSH):**
  - Pemisahan modul khusus barang fisik dan material baru dengan rute mandiri (`/ssh/usulan`, `/ssh/verifikasi`, `/ssh/penetapan`, `/ssh/master_data`).
  - Formulir input terstandarisasi untuk barang fisik, spesifikasi teknis, survei pasar, dan berkas bukti dukung.
  - Alur persetujuan 5 status: `Draft` &rarr; `Diajukan` &rarr; `Direvisi` &rarr; `Diverifikasi` &rarr; `Ditetapkan`.
  - Riwayat audit log aktivitas status pada tabel `standar_harga_log`.
- **Modul Standar Biaya Umum (SBU):**
  - Pemisahan modul khusus belanja non-fisik, honorarium tim/narasumber, konsultan perorangan, uang harian perjalanan dinas, sewa gedung, dan tarif jasa (`/sbu/usulan`, `/sbu/verifikasi`, `/sbu/penetapan`, `/sbu/master_data`).
  - Dukungan satuan berbasis orang/waktu/kegiatan seperti OB, OH, OJ, OK, Kegiatan, Paket.
- **Modul Master Referensi Akun Belanja (SIPD RI):**
  - Impor dan integrasi database referensi dari spreadsheet `sipd_ri_r_akun.xlsx`.
  - Tabel `ref_akun_belanja` memuat **9.617 akun belanja** (`5.x`) sesuai Permendagri No. 90 Tahun 2019 dan Kepmendagri No. 050-5888.
  - Tabel `ref_akun` memuat seluruh **30.669 akun SIPD RI** (Pendapatan `4.x`, Belanja `5.x`, Pembiayaan `6.x`).
  - View alias `akun_belanja` untuk fleksibilitas query database.
  - Halaman antarmuka manajemen referensi akun belanja pada `/master/akun_belanja` lengkap dengan 4 kartu KPI statistik dan pencarian berjenjang (*hierarchical indentation*).
- **Endpoint API AJAX:**
  - `GET /ajax/akun_belanja/search`: Autocomplete Select2 akun belanja dengan filter leaf/sub-rincian.
  - `GET /ajax/barang/search`: Autocomplete Select2 barang BMD.
- **Peningkatan UI/UX Responsif:**
  - Tombol tutup drawer (`#sidebarClose`) pada header mobile sidebar.
  - Skrip anti-FOUC (*Flash of Unstyled Content*) di `header.php` agar status sidebar collapsed pada desktop tersimpan mulus di `localStorage`.

### ⚡ Diubah (Changed)
- **Versi Aplikasi:** Dinaikkan dari `1.0.0` ke `2.0.0` pada `application/config/config.php` dan tampilan footer template.
- **Optimasi Master Data Barang BMD (`/master/barang`):**
  - Mengganti pemanggilan 13.367 barang sekaligus dengan **Server-Side Pagination** (`LIMIT` & `OFFSET`), menurunkan konsumsi memori HTML dari ~10 MB menjadi ~35 KB (hemat 99% RAM server).
  - Waktu muat halaman turun dari beberapa detik menjadi instan (**< 50ms**).
  - Tampilan visual baru dengan 4 kartu statistik KPI (Total Barang, Peralatan & Mesin, Gedung & Bangunan, Barang Aktif).
  - Filter pencarian teks/kode barang, kategori warna, ketersediaan harga standar, dan paging dinamis.
- **Fungsi Tombol Hamburger (`#sidebarToggle`):**
  - **Desktop ($\ge 992\text{px}$):** Melipat sidebar dan memperluas konten utama (*full-width canvas*).
  - **Mobile ($< 992\text{px}$):** Menampilkan drawer samping (*off-canvas*) dengan backdrop blur dan dukungan penutupan via backdrop, tombol `X`, tombol hamburger, atau tombol `Escape`.
  - Penyesuaian lebar kolom tabel otomatis (`columns.adjust()`) saat sidebar ditransisikan.

### 🛡️ Keamanan & Validasi (Fixed & Security)
- **Validasi Duplikasi Kode:** Penambahan validasi keunikan `kode_barang` dan `kode_akun` pada controller `Master.php` sebelum menjalankan perintah `INSERT` atau `UPDATE`, mencegah database error 1062 saat pengguna mengedit data.
- **Pembersihan Data Duplikat:** Skrip migrasi `fix_duplicate_barang.sql` untuk merapikan whitespace dan mengalihkan relasi ganda Pipet Tetes secara aman pada `rkbmd_penghapusan`.
- **Migrasi Idempotent:** Pembaruan skrip migrasi `optimize_barang_table.sql` menggunakan Stored Procedure dengan pemeriksaan `information_schema.statistics` agar tidak terjadi error `Duplicate key name 'idx_nama_barang'` saat dieksekusi di server VPS.
- **Isolasi Konfigurasi Lokal:** Penggunaan `database.local.php` yang terdaftar pada `.gitignore` untuk mencegah overwrite kredensial database VPS saat menjalankan `git pull`.
- **Kebijakan Row-Level Security (RLS):** Pengamanan data usulan SSH/SBU agar SKPD hanya dapat mengedit draf usulan miliknya sendiri.

---

## [1.0.0] - 2026-08-18

### 🚀 Ditambahkan (Added)
- Rilis perdana modul perencanaan RKBMD Kabupaten Tapin:
  - Usulan Pengadaan Barang BMD.
  - Usulan Pemeliharaan Barang BMD.
  - Usulan Pemanfaatan Barang BMD.
  - Usulan Pemindahtanganan Barang BMD.
  - Usulan Penghapusan Barang BMD.
- Modul Master Data SKPD, Master Data Barang, Master Periode RKBMD.
- Modul Verifikasi RKBMD oleh tim aset BPKAD.
- Autentikasi pengguna berbasis peran (Admin, SKPD, Verifikator, Pimpinan).
- Modul Notifikasi internal dan log aktivitas audit.
- Fitur ekspor laporan RKBMD format Excel dan PDF.
