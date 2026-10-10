-- =========================================================================
-- MIGRASI: Penambahan Kolom menu_permissions pada Tabel users
-- SIPA (Sistem Informasi Pengelolaan Aset) - Kabupaten Tapin
-- =========================================================================

-- Tambah kolom menu_permissions untuk menyimpan JSON hak akses menu dinamis per user
-- Format penyimpanan: JSON array string, contoh: ["ssh","sbu","laporan"] atau ["rkbmd_pengadaan","rkbmd_pemeliharaan","laporan"]
-- Jika NULL, maka user menggunakan default permissions sesuai rolenya.

ALTER TABLE `users` 
ADD COLUMN `menu_permissions` TEXT NULL DEFAULT NULL AFTER `role`;
