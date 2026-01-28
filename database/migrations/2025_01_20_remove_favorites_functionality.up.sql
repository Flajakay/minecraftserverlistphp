DROP TABLE IF EXISTS `favorites`;

SET @sql := (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'servers'
        AND COLUMN_NAME = 'favorites'
    ),
    'ALTER TABLE `servers` DROP COLUMN `favorites`;',
    'SELECT 1;'
  )
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

