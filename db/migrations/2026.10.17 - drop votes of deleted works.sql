-- A deleted work takes its votes with it; votes left behind by works deleted
-- before that are removed.
DELETE `vote`
FROM `engine_votes_history` `vote`
         LEFT JOIN `engine_structure_elements` `element` ON `element`.`id` = `vote`.`elementId`
WHERE `element`.`id` IS NULL;
