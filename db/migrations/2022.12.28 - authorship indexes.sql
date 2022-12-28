ALTER TABLE `engine_authorship` ADD UNIQUE (`elementId`, `authorId`); 
ALTER TABLE `engine_module_author` ADD UNIQUE (`id`, `country`); 