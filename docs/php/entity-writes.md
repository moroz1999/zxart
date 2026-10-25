# Entity writes

Creating and editing productions, releases, authors, author aliases, groups and
group aliases goes through one path per entity type, whoever asks for it: the
legacy edit and add forms, the batch upload, and the entity's
`<Entity>DataService`. New writes of these entities use it; nothing writes their
fields on the element directly.

## Use cases

Every change requested through a data service, and every import of these
entities, is a use case in `ZxArt\<Module>\UseCases`: a readonly
`<Action>Command` and a `<Action>Handler` with `handle(command)`. A handler

1. loads the entity, a legacy element, through `<Entity>Repository::get()`
   (`400` without an id, `404` for no element of the type) and checks the
   privilege through `<Entity>Privileges` (`403`), outside the transaction;
2. inside `ZxArt\Shared\UseCases\TransactionRunner::run()` changes the entity
   and stores it through the repository: `save()` for the element's own data,
   `touch()` for a change kept outside it (authorship, files), `create()` for a
   new element whose structure record is not stored yet, `delete()`; then logs
   the action.

`TransactionRunner` commits when the work returns and rolls back when it
throws; a run inside another becomes a savepoint, so a use case called from
another one (a release import inside a production import) rolls back alone or
with its caller. A change stopped halfway therefore leaves nothing, not even a
structure record. On the MyISAM tables of production this is a no-op until the
InnoDB migration ([../chat/innodb.md](../chat/innodb.md)).

`<Entity>DataService` is a thin facade: each method builds the command and
returns what the handler returns. `<Entity>Repository` lives in
`<Module>/Repositories/` and supplies entities; table access stays in
`<Module>/Dbal/` ([repositories.md](repositories.md)).

| Entity | Repository | Use cases |
|---|---|---|
| prod | `ProdRepository` | Create, Update, Delete, SetProdMember, DeleteProdMember, AddProdFiles, DeleteProdFile, JoinProds, ImportProd |
| release | `ReleaseRepository` | Create, Update, Delete, Clone, SetReleaseMember, DeleteReleaseMember, AddReleaseFiles, SetReleaseFile, DeleteReleaseFile, ImportRelease |
| author | `AuthorRepository` | Create, Update, Delete, ConvertAuthorToGroup, JoinAuthors, ImportAuthor, ImportAuthorLabel |
| author alias | `AuthorAliasRepository` | Create, Update, Delete, ConvertAuthorAliasToAuthor, JoinAuthorAliases, ImportAuthorAlias |
| group | `GroupRepository` | Create, Update, Delete, ConvertGroupToAuthor, JoinGroups, SetGroupMember, DeleteGroupMember, ImportGroup |
| group alias | `GroupAliasRepository` | Create, Update, Delete, ConvertGroupAliasToGroup, JoinGroupAliases, SetGroupAliasMember, DeleteGroupAliasMember |

The imports keep their own merge rules (`Imported<Entity>Merger`); see
[../features/imported-entities.md](../features/imported-entities.md#import-use-cases).

## Pieces per entity

| Piece | Job |
|---|---|
| `<Entity>FieldsDto` | The editable fields. `null` leaves a field untouched; an empty string or list clears it; 0 clears a reference or a date (dates are timestamps). |
| `<Entity>FieldsWriter::apply()` | Checks every reference, then writes the given fields and what follows from them, and saves through `<Entity>Repository::save()`. Checks no privilege and logs nothing. A wrong reference is a `400` of the entity's `<Entity>DataException`, thrown before anything is written. |
| `<Entity>Creator` | `create()` makes the element where it belongs (`<Entity>Repository::create()`; `500` when it cannot be made) and calls `completeCreation()`, which runs the writer and grants the creator the actions on it. A legacy add form creates its own element and calls `completeCreation()` itself. The caller refreshes the user's privileges once done creating. |
| `<Entity>FieldsDtoFactory::fromSubmittedForm()` | Turns a validated legacy form into the DTO. |
| `Create<Entity>Handler` / `Update<Entity>Handler` | Privilege check, title rules, one transaction, actions log. Reached through `<Entity>DataService::create()` / `update()`, the only entry point for anything other than the legacy forms. |

What follows from the fields, per type:

- **prod** — party link, tags, a missing year, categories (and the *misc*
  fallback), the AI queue; created under its first category.
- **release** — the production it belongs to (another one moves it; the move
  also needs `zxRelease.publicAdd` on the target); created under its production.
  A clone is created the same way, from the source's fields
  (`ReleaseCloner`).
- **author** — a blank title is the real name; a rename moves it to its letter;
  ratings are recalculated; changed conversion defaults reconvert its tunes.
- **author alias / group** — a rename moves it to its letter; a group also
  follows its subgroup list and is recalculated.
- **group alias** — nothing beyond its fields.
- **author alias / group alias** — moved to another author or group, both the
  old and the new owner read their aliases again.

Privileges `create()` checks: `zxProdsUploadForm.batchUpload` and
`author.publicAdd` / `group.publicAdd` on the root, `zxRelease.publicAdd` on the
production, `authorAlias.publicAdd` on the author, `groupAlias.publicAdd` on the
group. `update()` checks `publicReceive` on the element, and a release moved to
another production `zxRelease.publicAdd` there. The CMS works an element's
privileges out as it loads it, so each check follows a load of that element —
the root, the author, the group or the production — which also rejects with a
`400` an id of another type.

## Authors and members

`ProdDataService::setMember()` / `ReleaseDataService::setMember()` add an author
or an author alias to the element's authors, or make its roles exactly the given
ones; `GroupDataService::setMember()` / `GroupAliasDataService::setMember()` do
the same for the members of a group or a group alias and also take the start
and end date (`null` keeps the stored one, 0 clears it). An empty role list is
stored as `unknown`. They check `publicReceive` on the element, reject with a
`400` a member that is no author or alias and a role outside
`ZxArt\Authors\AuthorshipRoles::LIST` (productions, releases) or
`ZxArt\Groups\GroupMemberRoles::LIST` (groups and their aliases), and log
`publicReceive`. `deleteMember()` removes one; it checks `deleteAuthor`.

The records live in `authorship` (`AuthorshipDbal::setAuthorship()`), not
on the element, so `setMember()` and `deleteMember()` store a new modification
time on the element itself (`structureElement::persistModificationTime()`).

## Joins

`<Entity>DataService::join()` of prod, author, author alias, group and group
alias merges another element of the same type into the given one and returns
its id. It checks `join` on both elements, rejects joining an element into
itself (`400`) and an id of another type (`404`), logs `join` on the surviving
element, then calls the method the legacy `join` action calls:

- **prod** — `ProdsService::joinDeleteZxProd()`: releases and materials move,
  and unless `releasesOnly` also authors, groups, publishers, categories,
  privileges and the fields the main one lacks; the joined prod is deleted
  either way.
- **author** — `AuthorsService::joinDeleteAuthor()`, or with `asAlias`
  `joinAuthorAsAlias()`, which makes a new alias of the main author named as the
  joined one and moves the works onto it; aliases, works, memberships, import
  origins and claims move, then the author is recalculated.
- **group** — `GroupsService::joinDeleteGroup()` / `joinGroupAsAlias()`: the
  same for members, productions and aliases.
- **author alias / group alias** — `joinDeleteAuthor()` / `joinDeleteGroup()`
  with the two alias ids.

The joined element's aliases are read afresh (`resetAliasElements()`): an alias
added in the same request is otherwise missed.

What a join moves can be counted beforehand: `ReleasesDbal::countByProdId()`,
`AuthorProdsDbal::countByAuthorId()`, `PicturesDbal` and
`TunesDbal::countByAuthorId()`,
`AuthorCollaboratorsDbal::countGroupMemberships()`,
`GroupCollaboratorsDbal::countMembers()` and `GroupProdsDbal::count()`.

## Reads

| Piece | Job |
|---|---|
| `<Entity>FieldsReader::read()` | The element's editable fields as the `<Entity>FieldsDto` the writer takes, every field set: text decoded, an empty list `[]`, a reference or a date not set 0, an unknown stored type or legal status `null`. Reading back what a create or an update wrote gives the same DTO, in the same process. |
| `<Entity>StateService::get()` | `<Entity>StateDto`: the id, the modification time (`getModifiedTimestamp()`), the fields, and what the entity is linked with. An id of another type is a `404` of the entity's `<Entity>DataException`. Checks no privilege beyond what loading the element checks. |

What a state holds beyond the fields:

- **prod** — its authors (`AuthorshipDto`: author, roles, dates), its
  release ids, and its file lists;
- **release** — its authors, its file name and size, and its file lists;
- **author** — its alias ids, its group memberships, and the counters the
  author page shows (`AuthorDetailsService::getCounters()`);
- **author alias** — its group memberships and counters;
- **group** — its members, its parent group ids (read from the links), its
  alias ids, and the counters the group page shows
  (`GroupDetailsService::getCounters()`);
- **group alias** — its members and counters.

Every state also carries the entity's import origins (`ImportOriginDto`:
portal and the entity's id there, `ImportOriginsReader`), ordered by portal
and id. The file lists of a prod or a release (`AttachedFilesReader`) are keyed
by kind (`ProdFileKind` / `ReleaseFileKind` values), each an `AttachedFileDto`
list (file element id, decoded file name) in the order the files were added;
the ids are read from the links, since the element's own list holds only what
it loaded or was given since. A release's file size is `null` when it has no
file or the file is not stored on this server.

`AuthorshipDbal::findByElement()` / `findByAuthor()` read the authorship
records of one type as stored: roles decoded (an empty column is `[]`), dates
as timestamps.

Some fields read stale in the process that wrote them, and the readers go
around it: a value-set field (hardware, languages, release formats) is read
from its stored value (`ElementFields::strings()`), a date field from its
timestamp (`ElementFields::timestamp()`), sub-groups, parent groups and file
lists from the links. See
[features/imported-entities.md](../features/imported-entities.md#known-defects-of-the-production-import)
for what the stale value-set display does to an import.

## Shared pieces

- `ZxArt\Shared\References\EntityReferenceResolver` loads referenced elements of
  the allowed classes and checks hardware and language codes, throwing
  `InvalidReferenceException`; each writer turns it into its own `400`.
  `ReferenceTypes` holds the class sets several entities share (publishers,
  groups).
- `ZxArt\Shared\Elements\ElementFields::assign()` writes the non-null values
  onto the element, flags as checkbox 0/1. Its readers (`text()`, `number()`,
  `flag()`, `ids()`, `strings()`, `timestamp()`) take a field by name in the
  type it is stored in.
- `ZxArt\Shared\Elements\CreatorPrivilegesGranter` grants the current user a
  list of `[structure type, action]` on a new element.
- `ZxArt\Forms\SubmittedFormValues` reads a validated legacy form's storage in
  the field's type: a field the action expects is read even when empty (the
  form is the whole state of its fields), a field it does not expect is `null`.
  A per-language field (the author's real name) is not read through it: the
  form has already put each language's value on the element, and the factory
  leaves it `null`.
- `ZxArt\Shared\LanguageCodes::ALL` is the list of language codes.

## What stays in the legacy actions

Only what is the form's own: authorship and memberships from the `add*` fields,
import origins, uploaded images and files, staged uploads, and the batch
upload's releases from the uploaded files.

## Tests

Writers and creators are tested against element spies in
`tests/Doubles/Elements/` (`ElementSpyTrait`): assigned fields land in
`$values`, persisting steps in `$calls`, so a test asserts what was written and
in which order without the CMS data layer.
