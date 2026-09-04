-- Authorship records that belong to no element: rows written under id 0 by
-- earlier imports. They describe nothing and are not reachable from any
-- author or work page.
DELETE FROM `engine_authorship`
WHERE `authorId` = 0
   OR `elementId` = 0;
