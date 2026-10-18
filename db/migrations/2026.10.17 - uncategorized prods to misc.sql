-- Productions that lost every category have no page. The ZXDB import used to
-- drop the category a production was created in when the source genre mapped
-- to none of the site's categories. They go to the miscellaneous category.
INSERT INTO `engine_structure_links` (`parentStructureId`, `childStructureId`, `type`, `position`)
SELECT 92188, `prod`.`id`, 'zxProdCategory', 0
FROM `engine_module_zxprod` `prod`
WHERE NOT EXISTS (SELECT 1
                  FROM `engine_structure_links` `category`
                  WHERE `category`.`childStructureId` = `prod`.`id`
                    AND `category`.`type` = 'zxProdCategory');
