-- =========================================================================
-- SIPA KABUPATEN TAPIN
-- Migrasi: Penambahan Kolom Bukti Survey Harga Pasar 2 & 3 (Wajib 3 Survey)
-- Target: Tabel `standar_harga_usulan`
-- =========================================================================

DROP PROCEDURE IF EXISTS `AddBuktiSurveyColumns`;
DELIMITER $$
CREATE PROCEDURE `AddBuktiSurveyColumns`()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'standar_harga_usulan' AND `COLUMN_NAME` = 'file_lampiran_2') THEN
    ALTER TABLE `standar_harga_usulan` ADD COLUMN `file_lampiran_2` VARCHAR(255) DEFAULT NULL AFTER `file_nama_asli`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'standar_harga_usulan' AND `COLUMN_NAME` = 'file_nama_asli_2') THEN
    ALTER TABLE `standar_harga_usulan` ADD COLUMN `file_nama_asli_2` VARCHAR(255) DEFAULT NULL AFTER `file_lampiran_2`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'standar_harga_usulan' AND `COLUMN_NAME` = 'file_lampiran_3') THEN
    ALTER TABLE `standar_harga_usulan` ADD COLUMN `file_lampiran_3` VARCHAR(255) DEFAULT NULL AFTER `file_nama_asli_2`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'standar_harga_usulan' AND `COLUMN_NAME` = 'file_nama_asli_3') THEN
    ALTER TABLE `standar_harga_usulan` ADD COLUMN `file_nama_asli_3` VARCHAR(255) DEFAULT NULL AFTER `file_lampiran_3`;
  END IF;
END$$
DELIMITER ;

CALL `AddBuktiSurveyColumns`();
DROP PROCEDURE IF EXISTS `AddBuktiSurveyColumns`;
