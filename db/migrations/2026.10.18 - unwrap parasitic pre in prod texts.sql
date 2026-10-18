-- A form chunk used to render plain descriptions inside <pre>…</pre>, and that
-- rendering was saved into productions as their text, sometimes twice over
-- (<pre></pre><pre>…</pre>). A text made of nothing but <pre> blocks is plain:
-- the blocks are unwrapped (empty ones dropped, adjacent ones joined by a line
-- break, the line break a block opens with ignored as browsers do) and the text is
-- switched to plain format, which shows it on the same fixed-width grid. Texts
-- with any other markup, and <pre> blocks inside formatted content, are left
-- alone.
UPDATE `engine_module_zxprod`
SET `description`     = REGEXP_REPLACE(
        REGEXP_REPLACE(REPLACE(`description`, '<pre></pre>', ''), '</pre>\\s*<pre>\\n?', '\n'),
        '^<pre>\\n?|</pre>\\s*$', ''),
    `htmlDescription` = 0
WHERE `description` REGEXP '^<pre>'
  AND `description` REGEXP '</pre>\\s*$'
  AND REGEXP_REPLACE(`description`, '</?pre>', '') NOT REGEXP '<[a-zA-Z/!]';

UPDATE `engine_module_zxprod`
SET `instructions`     = REGEXP_REPLACE(
        REGEXP_REPLACE(REPLACE(`instructions`, '<pre></pre>', ''), '</pre>\\s*<pre>\\n?', '\n'),
        '^<pre>\\n?|</pre>\\s*$', ''),
    `htmlInstructions` = 0
WHERE `instructions` REGEXP '^<pre>'
  AND `instructions` REGEXP '</pre>\\s*$'
  AND REGEXP_REPLACE(`instructions`, '</?pre>', '') NOT REGEXP '<[a-zA-Z/!]';
