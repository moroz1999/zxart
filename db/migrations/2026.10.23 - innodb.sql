-- Every table moves to InnoDB --------------------------------------------------
-- MyISAM ignores transactions, so a change spread over several tables cannot be
-- rolled back as a whole. InnoDB gives the AI assistant's plans and the
-- integration tests real transactions.
--
-- The tables are taken from the live schema, so the statement converts whatever
-- is still MyISAM on the server it runs on and can be run again safely.
-- Changing the engine copies the table and blocks writes to it meanwhile:
-- run it in a quiet hour. `engine_structure_links` takes the longest.
--
-- Needs MariaDB 10.3+ (anonymous block with FOR loop and EXECUTE IMMEDIATE).
-- In a client that splits statements on `;` (mariadb CLI, phpMyAdmin), keep the
-- DELIMITER lines.

-- The one table without any key: a user's value of an additional form field,
-- read and written per user and field.
ALTER IGNORE TABLE `engine_module_user_additional_data`
    ADD PRIMARY KEY (`userId`, `fieldId`);

DELIMITER //
BEGIN NOT ATOMIC
    FOR myisamTable IN (
        SELECT `TABLE_NAME` AS `name`
        FROM `information_schema`.`TABLES`
        WHERE `TABLE_SCHEMA` = DATABASE()
          AND `TABLE_TYPE` = 'BASE TABLE'
          AND `ENGINE` = 'MyISAM'
        ORDER BY `DATA_LENGTH` + `INDEX_LENGTH`
    )
    DO
        EXECUTE IMMEDIATE CONCAT('ALTER TABLE `', myisamTable.name, '` ENGINE=InnoDB');
    END FOR;
END //
DELIMITER ;
