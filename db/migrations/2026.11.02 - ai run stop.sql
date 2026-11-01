-- The user may ask a running assistant run to stop; the run checks the flag
-- before each step.
ALTER TABLE `engine_ai_run`
    ADD COLUMN IF NOT EXISTS `stopRequested` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;
