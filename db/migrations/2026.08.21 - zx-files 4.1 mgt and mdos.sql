-- zx-files 4.1 reads MGT and IMG images (GDOS, G+DOS on a DISCiPLE or +D, SAMDOS on a
-- SAM Coupé) and D40/D80 images (MDOS on a Didaktik), so the parsed file structure can
-- hold them as containers instead of opaque files. A .d40 image is read as a D80 one:
-- the drives differ in how many tracks they reach and in nothing else.

ALTER TABLE `engine_files_registry`
    CHANGE `type` `type`
        ENUM ('folder','trd','tap','scl','file','zip','7z','rar','tar','tzx','nex','snx','dsk','fdi','udi','opd','mgt','img','d80')
        CHARACTER SET utf8mb3 COLLATE utf8mb3_estonian_ci NOT NULL;

-- Releases holding one of the newly readable containers keep the flat structure they were
-- parsed into. Their registry rows are dropped and `parsed` cleared, so the `parseReleases`
-- crontab job builds the structure again.
CREATE TEMPORARY TABLE `tmp_zxfiles_mgt_containers` (`id` MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY);

INSERT IGNORE INTO `tmp_zxfiles_mgt_containers` (`id`)
SELECT `id`
FROM `engine_module_zxrelease`
WHERE LOWER(SUBSTRING_INDEX(`fileName`, '.', -1)) IN ('mgt', 'img', 'd40', 'd80');

INSERT IGNORE INTO `tmp_zxfiles_mgt_containers` (`id`)
SELECT DISTINCT f.`elementId`
FROM `engine_files_registry` f
         JOIN `engine_module_zxrelease` r ON r.`id` = f.`elementId`
WHERE f.`type` = 'file'
  AND LOWER(SUBSTRING_INDEX(f.`fileName`, '.', -1)) IN ('mgt', 'img', 'd40', 'd80');

-- SAM Coupé disks are published as .dsk and are ordered like an MGT, so the +3DOS parser
-- read nothing in them. An MGT image is a headerless dump and only three lengths are one at
-- all — 40 or 80 tracks, one side or two — so a .dsk of any other length reads exactly as it
-- did before and is left alone.
INSERT IGNORE INTO `tmp_zxfiles_mgt_containers` (`id`)
SELECT DISTINCT f.`elementId`
FROM `engine_files_registry` f
         JOIN `engine_module_zxrelease` r ON r.`id` = f.`elementId`
WHERE f.`type` = 'dsk'
  AND f.`size` IN (204800, 409600, 819200);

DELETE f
FROM `engine_files_registry` f
         JOIN `tmp_zxfiles_mgt_containers` t ON t.`id` = f.`elementId`;

UPDATE `engine_module_zxrelease` r
    JOIN `tmp_zxfiles_mgt_containers` t ON t.`id` = r.`id`
SET r.`parsed` = 0;

DROP TEMPORARY TABLE `tmp_zxfiles_mgt_containers`;
