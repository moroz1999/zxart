-- A tune gets its own executable file next to the tracker file. The tracker
-- field used to hold both, so the runnable programs move to the new field by
-- their format: tapes, disk images, snapshots and Hobeta code blocks. Archives
-- and texts stay where they are, their content is unknown. The stored file keeps
-- its name on disk, only the field that points at it changes.
ALTER TABLE `engine_module_zxmusic`
    ADD `exeFile` varchar(255) NOT NULL DEFAULT '' AFTER `trackerFileName`,
    ADD `exeFileName` varchar(255) NOT NULL DEFAULT '' AFTER `exeFile`;

UPDATE `engine_module_zxmusic`
SET `exeFile`         = `trackerFile`,
    `exeFileName`     = `trackerFileName`,
    `trackerFile`     = '',
    `trackerFileName` = ''
WHERE LOWER(SUBSTRING_INDEX(`trackerFileName`, '.', -1))
          IN ('tap', 'tzx', 'trd', 'scl', 'sna', 'z80', 'dsk', '$c', '%24c');
