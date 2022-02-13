DELETE t1 FROM engine_structure_links t1
INNER JOIN engine_structure_links t2 
WHERE 
    t1.id < t2.id AND 
    t1.`parentStructureId` = t2.`parentStructureId` AND
        t1.`childStructureId` = t2.`childStructureId` AND
            t1.`type` = t2.`type`;
DELETE FROM `engine_structure_links` WHERE `parentStructureId`=92155;