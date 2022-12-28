ALTER TABLE `engine_module_zxprod` CHANGE `partyplace` `partyplace` SMALLINT(11) NOT NULL;
ALTER TABLE `engine_module_zxprod` CHANGE `year` `year` SMALLINT(11) NOT NULL; 


ALTER TABLE `engine_module_group` ADD UNIQUE `ZxProdsList:getCountriesSelector` (`id`, `country`); 