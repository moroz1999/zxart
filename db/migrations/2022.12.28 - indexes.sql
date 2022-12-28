

DELETE t1 FROM engine_structure_links t1
INNER JOIN engine_structure_links t2 
WHERE 
    t1.id < t2.id AND 
    t1.`parentStructureId` = t2.parentStructureId AND
    t1.`type` = t2.type AND
    t1.childStructureId = t2.childStructureId;

ALTER TABLE `engine_structure_links` ADD UNIQUE `ZxProdsList:getCountriesSelector` (`type`, `childStructureId`, `parentStructureId`);
