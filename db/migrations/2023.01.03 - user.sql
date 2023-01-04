ALTER TABLE `engine_module_user` ADD `comments` TINYINT NOT NULL AFTER `verified`;
ALTER TABLE `engine_module_user` ADD `banned` TINYINT NOT NULL AFTER `comments`;