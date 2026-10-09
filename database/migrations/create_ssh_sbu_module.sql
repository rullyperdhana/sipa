-- ============================================================================
-- MODUL STANDAR SATUAN HARGA (SSH) & STANDAR BIAYA UMUM (SBU)
-- SIPA (Sistem Informasi Pengelolaan Aset) - BPKAD Kabupaten Tapin
-- ============================================================================

-- 1. Penyesuaian ENUM Role pada tabel users (jika belum memuat operator_skpd dan penetap)
ALTER TABLE `users` 
MODIFY COLUMN `role` ENUM('admin','skpd','operator_skpd','verifikator','penetap','pimpinan') NOT NULL DEFAULT 'skpd';

-- ----------------------------------------------------------------------------
-- 2. Tabel Utama: standar_harga_usulan (SSH & SBU)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `standar_harga_usulan` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_usulan` VARCHAR(50) NOT NULL COMMENT 'Nomor unik usulan, misal SSH-2026-0001',
  `tipe` ENUM('SSH','SBU') NOT NULL DEFAULT 'SSH' COMMENT 'SSH=Standar Satuan Harga, SBU=Standar Biaya Umum',
  `kategori` VARCHAR(100) NOT NULL COMMENT 'Kategori barang/jasa, misal Alat Tulis Kantor, Peralatan Komputer, dsb',
  `uraian` VARCHAR(255) NOT NULL COMMENT 'Nama item barang atau jasa',
  `spesifikasi` TEXT NOT NULL COMMENT 'Deskripsi detail spesifikasi teknis item',
  `satuan` VARCHAR(50) NOT NULL COMMENT 'Satuan item (Unit, Rim, Paket, Orang/Bulan, dll)',
  `harga_usulan` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga yang diajukan oleh SKPD',
  `harga_ditetapkan` DECIMAL(18,2) DEFAULT NULL COMMENT 'Harga hasil verifikasi dan penetapan',
  `file_lampiran` VARCHAR(255) DEFAULT NULL COMMENT 'Nama file bukti pendukung / katalog / survey harga',
  `file_nama_asli` VARCHAR(255) DEFAULT NULL COMMENT 'Nama asli file saat diunggah',
  `id_skpd` INT(10) UNSIGNED NOT NULL COMMENT 'ID SKPD pengusul (relasi ke tabel skpd)',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT 'ID user pembuat usulan (relasi ke tabel users)',
  `status_proses` ENUM('Draft','Diajukan','Direvisi','Diverifikasi','Ditetapkan') NOT NULL DEFAULT 'Draft' COMMENT 'Alur transisi status usulan',
  `catatan_verifikator` TEXT DEFAULT NULL COMMENT 'Catatan perbaikan jika status Direvisi atau keterangan verifikator',
  `verifikator_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'ID user verifikator yang memproses',
  `tgl_verifikasi` DATETIME DEFAULT NULL COMMENT 'Waktu verifikasi',
  `penetap_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'ID user pimpinan / penetap harga',
  `tgl_penetapan` DATETIME DEFAULT NULL COMMENT 'Waktu penetapan akhir',
  `tahun_anggaran` INT(4) NOT NULL DEFAULT 2026 COMMENT 'Tahun berlakunya standar harga',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kode_usulan` (`kode_usulan`),
  KEY `idx_skpd_status` (`id_skpd`, `status_proses`),
  KEY `idx_status` (`status_proses`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_tipe_tahun` (`tipe`, `tahun_anggaran`),
  CONSTRAINT `fk_ssh_skpd` FOREIGN KEY (`id_skpd`) REFERENCES `skpd` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ssh_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ssh_verifikator` FOREIGN KEY (`verifikator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ssh_penetap` FOREIGN KEY (`penetap_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Tabel Riwayat Audit & Log Aktivitas Status (standar_harga_log)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `standar_harga_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usulan_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `status_sebelum` VARCHAR(50) DEFAULT NULL,
  `status_sesudah` VARCHAR(50) NOT NULL,
  `catatan` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_usulan` (`usulan_id`),
  CONSTRAINT `fk_log_usulan` FOREIGN KEY (`usulan_id`) REFERENCES `standar_harga_usulan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Sample Data Awal untuk Pengetesan Seluruh Status & Role
-- ----------------------------------------------------------------------------
INSERT INTO `standar_harga_usulan` 
(`id`, `kode_usulan`, `tipe`, `kategori`, `uraian`, `spesifikasi`, `satuan`, `harga_usulan`, `harga_ditetapkan`, `id_skpd`, `user_id`, `status_proses`, `catatan_verifikator`, `tahun_anggaran`, `created_at`)
VALUES
(1, 'SSH-2026-0001', 'SSH', 'Alat Tulis Kantor', 'Kertas HVS A4 80gr', 'Ukuran A4 210x297mm, 80 gsm, 500 lembar/rim', 'Rim', 65000.00, NULL, 1, 5, 'Draft', NULL, 2026, NOW()),
(2, 'SSH-2026-0002', 'SSH', 'Peralatan Komputer', 'Laptop Pengadaan Standar ASN', 'Intel Core i5 Gen 13, RAM 16GB, SSD 512GB, Windows 11 Pro', 'Unit', 13500000.00, NULL, 1, 5, 'Diajukan', NULL, 2026, NOW()),
(3, 'SSH-2026-0003', 'SSH', 'Bahan Bangunan', 'Semen Portland Komposit 50 Kg', 'SNI 7064:2014, Kemasan 50 Kg', 'Sak', 85000.00, NULL, 1, 5, 'Direvisi', 'Mohon lampirkan survey harga dari minimal 3 distributor lokal resmi.', 2026, NOW()),
(4, 'SBU-2026-0001', 'SBU', 'Honorarium', 'Honorarium Narasumber Pakar/Praktisi', 'Tingkat Nasional/Provinsi, per Orang per Jam Pelajaran', 'OJ (Orang/Jam)', 1000000.00, 1000000.00, 1, 5, 'Diverifikasi', 'Telah disesuaikan dengan PMK Standar Biaya Masukan yang berlaku.', 2026, NOW()),
(5, 'SBU-2026-0002', 'SBU', 'Jasa Tenaga Ahli', 'Tenaga Ahli Programmer Senior', 'Pendidikan S1 Informatika, Pengalaman > 5 Tahun, Sertifikasi BNSP', 'OB (Orang/Bulan)', 15000000.00, 15000000.00, 1, 5, 'Ditetapkan', 'Sesuai dengan Keputusan Bupati tentang SBU TA 2026.', 2026, NOW());

-- Log histori untuk data awal
INSERT INTO `standar_harga_log` (`usulan_id`, `user_id`, `role`, `status_sebelum`, `status_sesudah`, `catatan`, `created_at`)
VALUES
(1, 5, 'operator_skpd', NULL, 'Draft', 'Membuat usulan baru', NOW()),
(2, 5, 'operator_skpd', 'Draft', 'Diajukan', 'Mengajukan usulan ke BPKAD', NOW()),
(3, 3, 'verifikator', 'Diajukan', 'Direvisi', 'Mohon lampirkan survey harga dari minimal 3 distributor lokal resmi.', NOW()),
(4, 3, 'verifikator', 'Diajukan', 'Diverifikasi', 'Disetujui untuk diteruskan ke penetapan harga', NOW()),
(5, 1, 'penetap', 'Diverifikasi', 'Ditetapkan', 'Telah disahkan dan dikunci dalam SK Standar Satuan Harga', NOW());

-- ============================================================================
-- 5. ROW LEVEL SECURITY (RLS) POLICIES PADA POSTGRESQL (DOKUMENTASI DDL NATIVE)
-- Jika sistem database bermigrasi ke PostgreSQL dengan native RLS:
-- ============================================================================
/*
ALTER TABLE standar_harga_usulan ENABLE ROW LEVEL SECURITY;

-- Policy 1: operator_skpd
-- SELECT: hanya data miliknya sendiri (atau data berstatus Ditetapkan)
CREATE POLICY rls_operator_skpd_select ON standar_harga_usulan
  FOR SELECT
  USING (
    id_skpd = NULLIF(current_setting('app.current_skpd_id', true), '')::int
    OR status_proses = 'Ditetapkan'
  );

-- INSERT: hanya untuk id_skpd miliknya sendiri dan status awal wajib 'Draft'
CREATE POLICY rls_operator_skpd_insert ON standar_harga_usulan
  FOR INSERT
  WITH CHECK (
    id_skpd = NULLIF(current_setting('app.current_skpd_id', true), '')::int
    AND status_proses = 'Draft'
  );

-- UPDATE: hanya data miliknya sendiri dan HANYA ketika status bernilai 'Draft' atau 'Direvisi'
CREATE POLICY rls_operator_skpd_update ON standar_harga_usulan
  FOR UPDATE
  USING (
    id_skpd = NULLIF(current_setting('app.current_skpd_id', true), '')::int
    AND status_proses IN ('Draft', 'Direvisi')
  )
  WITH CHECK (
    id_skpd = NULLIF(current_setting('app.current_skpd_id', true), '')::int
    AND status_proses IN ('Draft', 'Diajukan', 'Direvisi')
  );

-- Policy 2: verifikator
-- SELECT: dapat melihat semua data
CREATE POLICY rls_verifikator_select ON standar_harga_usulan
  FOR SELECT
  USING (true);

-- UPDATE: hanya boleh mengupdate baris yang statusnya 'Diajukan'
CREATE POLICY rls_verifikator_update ON standar_harga_usulan
  FOR UPDATE
  USING (status_proses = 'Diajukan')
  WITH CHECK (status_proses IN ('Diverifikasi', 'Direvisi'));

-- Policy 3: penetap
-- SELECT: dapat melihat semua data
CREATE POLICY rls_penetap_select ON standar_harga_usulan
  FOR SELECT
  USING (true);

-- UPDATE: hanya boleh mengupdate baris yang statusnya 'Diverifikasi' menjadi 'Ditetapkan'
CREATE POLICY rls_penetap_update ON standar_harga_usulan
  FOR UPDATE
  USING (status_proses = 'Diverifikasi')
  WITH CHECK (status_proses = 'Ditetapkan');
*/
