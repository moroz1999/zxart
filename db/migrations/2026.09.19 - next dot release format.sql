-- Two release formats the ZX Spectrum Next publishes programs in: `dot`, a
-- NextZXOS dot command, and `bas`, a NextBASIC program. Without them in the
-- enum the format is stored as an empty string and the release shows no
-- format at all.
--
-- The label lives on the front end, in ng-zxart's i18n files.
ALTER TABLE `engine_module_zxrelease_format`
    MODIFY `value` ENUM(
        'bin','d80','dck','dsk','fdi','mdr','mgt','rom','scl','sna','spg','szx','tap','td0',
        'trd','tzx','udi','z80','nex','snx','p','slt','opd','mbd','img','o','tar','z81','d40',
        'mld','$b','$c','sad','cpm','dot','bas'
    ) NOT NULL;

-- Releases parsed before this carry neither format: re-derive them from the
-- structure already stored, with `/fix/job:release-formats/` (`dry:1` first).
