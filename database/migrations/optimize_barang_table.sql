-- ============================================================================
-- OPTIMASI INDEKS TABEL BARANG UNTUK PERFORMA TINGGI
-- SIPA (Sistem Informasi Pengelolaan Aset) - BPKAD Kabupaten Tapin
-- ============================================================================

-- Tambahkan index pada nama_barang dan is_active jika belum ada
ALTER TABLE `barang` 
  ADD INDEX `idx_nama_barang` (`nama_barang`(100)),
  ADD INDEX `idx_is_active` (`is_active`);
