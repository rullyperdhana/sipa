-- =========================================================================
-- MIGRASI: STRUKTUR TABEL MASTER STANDAR HARGA & JADWAL PENGUSULAN
-- =========================================================================

-- 1. Tabel Master Standar Harga (Katalog Resmi SSH & SBU 2027)
CREATE TABLE IF NOT EXISTS `ref_standar_harga` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tipe` enum('SSH','SBU') NOT NULL,
  `tahun_anggaran` int NOT NULL DEFAULT '2027',
  `kode_kelompok` varchar(50) NOT NULL,
  `kode_standar` varchar(50) NOT NULL,
  `uraian` varchar(500) NOT NULL,
  `spesifikasi` text,
  `satuan` varchar(50) NOT NULL,
  `harga_satuan` decimal(18,2) NOT NULL DEFAULT '0.00',
  `kode_rekening` varchar(255) DEFAULT NULL,
  `nama_rekening` text DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_kode_standar` (`kode_standar`),
  KEY `idx_tipe_tahun` (`tipe`,`tahun_anggaran`),
  KEY `idx_uraian` (`uraian`(100)),
  KEY `idx_kode_kelompok` (`kode_kelompok`),
  KEY `idx_kode_rekening` (`kode_rekening`(100)),
  KEY `idx_kategori` (`kategori`),
  FULLTEXT KEY `idx_fulltext` (`uraian`,`spesifikasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Jadwal Pengusulan Standar Harga (SSH & SBU)
CREATE TABLE IF NOT EXISTS `standar_harga_jadwal` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tipe` enum('SSH','SBU','SEMUA') NOT NULL DEFAULT 'SEMUA',
  `tahun_anggaran` int NOT NULL DEFAULT '2027',
  `nama_jadwal` varchar(150) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('buka','tutup') NOT NULL DEFAULT 'buka',
  `keterangan` text,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tahun_tipe` (`tahun_anggaran`,`tipe`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Data Default Jadwal Pengusulan
INSERT INTO `standar_harga_jadwal` (`tipe`, `tahun_anggaran`, `nama_jadwal`, `tanggal_mulai`, `tanggal_selesai`, `status`, `keterangan`)
SELECT 'SEMUA', 2027, 'Pengusulan Standar Satuan Harga (SSH) & Standar Biaya Umum (SBU) TA 2027', '2026-01-01', '2027-12-31', 'buka', 'Jadwal resmi pengusulan dan penyesuaian SSH & SBU Tahun Anggaran 2027 Kabupaten Tapin'
WHERE NOT EXISTS (SELECT 1 FROM `standar_harga_jadwal` WHERE `tahun_anggaran` = 2027 AND `tipe` = 'SEMUA');

INSERT INTO `standar_harga_jadwal` (`tipe`, `tahun_anggaran`, `nama_jadwal`, `tanggal_mulai`, `tanggal_selesai`, `status`, `keterangan`)
SELECT 'SEMUA', 2028, 'Pengusulan Standar Satuan Harga & Biaya Umum TA 2028', '2027-01-01', '2027-12-31', 'tutup', 'Jadwal TA 2028 belum dibuka (Menunggu pembuatan/pembukaan jadwal resmi oleh BPKAD)'
WHERE NOT EXISTS (SELECT 1 FROM `standar_harga_jadwal` WHERE `tahun_anggaran` = 2028 AND `tipe` = 'SEMUA');

-- 4. Tambah Kolom Relasi Master & SIPD RI pada Tabel standar_harga_usulan (Idempotent)
DROP PROCEDURE IF EXISTS `AddStandarHargaColumns`;
DELIMITER ;;
CREATE PROCEDURE `AddStandarHargaColumns`()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'standar_harga_usulan' AND `COLUMN_NAME` = 'master_standar_id') THEN
    ALTER TABLE `standar_harga_usulan` ADD COLUMN `master_standar_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'standar_harga_usulan' AND `COLUMN_NAME` = 'kode_kelompok') THEN
    ALTER TABLE `standar_harga_usulan` ADD COLUMN `kode_kelompok` VARCHAR(50) DEFAULT NULL AFTER `kategori`;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'standar_harga_usulan' AND `COLUMN_NAME` = 'kode_rekening') THEN
    ALTER TABLE `standar_harga_usulan` ADD COLUMN `kode_rekening` VARCHAR(255) DEFAULT NULL AFTER `satuan`;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'standar_harga_usulan' AND `COLUMN_NAME` = 'nama_rekening') THEN
    ALTER TABLE `standar_harga_usulan` ADD COLUMN `nama_rekening` TEXT DEFAULT NULL AFTER `kode_rekening`;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'standar_harga_usulan' AND `COLUMN_NAME` = 'harga_acuan_master') THEN
    ALTER TABLE `standar_harga_usulan` ADD COLUMN `harga_acuan_master` DECIMAL(18,2) DEFAULT NULL AFTER `harga_usulan`;
  END IF;
END ;;
DELIMITER ;
CALL `AddStandarHargaColumns`();
DROP PROCEDURE IF EXISTS `AddStandarHargaColumns`;

