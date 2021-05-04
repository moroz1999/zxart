RENAME TABLE `engine_module_zxprod_language` TO `engine_zxitem_language`; 

INSERT IGNORE INTO `engine_zxitem_language`(`elementId`, `value`) SELECT `elementId`, `value` FROM `engine_module_zxrelease_language`;

DROP TABLE `engine_module_zxrelease_language`;