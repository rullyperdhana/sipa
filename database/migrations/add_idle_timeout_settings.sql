-- =========================================================================
-- Migrasi: Pengaturan Auto Logout User Idle (SIPA Kabupaten Tapin)
-- =========================================================================

-- Memastikan tabel ex_settings tersedia
CREATE TABLE IF NOT EXISTS `ex_settings` (
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menambahkan pengaturan default untuk auto logout user saat idle
INSERT INTO `ex_settings` (`key`, `value`) VALUES
('idle_timeout_enabled', '1'),
('idle_timeout_minutes', '15'),
('idle_warning_seconds', '60')
ON DUPLICATE KEY UPDATE `key` = `key`;
