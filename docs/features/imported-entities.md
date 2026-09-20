# Imported entities — implementation

Domain rules: [../domain/imported-entities.md](../domain/imported-entities.md).

## Storage

One row per portal identifier in `import_origin` (`DatabaseTable::ImportOrigin`):
`elementId`, `importOrigin`, `importId`, `type`. `type` is the family the id
belongs to (`author` for authors and their aliases, `group` for groups and
theirs, `prod`, `release`, `category`, `country`), so an alias records the type
of the entity it stands for. `(importOrigin, importId, type)` is unique: one id
of one portal describes one element of a type.

## Portals

`ZxArt\Import\ImportOrigin` is the closed set of portals and the source of both
the stored code and the name offered in forms (`title()`). Importers declare
which portal they speak for (`protected ImportOrigin $origin`), and
`ImportIdOperator` takes the enum directly, as do `AuthorsService`,
`GroupsService`, `ProdsService` and `CountriesManager`. Codes arriving as strings
(an import payload's `ids`/`importIds` map, a split form's link key) are resolved
with `ImportOrigin::tryFrom()` and skipped when they name no known portal.

## Elements

An element whose portal ids are editable implements
`ZxArt\Import\ImportOriginsHolder` and uses `ImportOriginsHolderTrait`:
`zxProdElement`, `zxReleaseElement`, and — through `AuthorTrait` and the `Group`
trait — `authorElement`, `authorAliasElement`, `groupElement`,
`groupAliasElement`. The trait reads and writes through
`ImportOriginsRepository`; `getImportEntityType()` names the `type` new rows are
written under.

Each of these elements declares an `importOrigins` array field in its
`$moduleStructure`. It holds nothing in the database — it only carries the
submitted rows to `persistImportOrigins()`, which the `publicReceive` and
`publicAdd` actions call after `persistElementData()` and which lists it in
`setExpectedFields()`.

`persistImportOrigins()` treats the submission as the whole list: rows that are
new are saved, stored rows the form no longer carries are deleted, and a row
whose portal id already belongs to another element of that type is moved to this
one. A field absent from the request therefore clears every id — the same
contract the authorship fields use, and the reason every edit form always sends
the editor's output.

`getLinksInfo()` on each element decides which portals actually produce a link
for that entity and how the URL is built from the id; entities that deny 3A
(`is3aDenied()`) leave zxaaa.net (and, for productions, World Of Sam) out.

## Form data

`/formdata/` returns the stored pairs as `importOrigins`
(`[{origin, importId}]`) and the portal options as `enums['importOrigins']`
(`getImportOriginOptions()`, registered in `Formdata::enumSpecs()` for the six
element types). Both are empty for entities that carry no portal ids.

## Frontend

`ZxImportOriginsEditorComponent` (`shared/ui/zx-import-origins-editor/`) is the
single editor, used by the author, author alias, group (and group alias), prod
and release edit pages. It renders one row per id — a `zx-select` of portals and
a text input for the id — plus an add button, and emits the whole list keyed by
row index on every change. The page stores that map and submits it as the
`importOrigins` form field. It is not shown on the batch upload form, and on the
author alias form only when editing an existing alias.

## ZxDB author roles

ZxDB keeps credits and roles in two tables: `authors` names who worked on an
entry, `roles` adds one row per role that author is credited with. Most credits
carry no role row at all, so such an author is imported with an empty role list
— the role is simply unknown, not missing data. An author credited with several
roles produces several rows, and every one of them is imported.

`ZxArt\Import\ZxdbRoleType` holds the closed set of role codes ZxDB can emit
(its `roletypes` table, which `roles.roletype_id` references) and the ZxArt
authorship role each code maps to. A code outside that set means ZxDB gained a
role type this import does not know: `ZxdbImport` logs it and aborts, instead of
dropping the credit silently, as the Pouet import does for unknown roles and
categories.

## When an import fails

The ZxDB import prints every production to its browser log *before* importing it
(`prod <counter>/<total> <importId> <title> importing`), so the last line on
screen always names the entry being worked on. Anything thrown while importing
one production is printed with its class, message, file, line and stack trace,
written to the error log, and stops the run; the resume counter is not advanced,
so the next run starts again at the same entry once the data is repaired.

That only helps if the exceptions say what is broken, so the import path names
the entity instead of failing bare:

- authorship whose stored `roles` cannot be read names the element, the author
  and the stored value (an empty string is the legacy way of saying "no roles"
  and is read as such);
- an import id pointing at an author element the structure cannot load — a
  half-created author — names the author element, the portal id and the
  production that reached it;
- an author that cannot be created names the author and what was missing (the
  authors section, its letter, or the element itself).
