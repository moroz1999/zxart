-- Hardware on the production.
--
-- Until now only releases carried hardware and a prod derived its own by
-- aggregating them. From here the shared set lives on the prod and a release
-- keeps only what is specific to it, so the same codes stop being repeated on
-- every release of a production.
--
-- Requires `2026.08.08 - hardware catalog.sql`: the link column is a catalog id,
-- exactly like the release one.
--
-- The data itself is moved by the one-off job `/fix/job:prod-hardware-migrate/`,
-- which must be run together with the import rerouting it ships with — see the
-- plan's D.4 and D.7.

CREATE TABLE `engine_module_zxprod_hw_required`
(
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `elementId`  INT          NOT NULL,
    `hardwareId` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `elementId` (`elementId`, `hardwareId`),
    KEY `hardwareId` (`hardwareId`),
    CONSTRAINT `zxprod_hw_hardware` FOREIGN KEY (`hardwareId`) REFERENCES `engine_hardware` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
