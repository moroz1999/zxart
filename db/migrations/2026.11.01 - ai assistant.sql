-- The editors' AI assistant: conversations, their messages, agent runs and plans
-- --------------------------------------------------------------------------------
-- InnoDB, so deleting a conversation cascades to everything it holds.

CREATE TABLE IF NOT EXISTS `engine_ai_conversation`
(
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `userId`        INT UNSIGNED NOT NULL,
    `title`         VARCHAR(255) NOT NULL DEFAULT '',
    `createdAt`     INT UNSIGNED NOT NULL,
    `lastMessageAt` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user` (`userId`, `lastMessageAt`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- `seq` grows per conversation; the panel polls for "everything after seq N"
CREATE TABLE IF NOT EXISTS `engine_ai_message`
(
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversationId` INT UNSIGNED NOT NULL,
    `seq`            INT UNSIGNED NOT NULL,
    `role`           VARCHAR(16)  NOT NULL,
    `kind`           VARCHAR(16)  NOT NULL,
    `text`           MEDIUMTEXT   NOT NULL,
    `payload`        JSON         NULL,
    `createdAt`      INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `conversationSeq` (`conversationId`, `seq`),
    CONSTRAINT `ai_message_conversation` FOREIGN KEY (`conversationId`)
        REFERENCES `engine_ai_conversation` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `engine_ai_run`
(
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversationId` INT UNSIGNED NOT NULL,
    `type`           VARCHAR(16)  NOT NULL,
    `status`         VARCHAR(16)  NOT NULL,
    `steps`          INT UNSIGNED NOT NULL DEFAULT 0,
    `tokensIn`       INT UNSIGNED NOT NULL DEFAULT 0,
    `tokensOut`      INT UNSIGNED NOT NULL DEFAULT 0,
    `error`          TEXT         NULL,
    `startedAt`      INT UNSIGNED NOT NULL,
    `finishedAt`     INT UNSIGNED NULL,
    PRIMARY KEY (`id`),
    KEY `conversationStatus` (`conversationId`, `status`),
    CONSTRAINT `ai_run_conversation` FOREIGN KEY (`conversationId`)
        REFERENCES `engine_ai_conversation` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- `actions`: the proposed tool calls with their previews, and once executed,
-- each one's status, result or error
CREATE TABLE IF NOT EXISTS `engine_ai_plan`
(
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversationId` INT UNSIGNED NOT NULL,
    `title`          VARCHAR(255) NOT NULL DEFAULT '',
    `status`         VARCHAR(16)  NOT NULL,
    `actions`        JSON         NOT NULL,
    `createdAt`      INT UNSIGNED NOT NULL,
    `approvedAt`     INT UNSIGNED NULL,
    `finishedAt`     INT UNSIGNED NULL,
    PRIMARY KEY (`id`),
    KEY `conversationStatus` (`conversationId`, `status`),
    CONSTRAINT `ai_plan_conversation` FOREIGN KEY (`conversationId`)
        REFERENCES `engine_ai_conversation` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- Who may use it -----------------------------------------------------------------
-- `aiAssistant` is a site-wide privilege on the public root, like `editHardware`.
-- privilegesManager reads $user->privileges from the session, so a member sees
-- the assistant after their next login.
INSERT INTO `engine_structure_elements` (`structureType`, `structureName`, `structureRole`, `dateCreated`, `dateModified`,
                                         `marker`)
SELECT 'userGroup', 'ai-assistant-users', 'content', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ''
WHERE NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_structure_elements`) `existing`
                  WHERE `existing`.`structureType` = 'userGroup'
                    AND `existing`.`structureName` = 'ai-assistant-users');

INSERT INTO `engine_module_user_group` (`id`, `groupName`, `description`, `languageId`)
SELECT `grp`.`id`, 'AI assistant users', 'May talk to the editors'' AI assistant and approve its plans', 0
FROM `engine_structure_elements` `grp`
WHERE `grp`.`structureType` = 'userGroup'
  AND `grp`.`structureName` = 'ai-assistant-users'
  AND NOT EXISTS (SELECT 1 FROM (SELECT * FROM `engine_module_user_group`) `existing` WHERE `existing`.`id` = `grp`.`id`);

INSERT INTO `engine_structure_links` (`parentStructureId`, `childStructureId`, `type`, `position`)
SELECT `folder`.`id`, `grp`.`id`, 'structure', 0
FROM `engine_structure_elements` `grp`
         JOIN `engine_structure_elements` `folder` ON `folder`.`marker` = 'userGroups'
WHERE `grp`.`structureType` = 'userGroup'
  AND `grp`.`structureName` = 'ai-assistant-users'
  AND NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_structure_links`) `existing`
                  WHERE `existing`.`parentStructureId` = `folder`.`id`
                    AND `existing`.`childStructureId` = `grp`.`id`
                    AND `existing`.`type` = 'structure');

INSERT INTO `engine_privilege_relations` (`privilegeId`, `elementId`, `type`, `userId`, `module`, `action`)
SELECT 0, `root`.`id`, 1, `grp`.`id`, 'root', 'aiAssistant'
FROM `engine_structure_elements` `root`
         JOIN `engine_structure_elements` `grp`
              ON `grp`.`structureType` = 'userGroup' AND `grp`.`structureName` = 'ai-assistant-users'
WHERE `root`.`marker` = 'public_root'
  AND NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_privilege_relations`) `existing`
                  WHERE `existing`.`userId` = `grp`.`id`
                    AND `existing`.`elementId` = `root`.`id`
                    AND `existing`.`module` = 'root'
                    AND `existing`.`action` = 'aiAssistant');

-- the first member; skipped where the user does not exist
INSERT INTO `engine_structure_links` (`parentStructureId`, `childStructureId`, `type`, `position`)
SELECT `grp`.`id`, `usr`.`id`, 'userRelation', 0
FROM `engine_structure_elements` `grp`
         JOIN `engine_module_user` `usr` ON `usr`.`userName` = 'moroz1999'
WHERE `grp`.`structureType` = 'userGroup'
  AND `grp`.`structureName` = 'ai-assistant-users'
  AND NOT EXISTS (SELECT 1
                  FROM (SELECT * FROM `engine_structure_links`) `existing`
                  WHERE `existing`.`parentStructureId` = `grp`.`id`
                    AND `existing`.`childStructureId` = `usr`.`id`
                    AND `existing`.`type` = 'userRelation');
