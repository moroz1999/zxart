-- Japanese as a language a production or release can be in.
-- `ja` is appended to the end of the enum on purpose: appending is an in-place
-- change, while inserting in the middle rebuilds the whole table. The order here
-- carries no meaning — the selector is ordered by LanguageCodesProviderTrait.

ALTER TABLE `engine_zxitem_language`
    MODIFY `value` ENUM(
        'be','bs','by','ca','cs','da','de','el','en','eo','es','eu','fi','fr','gl',
        'hr','hu','is','it','la','lt','lv','m-','nl','no','pl','pt','ro','ru','sh',
        'sk','sl','sr','sv','tr','ua','he','ja'
    ) NOT NULL;
