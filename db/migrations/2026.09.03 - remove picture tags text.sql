-- Tags live only as links to tag elements; the comma-separated string is no longer read or written.
-- A handful of pictures hold a string that never became links (ids 12271, 53645, 53646 in the
-- August dump); those tags are lost with the column and have to be re-entered on the picture form.
ALTER TABLE `engine_module_zxpicture`
    DROP COLUMN `tagsText`;
-