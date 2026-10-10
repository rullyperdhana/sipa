# Changelog
Semua perubahan penting pada proyek **SIPA (Sistem Informasi Pengelolaan Aset) - Kabupaten Tapin** didokumentasikan dalam file ini.

Format changelog ini mengacu pada prinsip [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mematuhi [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.5.0] - 2026-10-10

### 🚀 Ditambahkan (Added)
- **Pengaturan Hak Akses Menu & Modul Granular Per-User (Granular RBAC):**
  - **Menu Checklist di Admin Master Pengguna (`/master/user`):** Admin dapat menentukan dan mencentang secara spesifik modul apa saja yang dapat diakses oleh masing-masing akun pengguna (operator SKPD maupun peran lainnya).
  - **Dukungan Preset Cepat 1-Klik:**
    - *Preset User A:* Khusus modul Standar Satuan Harga (`ssh`), Standar Biaya Umum (`sbu`), dan Pusat Laporan (`laporan`).
    - *Preset User B:* Khusus modul Perencanaan RKBMD 5 instrumen (`rkbmd_pengadaan`, `rkbmd_pemeliharaan`, `rkbmd_pemanfaatan`, `rkbmd_pemindahtanganan`, `rkbmd_penghapusan`), dan Pusat Laporan (`laporan`).
    - *Pilih Semua Menu* dan *Bersihkan Semua Centang*.
  - **Visualisasi Status Izin pada Tabel Pengguna:** Kolom baru *"Hak Akses Menu"* yang menampilkan badge pill indikator modul aktif (`RKBMD (5)`, `SSH`, `SBU`, `Laporan`, `Verifikasi`, `Akses Penuh (Admin)`, atau `Default Role`).
  - **Proteksi Ketat Controller-Level (HTTP 403 Forbidden):**
    - `Rkbmd`: Validasi jenis instrumen RKBMD (`can_access('rkbmd_' . $jenis)`).
    - `Ssh`: Pemeriksaan hak akses modul Standar Satuan Harga (`can_access('ssh')`).
    - `Sbu`: Pemeriksaan hak akses modul Standar Biaya Umum (`can_access('sbu')`).
    - `Laporan`: Pemeriksaan hak akses modul Pusat Laporan & Rekap (`can_access('laporan')`).
  - **Navigasi Sidebar & Dashboard Adaptif:** Sidebar navigation drawer dan kartu aksi cepat di dashboard secara otomatis menyembunyikan modul yang tidak diizinkan untuk pengguna tersebut.
  - **Auto-Migration & Database Schema:** Penambahan kolom `menu_permissions TEXT NULL` pada tabel `users` serta mekanisme safe auto-migration pada `User_model`.

---

## [2.4.0] - 2026-10-10

### 🚀 Ditambahkan (Added)
- **Perombakan Total Dashboard Utama Terpadu SIPA (`/dashboard`):**
  - **Integrasi Penuh Modul Standar Harga (SSH & SBU):** Mengakhiri keterisolasian data Standar Harga, kini dipantau secara real-time bersama dengan modul RKBMD.
  - **Banner Live Jadwal Pengusulan:** Indikator dinamis status jadwal pengusulan SSH & SBU (apakah jadwal sedang DIBUKA beserta tanggal batas akhir, atau DITUTUP oleh BPKAD).
  - **Quick Action Bar (Akses Cepat):** Tombol aksi cepat kontekstual berdasarkan peran pengguna (`+ Usul RKBMD`, `+ Usul SSH`, `+ Usul SBU`, `Verifikasi BPKAD`, `Pusat Laporan & Rekap Eksekutif`).
  - **4 Kartu Metrik KPI Modern Bergradien:** Total Seluruh Usulan Terpadu, Total Pagu Usulan Terpadu, Antrean Menunggu Verifikasi BPKAD, dan Kepatuhan Partisipasi SKPD (dengan bubble glassmorphism icons).
  - **Dua Visualisasi Grafik Interaktif (Chart.js):**
    - *Grafik Batang:* Distribusi Alokasi Anggaran Terpadu per Modul (Pengadaan, Pemeliharaan, Pemanfaatan, Pemindahtanganan, Penghapusan, SSH, dan SBU).
    - *Grafik Donat:* Proporsi Status Usulan (Draft, Diajukan/Verifikasi, Disetujui/Ditetapkan, Ditolak/Revisi).
  - **Ringkasan Modul Terpadu Simetris:**
    - *Modul Standar Harga (SSH & SBU):* 2 kartu performa dengan breakdown status (Draft, Diajukan, Diverifikasi, Ditetapkan) serta total nilai pagu.
    - *Modul RKBMD (5 Instrumen):* 5 kartu instrumen (Pengadaan, Pemeliharaan, Pemanfaatan, Pemindahtanganan, Penghapusan) ditambah 1 kartu rekap total membentuk grid 3x2 simetris yang rapi.
  - **Monitoring Progres SKPD & Aktivitas Usulan Terbaru:** Tabulasi usulan terbaru RKBMD vs Standar Harga dan monitoring per SKPD.

- **Peningkatan Sub-Dashboard Verifikasi & Penetapan SSH/SBU:**
  - Penambahan Kartu Indikator KPI Antrean Kerja pada `/ssh/verifikasi` dan `/sbu/verifikasi` (*Menunggu Verifikasi*, *Telah Diverifikasi*, *Perlu Revisi SKPD*, *Total Berkas*).
  - Penambahan Kartu Indikator KPI Antrean Kerja pada `/ssh/penetapan` dan `/sbu/penetapan` (*Menunggu Penetapan Resmi*, *Telah Ditetapkan (SK)*, *Total Berkas*).

---

## [2.3.0] - 2026-10-10

### 🚀 Ditambahkan (Added)
- **Pusat Laporan & Dashboard Eksekutif SIPA (`/laporan`):**
  - **4 Kartu KPI Eksekutif Utama:** Menampilkan Total Usulan Masuk (RKBMD + Standar Harga), Total Pagu Diusulkan (Rp), Realisasi Disetujui/Ditetapkan (Rp) dengan indikator persentase *Approval Rate*, serta Kepatuhan Partisipasi SKPD lengkap dengan progress bar.
  - **Visualisasi Grafik Interaktif Modern (Chart.js):**
    - *Bar Chart:* Alokasi dan distribusi pagu anggaran per 5 jenis usulan RKBMD (Pengadaan, Pemeliharaan, Pemanfaatan, Pemindahtanganan, Penghapusan).
    - *Donut Chart:* Komposisi persentase status seluruh usulan gabungan (Disetujui/Ditetapkan, Menunggu Verifikasi, Draft, Direvisi/Ditolak).
  - **Sistem Tabulasi Terpadu (Multi-Tab Interface):**
    - **Tab 1: Rekapitulasi RKBMD:** 5 kartu instrumen seimbang dan tabel usulan RKBMD interaktif.
    - **Tab 2: Rekapitulasi Standar Harga (SSH & SBU):** Kartu komparasi SSH vs SBU (status dan pagu) beserta tabel rincian usulan standar harga dan tautan bukti survey.
    - **Tab 3: Matriks Kepatuhan SKPD:** Monitoring komprehensif 66 SKPD se-Kabupaten Tapin dengan status kepatuhan (*Lengkap*, *Sebagian*, *Belum Ada Usulan*).
  - **Fitur Ekspor & Cetak Laporan Resmi:**
    - Ekspor Spreadsheet Excel/CSV per modul (`/laporan/export/rkbmd/excel`, `/laporan/export/ssh_sbu/excel`, `/laporan/export/kepatuhan/excel`).
    - Halaman cetak laporan resmi ber-kop Pemerintah Kabupaten Tapin & BPKAD (`/laporan/cetak`) siap ditandatangani Kepala BPKAD.
  - **Peningkatan Filter & UX:** Filter Tahun Anggaran, SKPD, Status Usulan, Kata Kunci, dan tombol *Reset Filter* satu klik.

---

## [2.2.0] - 2026-10-10

### 🚀 Ditambahkan (Added)
- **Mandatori 3 Berkas Bukti Survey Harga Pasar / Brosur Resmi:**
  - Peningkatan formulir pengusulan SSH & SBU (`/ssh/tambah`, `/ssh/edit`, `/sbu/tambah`, `/sbu/edit`): dari 1 upload berkas tunggal menjadi **3 berkas bukti survey pasar / brosur resmi toko/distributor** yang independen (`file_lampiran`, `file_lampiran_2`, `file_lampiran_3`).
  - **Validasi Ketat Wajib Terisi (Strict Mandatory):**
    - Client-side validation (SweetAlert2 & Bootstrap visual states): Mengharuskan ketiga slot survey diunggah dan membatasi ukuran maksimal 5MB per berkas.
    - Server-side validation pada controller `Ssh.php` dan `Sbu.php`: Menggagalkan pengajuan usulan jika salah satu atau lebih dari ketiga berkas survey belum diunggah.
    - Edit mode safeguard: Memastikan integritas data ketiga slot survey tetap lengkap (baik berkas yang sudah ada maupun berkas baru yang diganti).
  - Tampilan visual kartu modern 3 kolom dengan nomor berkas, label *"Wajib"*, petunjuk survey toko/vendor, dan tombol preview/download berkas saat mode edit.
  - Multi-file download routes: Dukungan pengunduhan berkas per nomor survey (`/ssh/download/{id}/{slot}` dan `/sbu/download/{id}/{slot}`) untuk slot 1, 2, dan 3.
  - Kolom bukti survey pada tabel daftar usulan (`/ssh/usulan`, `/sbu/usulan`) dan verifikasi (`/ssh/verifikasi`, `/sbu/verifikasi`) menampilkan 3 tombol pintasan unduh (`S1`, `S2`, `S3`) dengan tooltip.
  - Halaman detail usulan (`/ssh/detail/{id}`, `/sbu/detail/{id}`) menyajikan rincian lengkap ketiga dokumen survey pasar yang diunggah.
  - Script migrasi database: `database/migrations/add_bukti_survey_3_files.sql` untuk penambahan kolom `file_lampiran_2`, `file_nama_asli_2`, `file_lampiran_3`, `file_nama_asli_3` pada tabel `standar_harga_usulan`.

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
