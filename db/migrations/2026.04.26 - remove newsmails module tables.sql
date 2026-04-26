-- Drop tables for removed newsmails-related homepage modules.

DROP TABLE IF EXISTS
    engine_module_newsmailaddress,
    engine_module_newsmailshistory,
    engine_module_newsmailstext,
    engine_module_newsmailtext_subcontent;
