ALTER TABLE `engine_votes_history` ADD INDEX (`type`, `date`);
DELETE FROM `engine_votes_history` WHERE type='comment'