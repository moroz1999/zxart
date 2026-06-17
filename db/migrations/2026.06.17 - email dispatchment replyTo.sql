-- Store an optional Reply-To address per dispatchment (e.g. feedback sender)
ALTER TABLE `engine_email_dispatchments`
    ADD COLUMN `replyTo` VARCHAR(255) NULL DEFAULT NULL AFTER `fromEmail`;
