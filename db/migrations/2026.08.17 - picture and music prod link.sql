-- The software a picture or a tune belongs to.
--
-- The column was called `game` because the archive only held games when it was
-- added, and music was only ever attached to one. It has held any production
-- or release for years — a demo, a magazine issue, a utility — so the name is
-- renamed to match what it stores.
--
-- The `gameLink` link type keeps its name: renaming it would rewrite every row
-- of `structure_links` for no gain.

ALTER TABLE `engine_module_zxpicture` CHANGE `game` `prod` INT(11) NOT NULL;
ALTER TABLE `engine_module_zxmusic` CHANGE `game` `prod` INT(11) NOT NULL;
