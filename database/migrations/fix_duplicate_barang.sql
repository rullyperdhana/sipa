-- ============================================================================
-- PEMBERSIHAN DUPLIKAT KODE BARANG PIPET TETES & WHITESPACE KODE BARANG
-- SIPA (Sistem Informasi Pengelolaan Aset) - BPKAD Kabupaten Tapin
-- ============================================================================

-- 1. Bersihkan spasi di awal kode_barang jika ada
UPDATE `barang` SET `kode_barang` = TRIM(`kode_barang`) WHERE `kode_barang` LIKE ' %';

-- 2. Alihkan relasi rkbmd_penghapusan dari barang id 513 ke id 8702 jika ada
UPDATE `rkbmd_penghapusan` SET `barang_id` = 8702 WHERE `barang_id` = 513;

-- 3. Hapus baris duplikat id 513 (.1.3.2.08.03.04.013) yang merupakan duplikat dari 8702 (1.3.2.08.03.04.013)
DELETE FROM `barang` WHERE `id` = 513;
