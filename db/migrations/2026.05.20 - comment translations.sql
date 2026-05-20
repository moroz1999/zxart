ALTER TABLE `engine_module_comment`
    ADD COLUMN `text_en` TEXT NOT NULL AFTER `content`,
    ADD COLUMN `text_ru` TEXT NOT NULL AFTER `text_en`,
    ADD COLUMN `text_es` TEXT NOT NULL AFTER `text_ru`,
    ADD COLUMN `is_translated` TINYINT(1) NOT NULL DEFAULT 0 AFTER `text_es`;
