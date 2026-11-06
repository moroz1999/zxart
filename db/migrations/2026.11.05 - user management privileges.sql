-- User management in the SPA (/manage/users, /manage/user-groups) -------------
-- The existing `user managers` group keeps its members; this only grants it the
-- actions the SPA screens check, on the **public root** — a public request
-- compiles privileges under the public root, while the group's existing grants
-- sit on the admin root, which the Smarty panel loads elements under.
--
-- - `root.manageUsers` — the users list, the dashboard, a user's details and
--   form, the groups a user belongs to. A privilege of its own: `user.receive`
--   and `users.showFullList` are already granted to every visitor on the public
--   root (profile editing), so they cannot gate the screens.
-- - `user.ban` — the action the admin panel's ban has always been gated on.
-- - `userGroup.showForm` — the default action of a group: the public structure
--   manager does not make a group element for a user without it.
-- - `userGroup.receive`, `userGroup.delete` — saving and deleting groups.
-- - `userGroups.showFullList` — lets the public structure manager load the
--   groups folder, which lives under the admin root, so a group can be created
--   in it.
--
-- privilegesManager reads $user->privileges from the session, so a member sees
-- the screens after their next login.
INSERT INTO `engine_privilege_relations` (`privilegeId`, `elementId`, `type`, `userId`, `module`, `action`)
SELECT 0, `root`.`id`, 1, `grp`.`id`, `needed`.`module`, `needed`.`action`
FROM `engine_structure_elements` `root`
         JOIN `engine_module_user_group` `grp` ON `grp`.`groupName` = 'user managers'
         JOIN (SELECT 'root' AS `module`, 'manageUsers' AS `action`
               UNION ALL
               SELECT 'user', 'ban'
               UNION ALL
               SELECT 'userGroup', 'showForm'
               UNION ALL
               SELECT 'userGroup', 'receive'
               UNION ALL
               SELECT 'userGroup', 'delete'
               UNION ALL
               SELECT 'userGroups', 'showFullList') `needed`
WHERE `root`.`marker` = 'public_root'
  AND NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_privilege_relations`) `existing`
                  WHERE `existing`.`userId` = `grp`.`id`
                    AND `existing`.`elementId` = `root`.`id`
                    AND `existing`.`module` = `needed`.`module`
                    AND `existing`.`action` = `needed`.`action`);
