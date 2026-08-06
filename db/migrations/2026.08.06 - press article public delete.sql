-- The article form offers deletion, so whoever may save an article may delete it.
-- Mirrors every existing `pressArticle`/`publicReceive` grant; the author of a new
-- article gets the same right on it from publicReceivePressArticle.

INSERT INTO `engine_privilege_relations` (`privilegeId`, `elementId`, `type`, `userId`, `module`, `action`)
SELECT `privilegeId`, `elementId`, `type`, `userId`, `module`, 'publicDelete'
FROM `engine_privilege_relations` AS `granted`
WHERE `module` = 'pressArticle'
  AND `action` = 'publicReceive'
  AND NOT EXISTS(
    SELECT 1
    FROM (SELECT * FROM `engine_privilege_relations`) AS `existing`
    WHERE `existing`.`module` = 'pressArticle'
      AND `existing`.`action` = 'publicDelete'
      AND `existing`.`userId` = `granted`.`userId`
      AND `existing`.`elementId` = `granted`.`elementId`
  );
