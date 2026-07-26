UPDATE `engine_module_translation`
SET `valueText` = CASE `languageId`
    WHEN 930 THEN 'Ссылка на SpeccyWiki'
    WHEN 2105 THEN 'SpeccyWiki link'
    WHEN 84102 THEN 'Enlace de SpeccyWiki'
END
WHERE `id` = 87584
  AND `languageId` IN (930, 2105, 84102);

UPDATE `engine_module_translation`
SET `valueText` = CASE `languageId`
    WHEN 930 THEN 'Ссылка на соответствующую статью SpeccyWiki'
    WHEN 2105 THEN 'Link to the relevant SpeccyWiki article'
    WHEN 84102 THEN 'Enlace al artículo correspondiente de SpeccyWiki'
END
WHERE `id` = 52299
  AND `languageId` IN (930, 2105, 84102);

UPDATE `engine_module_translation`
SET `valueText` = CASE `languageId`
    WHEN 930 THEN 'Ссылка на SpeccyWiki'
    WHEN 2105 THEN 'SpeccyWiki link'
    WHEN 84102 THEN 'Enlace de SpeccyWiki'
END
WHERE `id` = 88935
  AND `languageId` IN (930, 2105, 84102);
