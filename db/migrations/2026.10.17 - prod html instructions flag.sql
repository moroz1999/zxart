-- A production's instructions get their own format flag next to the
-- description's. Instructions have always been shown as HTML, so existing ones
-- keep that format.
ALTER TABLE `engine_module_zxprod`
    ADD `htmlInstructions` tinyint(4) NOT NULL DEFAULT 1 AFTER `htmlDescription`;
