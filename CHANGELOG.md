# Changelog
Semua perubahan penting pada proyek **SIPA (Sistem Informasi Pengelolaan Aset) - Kabupaten Tapin** didokumentasikan dalam file ini.

Format changelog ini mengacu pada prinsip [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mematuhi [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.1.0] - 2026-10-10

### 🚀 Ditambahkan (Added)
- **Master Data Standar Harga SSH & SBU TA 2027 (Katalog Referensi Resmi):**
  - Integrasi 11.519 data resmi dari spreadsheet SIPD RI Kabupaten Tapin: **5.786 item SSH** (`export_excel_ssh_Kab. Tapin.xlsx`) dan **5.733 item SBU** (`export_excel_sbu_Kab. Tapin.xlsx`).
  - Pembuatan tabel katalog `ref_standar_harga` lengkap dengan kode kelompok, kode standar, nama barang/standar, spesifikasi teknis rinci, satuan, harga satuan 2027, kode rekening belanja SIPD RI, nama rekening belanja, dan tahun anggaran.
  - Halaman antarmuka katalog Master Data SSH (`/ssh/master_data`) dan SBU (`/sbu/master_data`) dengan Server-Side Pagination cepat (< 30ms), kartu KPI statistik, pencarian Fulltext, filter kategori, dan tombol cepat *"Usulkan"*.
- **Modul Penjadwalan Pengusulan Standar Harga (`standar_harga_jadwal`):**
  - Manajemen periode pengusulan oleh BPKAD (Admin/Verifikator) untuk SSH dan SBU per tahun anggaran (`/ssh/jadwal` dan `/sbu/jadwal`).
  - Mekanisme penguncian pengusulan SKPD: tombol dan form tambah usulan otomatis terkunci (*disabled*) dengan status *"Menunggu Pembuatan Jadwal oleh BPKAD"* jika tidak ada jadwal aktif yang dibuka.
  - Banner indikator jadwal dinamis pada daftar usulan SKPD yang menampilkan status (Buka/Tutup), periode tanggal mulai-selesai, dan catatan dari BPKAD.
  - Kemudahan saklar toggle status jadwal (Buka/Tutup) dalam satu klik oleh tim BPKAD.
- **Pencarian Cerdas Rekening Belanja SIPD RI Berdasarkan Nama:**
  - Antarmuka pencarian akun belanja pada formulir usulan SSH & SBU dengan dukungan pengetikan nama kebutuhan belanja (contoh: *"Alat Tulis"*, *"Kertas"*, *"Honorarium"*, *"Perjalanan Dinas"*, *"Pemeliharaan"*).
  - Sistem otomatis menampilkan sugesti hasil dari 9.617 referensi akun belanja SIPD RI (`ref_akun_belanja`) dan langsung memunculkan **Kode Rekening Belanja** resmi dalam bentuk kartu badge visual interaktif.
  - Peningkatan metode `searchSelect2` pada `Akun_model.php` dengan pencocokan multi-kata (*multi-word search*) fleksibel.
- **Penyelarasan Kategori Barang & Jasa dengan Master Data TA 2027:**
  - Penambahan klasifikasi aset BMD resmi (*Bahan & Persediaan Habis Pakai*, *Peralatan dan Mesin*, *Gedung dan Bangunan*, *Jalan, Irigasi dan Jaringan*, *Aset Tetap Lainnya*, *Tanah*, dll.) ke dalam daftar dropdown kategori SSH & SBU.
  - Dropdown kategori kini bersifat dinamis menggabungkan kategori master dari database `ref_standar_harga` dan kategori fungsional.
  - Pemilihan item master pada form usulan otomatis memilih (*auto-select*) kategori barang yang tepat tanpa perlu diubah manual oleh operator.
- **Interkoneksi Data Menyeluruh Antar Modul:**
  - Pilihan mode pengusulan pada formulir: **Pilih dari Master Data TA 2027** atau **Input Manual Standar Baru**.
  - Autocomplete AJAX Select2 terhubung ke master katalog (`/ajax/standar_harga/search` & `/ajax/standar_harga/detail/(:num)`).
  - Pengisian otomatis (*auto-populate*) nama standar, spesifikasi teknis, satuan, kelompok akun belanja SIPD RI, serta harga dasar TA 2027.
  - Komparasi harga usulan terhadap baseline 2027 dengan indikator visual selisih dan persentase perubahan harga.
  - Sinkronisasi otomatis ke katalog master (`ref_standar_harga`) saat usulan disetujui pada tahap penetapan akhir.
- **Endpoint API AJAX Baru:**
  - `GET /ajax/standar_harga/search?q={keyword}&tipe={ssh|sbu}&tahun={2027}`: Autocomplete Select2 katalog master standar harga.
  - `GET /ajax/standar_harga/detail/{id}`: Pengambilan data detail item master beserta rekening belanja SIPD terkait.

### ⚡ Diubah (Changed)
- **Versi Aplikasi:** Dinaikkan ke `2.1.0` pada `application/config/config.php` dan tampilan footer sistem.
- **Struktur Tabel Usulan:** Penambahan kolom relasi `master_standar_id`, `kode_kelompok`, `kode_rekening`, `nama_rekening`, dan `harga_acuan_master` pada tabel `standar_harga_usulan`.
- **Navigasi Sidebar:** Penambahan menu *Jadwal Pengusulan* pada modul SSH dan SBU untuk role Admin dan Verifikator.

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
