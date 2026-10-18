-- A tune gets an uploaded audio recording (MP3 or OGG), played instead of the
-- conversion server's. Recordings kept in the tracker field move to it; the
-- stored file keeps its name on disk.
ALTER TABLE `engine_module_zxmusic`
    ADD `audioFile` varchar(255) NOT NULL DEFAULT '' AFTER `trackerFileName`,
    ADD `audioFileName` varchar(255) NOT NULL DEFAULT '' AFTER `audioFile`;

UPDATE `engine_module_zxmusic`
SET `audioFile`       = `trackerFile`,
    `audioFileName`   = `trackerFileName`,
    `trackerFile`     = '',
    `trackerFileName` = ''
WHERE LOWER(SUBSTRING_INDEX(`trackerFileName`, '.', -1)) IN ('mp3', 'ogg');
