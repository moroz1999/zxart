ALTER TABLE `engine_module_zxprod` ADD `hasAiData` TINYINT NOT NULL;
CREATE TABLE `engine_module_zxprod_meta` (`id` INT NOT NULL , `metaTitle` VARCHAR(255) NOT NULL , `h1` VARCHAR(255) NOT NULL , `metaDescription` TEXT NOT NULL , `generatedDescription` TEXT NOT NULL , `languageId` INT NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `engine_module_zxprod_meta` ADD UNIQUE (`id`, `languageId`);