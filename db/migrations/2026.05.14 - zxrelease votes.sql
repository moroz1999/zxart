ALTER TABLE `engine_module_zxrelease`
    ADD `votes` FLOAT NOT NULL DEFAULT '0' AFTER `plays`,
    ADD `votesAmount` INT NOT NULL DEFAULT '0' AFTER `votes`;
