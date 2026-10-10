-- Migrasi Fitur Pemberitahuan WhatsApp (v2.6.0)
-- Menambahkan kolom nomor WhatsApp pada tabel users dan memastikan tabel ex_settings tersedia

ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `no_wa` VARCHAR(25) NULL DEFAULT NULL AFTER `email`;

-- Pastikan tabel ex_settings tersedia untuk menyimpan konfigurasi WhatsApp Gateway opsional
CREATE TABLE IF NOT EXISTS `ex_settings` (
    `key` VARCHAR(255) NOT NULL,
    `value` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default setting WhatsApp (default: direct wa.me link tanpa biaya API pihak ketiga)
INSERT IGNORE INTO `ex_settings` (`key`, `value`) VALUES
('wa_mode', 'direct'),
('wa_gateway_provider', 'fonnte'),
('wa_gateway_url', 'https://api.fonnte.com/send'),
('wa_gateway_token', ''),
('wa_auto_send', '0'),
('wa_sender_footer', 'Sistem Informasi Pengelolaan Aset (SIPA) Pemerintah Kabupaten Tapin');
