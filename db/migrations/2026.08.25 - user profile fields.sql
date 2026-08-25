-- Accounts keep only the fields the registration form collects:
-- userName, email and password. The optional profile fields are dropped.
-- `website` is the only one that ever received data (it was filled by the old
-- registration form's "www" field and never displayed anywhere).
ALTER TABLE engine_module_user
    DROP COLUMN company,
    DROP COLUMN firstName,
    DROP COLUMN lastName,
    DROP COLUMN address,
    DROP COLUMN city,
    DROP COLUMN postIndex,
    DROP COLUMN country,
    DROP COLUMN phone,
    DROP COLUMN website;

-- The orphaned "www" registration field: no form links to it and its
-- autocomplete role no longer exists.
DELETE FROM engine_module_form_field WHERE id = 192219;
DELETE FROM engine_structure_links WHERE childStructureId = 192219;
DELETE FROM engine_structure_elements WHERE id = 192219;
