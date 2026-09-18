-- Label for the LCE picture format (field.format_lce).
INSERT INTO `engine_structure_elements` (`structureType`, `structureName`, `structureRole`, `dateCreated`, `dateModified`, `marker`)
VALUES ('translation', 'format_lce', 'content', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '');

SET @lceTranslationId = LAST_INSERT_ID();
SET @lcePosition = (SELECT MAX(`position`) + 10 FROM `engine_structure_links` WHERE `parentStructureId` = 11829);

-- 11829 is the `field` translations group.
INSERT INTO `engine_structure_links` (`parentStructureId`, `childStructureId`, `type`, `position`)
VALUES (11829, @lceTranslationId, 'structure', @lcePosition);

INSERT INTO `engine_module_translation` (`id`, `valueText`, `languageId`, `valueType`, `valueTextarea`, `valueHtml`)
VALUES
    (@lceTranslationId, 'LCE (чересстрочный гигаскрин)', 930, 'text', '', ''),
    (@lceTranslationId, 'LCE (interlaced gigascreen)', 2105, 'text', '', ''),
    (@lceTranslationId, 'LCE (gigascreen entrelazado)', 84102, 'text', '', '');
