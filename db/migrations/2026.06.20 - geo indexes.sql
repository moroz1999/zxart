ALTER TABLE `engine_module_author`
  ADD INDEX `geo_author_language_country` (`languageId`, `country`),
  ADD INDEX `geo_author_language_city` (`languageId`, `city`);

ALTER TABLE `engine_module_group`
  ADD INDEX `geo_group_country` (`country`);

ALTER TABLE `engine_module_party`
  ADD INDEX `geo_party_country` (`country`);

ALTER TABLE `engine_module_country`
  ADD INDEX `geo_country_language_coordinates` (`languageId`, `latitude`, `longitude`);

ALTER TABLE `engine_module_city`
  ADD INDEX `geo_city_language_coordinates` (`languageId`, `latitude`, `longitude`);
