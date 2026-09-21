-- Importing a production's screenshots from Spectrum Computing is granted to the
-- catalogues managers. The SPA compiles privileges under the public root, so the
-- grant sits there.
--
-- privilegesManager reads $user->privileges from the session, so a member of the
-- group sees the action after their next login.

INSERT INTO `engine_privilege_relations` (`privilegeId`, `elementId`, `type`, `userId`, `module`, `action`)
SELECT 0, `root`.`id`, 1, `grp`.`id`, 'zxProd', 'importScScreenshots'
FROM `engine_structure_elements` `root`
         JOIN `engine_structure_elements` `grp`
              ON `grp`.`structureType` = 'userGroup' AND `grp`.`structureName` = 'catalogues-managers'
WHERE `root`.`marker` = 'public_root'
  AND NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_privilege_relations`) `existing`
                  WHERE `existing`.`userId` = `grp`.`id`
                    AND `existing`.`elementId` = `root`.`id`
                    AND `existing`.`module` = 'zxProd'
                    AND `existing`.`action` = 'importScScreenshots');
