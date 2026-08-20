-- zx-files 4 reads DSK, FDI, UDI, OPD and TAR containers, so the parsed file
-- structure can now hold them as containers instead of opaque files.

ALTER TABLE `engine_files_registry`
    CHANGE `type` `type`
        ENUM ('folder','trd','tap','scl','file','zip','7z','rar','tar','tzx','nex','snx','dsk','fdi','udi','opd')
        CHARACTER SET utf8mb3 COLLATE utf8mb3_estonian_ci NOT NULL;

-- Releases holding one of the newly readable containers keep the flat structure
-- they were parsed into. Their registry rows are dropped and `parsed` cleared, so
-- the `parseReleases` crontab job builds the structure again.
CREATE TEMPORARY TABLE `tmp_zxfiles_containers` (`id` MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY);

INSERT IGNORE INTO `tmp_zxfiles_containers` (`id`)
SELECT `id`
FROM `engine_module_zxrelease`
WHERE LOWER(SUBSTRING_INDEX(`fileName`, '.', -1)) IN ('dsk', 'edsk', 'fdi', 'udi', 'opd', 'opu', 'tar');

INSERT IGNORE INTO `tmp_zxfiles_containers` (`id`)
SELECT DISTINCT f.`elementId`
FROM `engine_files_registry` f
         JOIN `engine_module_zxrelease` r ON r.`id` = f.`elementId`
WHERE f.`type` = 'file'
  AND LOWER(SUBSTRING_INDEX(f.`fileName`, '.', -1)) IN ('dsk', 'edsk', 'fdi', 'udi', 'opd', 'opu', 'tar');

DELETE f
FROM `engine_files_registry` f
         JOIN `tmp_zxfiles_containers` t ON t.`id` = f.`elementId`;

UPDATE `engine_module_zxrelease` r
    JOIN `tmp_zxfiles_containers` t ON t.`id` = r.`id`
SET r.`parsed` = 0;

DROP TEMPORARY TABLE `tmp_zxfiles_containers`;
