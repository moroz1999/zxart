INSERT INTO `engine_module_zxrelease_hw_required`(`elementId`, `value`) SELECT `elementId`, `value` FROM `engine_module_zxrelease_hw_optional`;
DROP TABLE `engine_module_zxrelease_hw_optional`;