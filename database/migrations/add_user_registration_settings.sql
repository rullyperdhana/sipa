-- =========================================================================
-- Migrasi: Pengaturan Pendaftaran Mandiri Pengguna (SIPA Kabupaten Tapin)
-- =========================================================================

-- Memastikan tabel ex_settings tersedia
CREATE TABLE IF NOT EXISTS `ex_settings` (
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menambahkan pengaturan default pendaftaran mandiri pengguna
INSERT INTO `ex_settings` (`key`, `value`) VALUES
('registration_enabled', '1'),
('registration_require_approval', '1'),
('registration_default_role', 'operator_skpd')
ON DUPLICATE KEY UPDATE `key` = `key`;
