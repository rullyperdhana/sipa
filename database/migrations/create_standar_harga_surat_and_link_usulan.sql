-- ============================================================================
-- MODUL STANDAR SATUAN HARGA (SSH) & STANDAR BIAYA UMUM (SBU)
-- SIPA (Sistem Informasi Pengelolaan Aset) - BPKAD Kabupaten Tapin
-- Migrasi: Tabel standar_harga_surat dan Relasi surat_id pada standar_harga_usulan
-- ============================================================================

-- 1. Buat Tabel Induk Surat Usulan SKPD
CREATE TABLE IF NOT EXISTS `standar_harga_surat` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_surat` VARCHAR(100) NOT NULL COMMENT 'Nomor surat resmi SKPD, misal: 005/123/DISDIK/2026',
  `tanggal_surat` DATE NOT NULL COMMENT 'Tanggal surat usulan resmi SKPD',
  `perihal` VARCHAR(255) NOT NULL COMMENT 'Perihal surat, misal: Usulan Penambahan Standar Satuan Harga TA 2027',
  `tipe` ENUM('SSH','SBU') NOT NULL DEFAULT 'SSH' COMMENT 'SSH=Standar Satuan Harga, SBU=Standar Biaya Umum',
  `file_surat` VARCHAR(255) NOT NULL COMMENT 'Nama file PDF surat resmi yang diunggah',
  `file_nama_asli` VARCHAR(255) NOT NULL COMMENT 'Nama asli file surat saat diunggah',
  `id_skpd` INT(10) UNSIGNED NOT NULL COMMENT 'ID SKPD pengusul',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT 'ID user pengusul',
  `tahun_anggaran` INT(4) NOT NULL DEFAULT 2027 COMMENT 'Tahun anggaran usulan',
  `keterangan` TEXT DEFAULT NULL COMMENT 'Catatan tambahan pengantar dari SKPD',
  `status_proses` ENUM('Draft','Diajukan','Direvisi','Ditolak','Diverifikasi','Ditetapkan') NOT NULL DEFAULT 'Draft',
  `catatan_verifikator` TEXT DEFAULT NULL COMMENT 'Catatan verifikator atas berkas surat / kumpulan usulan',
  `verifikator_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'ID user verifikator BPKAD',
  `tgl_verifikasi` DATETIME DEFAULT NULL COMMENT 'Waktu verifikasi',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_surat_skpd` (`id_skpd`),
  KEY `idx_surat_tipe_tahun` (`tipe`, `tahun_anggaran`),
  KEY `idx_surat_status` (`status_proses`),
  CONSTRAINT `fk_surat_skpd` FOREIGN KEY (`id_skpd`) REFERENCES `skpd` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_surat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_surat_verifikator` FOREIGN KEY (`verifikator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tambah Kolom surat_id pada Tabel standar_harga_usulan
ALTER TABLE `standar_harga_usulan`
ADD COLUMN `surat_id` INT(10) UNSIGNED DEFAULT NULL AFTER `tipe`;

ALTER TABLE `standar_harga_usulan`
ADD KEY `idx_surat_id` (`surat_id`);

ALTER TABLE `standar_harga_usulan`
ADD CONSTRAINT `fk_usulan_surat` FOREIGN KEY (`surat_id`) REFERENCES `standar_harga_surat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
