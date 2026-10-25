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
`ImportIdOperator` takes the enum directly, as do the import use cases and
`CountriesManager`. Codes arriving as strings
(an import payload's `ids`/`importIds` map, a split form's link key) are resolved
with `ImportOrigin::tryFrom()` and skipped when they name no known portal.

## Elements

An element whose portal ids are editable implements
`ZxArt\Import\ImportOriginsHolder` and uses `ImportOriginsHolderTrait`:
`zxProdElement`, `zxReleaseElement`, and — through `AuthorTrait` and the `Group`
trait — `authorElement`, `authorAliasElement`, `groupElement`,
`groupAliasElement`. The trait reads and writes through
`ImportOriginsDbal`; `getImportEntityType()` names the `type` new rows are
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

## Import use cases

Each import of one entity is a use case
([../php/entity-writes.md](../php/entity-writes.md#use-cases)), run in one
transaction:

| Use case | Entry point | Merges with |
|---|---|---|
| `ImportProdHandler` (`ZxArt\Prods\UseCases`) | `ProdsService::importProd()` | `ImportedProdMerger` |
| `ImportReleaseHandler` (`ZxArt\Releases\UseCases`) | run by `ImportProdHandler` for each release | `ImportedReleaseMerger` |
| `ImportAuthorHandler`, `ImportAuthorLabelHandler`, `ImportAuthorAliasHandler` (`ZxArt\Authors\UseCases`) | `AuthorImportService::importAuthor()`, `importLabel()`, `importAuthorAlias()` | `ImportedAuthorMerger`, `ImportedAuthorAliasMerger` |
| `ImportGroupHandler` (`ZxArt\Groups\UseCases`) | the handler itself | `ImportedGroupMerger`, `ImportedGroupAliasMerger` |

A handler finds the element the DTO or label stands for — by this portal's id,
then another portal's, then by name — or creates it, and hands it to its
merger. A merger fills what is empty, overwrites only what the import's policy
allows (`ProdImportPolicy`, `AuthorImportPolicy`), and saves through the
entity's repository when anything changed. The production import imports the
labels a production names first (`ImportedLabelsImporter`), each one an author,
alias or group import of its own inside the production's transaction.

`ProdsService` and `AuthorImportService` keep the policy an import source sets
once through their `set*()` methods, and build the command with it for every
import after. `ImportedAuthorsFinder` remembers the authors and aliases a run
has imported, under portal and id; the run stops at the first failure, so it
never reads an element whose import was rolled back.

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
The production's import is one transaction, so the failed entry leaves nothing
behind: no half-imported production, release, author or group.

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

## Known defects of the author import

Held as they are by the import golden master
([../testing.md](../testing.md)); a fix changes the snapshots on purpose.

- **An alias is looked up as an alias type that is never stored.**
  `ImportedAuthorAliasMerger::merge()` looks the alias's author up by
  `label->authorId` with `EntityType::AuthorAlias`, and
  `ImportedAuthorsFinder::getAuthorAliasByLabel()` looks the alias itself up
  the same way. Both
  families are stored as `author`, so neither ever matches: an imported alias
  never gets its author, and a re-imported alias is found only by name — or
  created again, the portal id moving to the copy.
- **A newly created author is not returned.** When `ImportAuthorHandler`
  finds no author for the label, it creates one but returns `null` and
  remembers `null` in `ImportedAuthorsFinder` under the label's id. A
  re-import of the label in the same run still finds the author, by its portal
  id; but a caller that uses the result loses the new author: the press article
  import (`ArticleParsedDataUpdater`) leaves it out of the article's authors.
- **A label with neither name nor real name matches an arbitrary author.**
  `AuthorsDbal::findAuthorIdsByName('')` compares `title`/`realName` with
  `LIKE ''`, and `LabelResolver::resolveEntity()` drops an author candidate only
  at a score of exactly `0` (alias candidates at `<= 0`), so a negative score
  still wins; the author found gets the portal id.

The intended rules:

- a new author is returned and remembered like a found one;

- the alias's author is looked up as an author, and only an `authorElement`
  is accepted;
- the alias itself is looked up by its portal id under `author` and accepted
  when it is an `authorAliasElement`; what ZxDB lists as a pseudonym is
  created as an alias;
- when the element under that id is an author — an alias an editor converted
  (`convertAliasToAuthor()` moves the portal ids to the new author) — the
  import resolves to that author and creates no alias;
- an empty label resolves to nothing: the resolver finds no candidates for it,
  and the import skips it with a logged error naming the portal and the id,
  without stopping the run;
- an author candidate with a score `<= 0` is dropped, as an alias candidate
  is.

## Known defects of the production import

Held as they are by the import golden master; a fix changes the snapshots on
purpose.

- **A value-set field reads stale after it is assigned.** `DBValueSet`
  (`hardwareRequired`, `language`) keeps its display value when the element
  sets a new stored value, so in the process that wrote the field it still
  reads as before. A re-import of a production in the same process therefore
  sees these fields empty and fills them again: the snapshot of a re-import
  that fills only empty fields holds the re-imported hardware, not the one the
  first import stored, and the forced re-import holds a different language
  than a fresh read would give. Converting the stored value to the display
  value in `DBValueSet::setExternalValue()` fixes the read and changes both
  snapshots. Writers' readers avoid it by reading the stored value
  (`ElementFields::strings()`).
