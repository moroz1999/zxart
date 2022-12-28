ALTER TABLE `engine_authorship` ADD UNIQUE (`authorId`, `elementId`); 
ALTER TABLE `engine_module_author` ADD UNIQUE (`id`, `country`, `languageId`); 