-- Migration: Menambahkan tabel skpd_nomenklatur untuk pencatatan nama/nomenklatur SKPD per Tahun Anggaran
CREATE TABLE IF NOT EXISTS `skpd_nomenklatur` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `skpd_id` INT UNSIGNED NOT NULL,
    `tahun_anggaran` INT NOT NULL,
    `nama_skpd` VARCHAR(255) NOT NULL,
    `kode_skpd` VARCHAR(50) DEFAULT NULL,
    `kepala_skpd` VARCHAR(150) DEFAULT NULL,
    `nip_kepala` VARCHAR(30) DEFAULT NULL,
    `jabatan_kepala` VARCHAR(200) DEFAULT NULL,
    `nama_pengurus` VARCHAR(150) DEFAULT NULL,
    `nip_pengurus` VARCHAR(30) DEFAULT NULL,
    `keterangan` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_skpd_tahun` (`skpd_id`, `tahun_anggaran`),
    KEY `idx_tahun` (`tahun_anggaran`),
    CONSTRAINT `fk_nomenklatur_skpd` FOREIGN KEY (`skpd_id`) REFERENCES `skpd` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
