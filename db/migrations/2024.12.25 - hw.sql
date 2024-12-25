CREATE TABLE `engine_hardware_storage`
(
    `id`          int(20) UNSIGNED                NOT NULL,
    `hardware_id` varchar(30) COLLATE utf8mb4_bin NOT NULL,
    `article_id`   int(11) UNSIGNED                NOT NULL,
    `json`        text COLLATE utf8mb4_bin        NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;

ALTER TABLE `engine_hardware_storage`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `engine_hardware_storage`
    MODIFY `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT;