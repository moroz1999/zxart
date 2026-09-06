-- Groups for the category and country management screens ------------------------
-- Categories, countries and cities are ordinary structure elements and already
-- carry a full set of per-type action privileges; the SPA screens check those
-- actions rather than a privilege of their own. (`editHardware` is the exception
-- and stays one: the hardware catalogue is a plain table, not an element.)
--
-- The existing grants sit on the **admin root**, which is what the Smarty panel
-- loads elements under. A public request compiles privileges under the public
-- root instead, so the same actions have to be granted there for the SPA to see
-- them.
--
-- Two groups are created for it and left **empty on purpose**: who belongs in
-- them is a decision for whoever runs the site, made by adding users to the
-- group, not by a migration.

-- Step 1: the groups -----------------------------------------------------------
INSERT INTO `engine_structure_elements` (`structureType`, `structureName`, `structureRole`, `dateCreated`, `dateModified`,
                                         `marker`)
SELECT 'userGroup', `needed`.`structureName`, 'content', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ''
FROM (SELECT 'zx-categories-managers' AS `structureName`
      UNION ALL
      SELECT 'countries-managers') `needed`
WHERE NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_structure_elements`) `existing`
                  WHERE `existing`.`structureType` = 'userGroup'
                    AND `existing`.`structureName` = `needed`.`structureName`);

INSERT INTO `engine_module_user_group` (`id`, `groupName`, `description`, `languageId`)
SELECT `grp`.`id`,
       IF(`grp`.`structureName` = 'countries-managers', 'Countries managers', 'ZX categories managers'),
       IF(`grp`.`structureName` = 'countries-managers',
          'May add, edit and remove countries and cities in the geo section',
          'May add, edit, move and remove production categories'),
       0
FROM `engine_structure_elements` `grp`
WHERE `grp`.`structureType` = 'userGroup'
  AND `grp`.`structureName` IN ('zx-categories-managers', 'countries-managers')
  AND NOT EXISTS (SELECT 1 FROM (SELECT * FROM `engine_module_user_group`) `existing` WHERE `existing`.`id` = `grp`.`id`);

-- the groups list is where the admin panel finds them
INSERT INTO `engine_structure_links` (`parentStructureId`, `childStructureId`, `type`, `position`)
SELECT `folder`.`id`, `grp`.`id`, 'structure', 0
FROM `engine_structure_elements` `grp`
         JOIN `engine_structure_elements` `folder` ON `folder`.`marker` = 'userGroups'
WHERE `grp`.`structureType` = 'userGroup'
  AND `grp`.`structureName` IN ('zx-categories-managers', 'countries-managers')
  AND NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_structure_links`) `existing`
                  WHERE `existing`.`parentStructureId` = `folder`.`id`
                    AND `existing`.`childStructureId` = `grp`.`id`
                    AND `existing`.`type` = 'structure');

-- Step 2: the actions each group may perform under the public root --------------
-- `receive` is the save action of these element types and `delete` is theirs
-- too, so the SPA endpoints check exactly what the Smarty forms have always
-- checked. `showFullList` on the two container types is what lets the public
-- structure manager instantiate `zxProdCategories` and `countries` at all: they
-- live under the admin root, and an element type with no privilege at the public
-- root is never manufactured there, so a top-level category or a new country
-- could not be created without it.
--
-- Note for whoever runs this: privilegesManager reads $user->privileges from the
-- session, so a user added to one of these groups sees the screen after their
-- next login.
INSERT INTO `engine_privilege_relations` (`privilegeId`, `elementId`, `type`, `userId`, `module`, `action`)
SELECT 0, `root`.`id`, 1, `grp`.`id`, `needed`.`module`, `needed`.`action`
FROM `engine_structure_elements` `root`
         JOIN `engine_structure_elements` `grp`
              ON `grp`.`structureType` = 'userGroup' AND `grp`.`structureName` = 'zx-categories-managers'
         JOIN (SELECT 'zxProdCategory' AS `module`, 'receive' AS `action`
               UNION ALL
               SELECT 'zxProdCategory', 'delete'
               UNION ALL
               SELECT 'zxProdCategories', 'showFullList') `needed`
WHERE `root`.`marker` = 'public_root'
  AND NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_privilege_relations`) `existing`
                  WHERE `existing`.`userId` = `grp`.`id`
                    AND `existing`.`elementId` = `root`.`id`
                    AND `existing`.`module` = `needed`.`module`
                    AND `existing`.`action` = `needed`.`action`);

INSERT INTO `engine_privilege_relations` (`privilegeId`, `elementId`, `type`, `userId`, `module`, `action`)
SELECT 0, `root`.`id`, 1, `grp`.`id`, `needed`.`module`, `needed`.`action`
FROM `engine_structure_elements` `root`
         JOIN `engine_structure_elements` `grp`
              ON `grp`.`structureType` = 'userGroup' AND `grp`.`structureName` = 'countries-managers'
         JOIN (SELECT 'country' AS `module`, 'receive' AS `action`
               UNION ALL
               SELECT 'country', 'delete'
               UNION ALL
               SELECT 'city', 'receive'
               UNION ALL
               SELECT 'city', 'delete'
               UNION ALL
               SELECT 'countries', 'showFullList') `needed`
WHERE `root`.`marker` = 'public_root'
  AND NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_privilege_relations`) `existing`
                  WHERE `existing`.`userId` = `grp`.`id`
                    AND `existing`.`elementId` = `root`.`id`
                    AND `existing`.`module` = `needed`.`module`
                    AND `existing`.`action` = `needed`.`action`);
