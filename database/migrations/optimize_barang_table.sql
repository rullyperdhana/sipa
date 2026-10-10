-- ============================================================================
-- OPTIMASI INDEKS TABEL BARANG UNTUK PERFORMA TINGGI (AMAN & IDEMPOTENT)
-- SIPA (Sistem Informasi Pengelolaan Aset) - BPKAD Kabupaten Tapin
-- ============================================================================

DROP PROCEDURE IF EXISTS `AddIndexIfNotExists`;

DELIMITER $$
CREATE PROCEDURE `AddIndexIfNotExists`(
    IN tableName VARCHAR(64),
    IN indexName VARCHAR(64),
    IN indexDefinition VARCHAR(255)
)
BEGIN
    DECLARE indexCount INT;
    SELECT COUNT(*) INTO indexCount 
    FROM information_schema.statistics 
    WHERE table_schema = DATABASE() 
      AND table_name = tableName 
      AND index_name = indexName;
      
    IF indexCount = 0 THEN
        SET @sqlQuery = CONCAT('ALTER TABLE `', tableName, '` ADD INDEX `', indexName, '` ', indexDefinition);
        PREPARE stmt FROM @sqlQuery;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- Buat indeks jika belum ada
CALL `AddIndexIfNotExists`('barang', 'idx_nama_barang', '(`nama_barang`(100))');
CALL `AddIndexIfNotExists`('barang', 'idx_is_active', '(`is_active`)');

-- Hapus procedure temporary
DROP PROCEDURE IF EXISTS `AddIndexIfNotExists`;
