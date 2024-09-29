DROP TABLE IF EXISTS `engine_module_pressarticle_meta`;
ALTER TABLE `engine_module_pressarticle` DROP IF EXISTS `hasAiData`;
ALTER TABLE `engine_module_pressarticle` CHANGE `languageId` `languageId` MEDIUMINT NOT NULL;
UPDATE `engine_module_pressarticle` SET `languageId`=930;
ALTER TABLE `engine_queue` CHANGE `type` `type` ENUM('recalculation','offline','ai_seo','ai_intro','ai_categories','ai_press') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `engine_module_pressarticle` ADD `h1` VARCHAR(255) NOT NULL AFTER `externalLink`, ADD `metaTitle` VARCHAR(255) NOT NULL AFTER `h1`, ADD `metaDescription` TEXT NOT NULL AFTER `metaTitle`;