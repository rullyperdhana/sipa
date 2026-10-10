-- ============================================================================
-- Migration: Penambahan Status 'Ditolak' pada Modul SSH dan SBU
-- Sistem Informasi Pengelolaan Aset (SIPA) - BPKAD Kabupaten Tapin
-- ============================================================================

ALTER TABLE `standar_harga_usulan` 
MODIFY COLUMN `status_proses` ENUM('Draft','Diajukan','Direvisi','Diverifikasi','Ditolak','Ditetapkan') 
NOT NULL DEFAULT 'Draft' 
COMMENT 'Alur transisi status usulan (termasuk Ditolak)';
