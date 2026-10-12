-- Migration: Menambahkan kolom-kolom pendukung keselarasan SIPD (form_upload_SIPD.xlsx) ke tabel standar_harga_usulan dan ref_standar_harga
-- Tanggal: 12 Oktober 2026

-- 1. Penambahan Kolom di standar_harga_usulan
ALTER TABLE `standar_harga_usulan`
  ADD COLUMN `keterangan_usulan` TEXT NULL COMMENT 'Keterangan tambahan item usulan untuk SIPD' AFTER `spesifikasi`,
  ADD COLUMN `kode_rekening_2` VARCHAR(255) NULL COMMENT 'Kode Rekening Belanja 2 SIPD' AFTER `nama_rekening`,
  ADD COLUMN `nama_rekening_2` TEXT NULL COMMENT 'Uraian Rekening Belanja 2' AFTER `kode_rekening_2`,
  ADD COLUMN `kode_rekening_3` VARCHAR(255) NULL COMMENT 'Kode Rekening Belanja 3 SIPD' AFTER `nama_rekening_2`,
  ADD COLUMN `nama_rekening_3` TEXT NULL COMMENT 'Uraian Rekening Belanja 3' AFTER `kode_rekening_3`,
  ADD COLUMN `harga_2` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga Standar Zona 2' AFTER `harga_ditetapkan`,
  ADD COLUMN `harga_3` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga Standar Zona 3' AFTER `harga_2`,
  ADD COLUMN `nilai_tkdn` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai Persentase TKDN (%)' AFTER `harga_3`,
  ADD INDEX `idx_usulan_kode_kelompok` (`kode_kelompok`);

-- 2. Penambahan Kolom di ref_standar_harga
ALTER TABLE `ref_standar_harga`
  ADD COLUMN `keterangan` TEXT NULL COMMENT 'Keterangan standar harga untuk SIPD' AFTER `spesifikasi`,
  ADD COLUMN `kode_rekening_2` VARCHAR(255) NULL COMMENT 'Kode Rekening Belanja 2 SIPD' AFTER `nama_rekening`,
  ADD COLUMN `nama_rekening_2` TEXT NULL COMMENT 'Uraian Rekening Belanja 2' AFTER `kode_rekening_2`,
  ADD COLUMN `kode_rekening_3` VARCHAR(255) NULL COMMENT 'Kode Rekening Belanja 3 SIPD' AFTER `nama_rekening_2`,
  ADD COLUMN `nama_rekening_3` TEXT NULL COMMENT 'Uraian Rekening Belanja 3' AFTER `kode_rekening_3`,
  ADD COLUMN `harga_2` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga Standar Zona 2' AFTER `harga_satuan`,
  ADD COLUMN `harga_3` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga Standar Zona 3' AFTER `harga_2`,
  ADD COLUMN `nilai_tkdn` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai Persentase TKDN (%)' AFTER `harga_3`;
