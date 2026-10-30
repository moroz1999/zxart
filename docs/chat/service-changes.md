# AI assistant — changes to existing services

Overview: [README.md](README.md). The tools these serve: [mcp-tools.md](mcp-tools.md).

The tools are thin adapters; the work is in domain services. The writes of the
six entity types are use cases behind `<Entity>DataService`, which the SPA (over
the data endpoints), the assistant and the imports all call — **one code path**.
The rule for every item below: extend the existing `<Entity>DataService` and the
existing repositories where they are good, and delete a legacy action once
nothing calls it.

The `<Entity>DataService` contract stays as it is
([rest-api.md](../php/rest-api.md)): it loads its own element class, checks the
privilege of the matching legacy action through `privilegesManager`, and logs to
the actions log.

## 1. Create and update as services

Done, see [php/entity-writes.md](../php/entity-writes.md):
`<Entity>DataService::create()` / `update()` of the six types take the fields
DTO (every field optional, only the given ones change), the entity's lists
(authors or members, memberships, import origins; `null` keeps, the given list
replaces) and its uploads, and run as one transaction. The SPA edit pages call
them through `/<entity>-data/` (`create`, `update`, and `batchUpload` /
`createFromFiles` for the flows that make one element per file); the public
form actions they replaced are deleted. Only the author alias actions the admin
form posts remain.

- Create places the element where the legacy action did (the author letter
  folder, the prod category, the release under its prod) and returns its id.
- Privileges: `publicAdd` for create under the parent, `publicReceive` for
  update — the same actions the SPA's buttons check.

## 2. Joins and conversions

Done: `join()` in `ProdDataService`, `AuthorDataService`,
`AuthorAliasDataService`, `GroupDataService`, `GroupAliasDataService` checks
`join` on both elements, logs, then calls the existing method; the
`convertToGroup()` / `convertToAuthor()` methods already had that shape. The
counts a preview needs are listed in
[php/entity-writes.md](../php/entity-writes.md#joins).

## 3. Authorship and membership

Done for productions, releases, groups and group aliases: `setMember()` next
to `deleteMember()`, writing through `AuthorshipDbal::setAuthorship()`;
both move the element's modification time — see
[php/entity-writes.md](../php/entity-writes.md#authors-and-members).

A plan that changes the same entity twice moves its modification time with the
first action, so the executor has to take the times its own actions produced as
the new baseline for later actions on that entity.

## 4. Files from a URL

`ZxArt\Releases\Services\ElementFileImporter` stores files fetched from a URL:

- `importFile()` — for the imports, through `prodsDownloader`: skips a name
  already stored, retries a failed download after 10 and 20 seconds. The
  release's main file of an import still goes through `prodsDownloader` inside
  `ImportedReleaseMerger::merge()`, with the md5 duplicate check.
- `addFiles(element, kind, urls)` — for URLs a user gives: downloads every URL
  first, then stores them all in the list of the kind (`ProdFileKind`:
  screenshots, inlays, maps, rzx; `ReleaseFileKind`: screenshots, inlays,
  info, ads); a refused download or a file of a type the list does not take
  stores none. Pictures only, except rzx (any type) and release info (also PDF
  and plain text), by the type detected from the bytes; 10 MB a picture,
  20 MB otherwise.
- `setMainFile(release, url, ?fileName)` — makes the download the release's
  file (up to 50 MB), named as given or as in the URL, and reads its structure
  and formats as an upload does (`updateFileStructure()`); hardware is not
  derived on save, for an upload neither. A refused download keeps the
  release's file.

Both go through `ZxArt\Shared\Http\GuardedDownloader`, over
`GuardedHttpClient`: `http`/`https` only,
every address — the first and each redirect, at most 5 — passes
`PublicAddressGuard` (the host must resolve to public addresses alone, not
loopback, private, link-local, shared, reserved, multicast or NAT64), and the
request is pinned to the checked address so a second DNS answer cannot move it;
a file is dropped as soon as it grows past its limit. `fetch_url` and
`view_image` use the same client (see
[features/ai-assistant.md](../features/ai-assistant.md)).

Entry points: `ProdDataService::addFiles()`, `ReleaseDataService::addFiles()`
(`AddedFilesDto`: the element and the new file ids) and
`ReleaseDataService::setFile()`; each needs `publicReceive` on the element,
answers a refused file with `400`, moves the modification time and logs
`publicReceive`. Deleting a file: `ProdDataService::deleteFile()`,
`ReleaseDataService::deleteFile()`.

## 5. Search with filters

Done, see [features/search.md](../features/search.md#typed-searches):
`ProdSearchService`, `AuthorSearchService`, `GroupSearchService`,
`ReleaseSearchService` and `PlaceSearchService`. Tags are filtered by
id; turning a tag name into its id is left to the caller.

The phrase search the assistant's `search` tool needs is
`EntitySearchService`, next to the public quick search rather than inside it,
so the public result does not grow: exactly the six types with aliases apart,
one list closest first, each result with its year and qualifier — see
[features/search.md](../features/search.md#entity-phrase-search).

## 6. Entity reads

`get_*` tools read `<Entity>StateService::get()`: the editable fields through
`<Entity>FieldsReader` (the DTO the writers take), the modification time, and
what the entity is linked with as ids — authorship records with roles and
dates, releases, aliases, parent groups — plus the counters of the author and
group pages, the import origins (portal and id) of every type, and the file
lists of a prod or a release with a release's file size (see
[php/entity-writes.md](../php/entity-writes.md#reads)). Titles and URLs of
those ids come from the batch lookup (section 9).

Not in the states yet, for the tools to add when they need them: a release's
inherited hardware and languages.

## 7. Running as a user outside a request

- Cron resumes a run as the `crontab` user for now (see
  [backend.md](backend.md#running-as-the-user)); nothing to change. Running as
  the conversation's owner later needs `switchUser()` to leave
  `privilegesManager` with that user's privileges compiled on the public root.
- `ServerSessionManager::close()` exists; the runner calls it before the loop
  starts so the user's polls are not blocked by the session lock.

## 8. LLM client

`PromptSender` sends a single prompt and returns text; it has no tool calls,
no message history and no token usage. It stays as it is for the existing
cron jobs. The assistant gets a new `ToolCallingClient` (see
[backend.md](backend.md)) on the same `openai-php/client` and `ai_key`.

## 9. Entity URLs and titles

`ZxArt\Shared\References\EntityMentionLookup::lookup()` takes the mentions of one
message (`EntityMentionDto`: an `EntityType` and an id) and answers
`MentionedEntityDto` (decoded title, SPA URL from `EntityUrlResolver`) keyed by
`type:id`, the shape of a message's `refs`. Productions, releases, authors,
groups and their aliases are looked up; a mention of anything else, of a
missing element or of one of another type is left out. Parsing `[[type:id]]`
out of a message is the assistant's own.

## 10. Integration tests

The unit tests check the writers, creators, factories and data services against
element spies; they cannot see how the CMS data layer really stores links,
hardware, languages, letter moves and categories. A separate PHPUnit suite
(`tests/Integration/`, `composer test:integration`, not part of `composer test`)
runs against the local docker database:

- for each of the six entity types: create through `<Entity>DataService::create()`,
  update through `update()`, then read the stored rows back and compare every
  field and link with what was written;
- the legacy add and edit actions through the same request a form sends.

Done for all six (`tests/Integration/Entities/`, see
[../testing.md](../testing.md#entity-writes)), apart from the production batch
upload, which needs uploaded files, and the group alias add form, which nothing
offers.

Each test runs in a transaction that is rolled back (the local database is on
InnoDB, see [innodb.md](innodb.md)).

## 11. Imports

The imports (ZxDB, Pouet, VT-DOS, World of Sam, itch.io — through
`ProdsService::importProd()`, `AuthorImportService::import*()` and
`ImportGroupHandler`) are correct as they are, and **their output must not
change by a single byte**. They do not go through the field writers:

- they merge, not replace — a field is filled only when empty unless a
  `forceUpdate*` flag says otherwise, and they persist only when something
  changed;
- they link categories, groups and publishers directly by import id, without the
  writers' normalisation (*misc* fallback, parent pruning) and validation;
- they do not queue the AI category and tag suggestions, leave the year
  fill-in and the tags alone, and skip what they cannot resolve instead of
  failing.

Routing them through the writers would change all of that. The
**golden master** guards it: `tests/Integration/Import/` (see
[testing.md](../testing.md#import-golden-master)) imports fixed DTOs — new prods
with releases, labels, parties, categories, files and articles; a local release
file; repeated imports without flags, with the update flags and with every force
flag; matches by another source's id and by title; compilations and series;
authors, aliases and labels with locations, re-imports with and without force
flags and group memberships; groups, subgroups and group aliases — and compares
everything written with the stored snapshots. Any change of the import code,
including sharing pieces with the writers (the file download of section 4), must
keep them identical.

Behaviour the snapshots hold as it is today:

- the author-import defects listed in
  [../features/imported-entities.md](../features/imported-entities.md#known-defects-of-the-author-import)
  (the empty-label one is left out of the snapshots, since the author it
  matches depends on the database);
- a new prod is queued for `ai_seo`, `ai_intro` and `social_post`, a new
  release for `social_post`.

The imports and the writers share the use-case shape, not the merge rules
([../php/entity-writes.md](../php/entity-writes.md#use-cases)): each takes a
command, runs in one transaction, loads or creates the legacy element, changes
it, and stores it through the same `<Entity>Repository`. An import changes the
element through its `Imported<Entity>Merger` (the code the imports always ran,
moved as it was); a writer through its `<Entity>FieldsWriter`. A fix to a field
rule still has to be made on both paths. The import code that moved keeps
running without `strict_types`, as before: the sources hand numeric import ids
over as ints.

## 12. Release cloning

Done: `ReleaseCloner` creates the copy through `ReleaseCreator`; the clone
action and `ReleaseDataService::cloneRelease()` both use it — see
[features/release.md](../features/release.md#special-operations).

## Checklist

- [x] prod: `ProdFieldsDto`, `ProdFieldsWriter`, `ProdCreator`, `ProdDataService::create()` / `update()` / `batchUpload()` (see [features/prod.md](../features/prod.md#writing-the-fields))
- [x] release, author, author alias, group, group alias: the same pieces — see [php/entity-writes.md](../php/entity-writes.md)
- [x] SPA edit pages save through the data endpoints with lists and uploads; the public form actions of five types, the production batch upload action and the release batch create service are deleted
- [x] `join()` with privilege and log in five data services; move counts for previews
- [x] Authorship and membership set-with-roles; modification time bumped
- [x] `ElementFileImporter` extracted from `ProdsService`; imports use it — only once the import golden master is in place
- [x] `ElementFileImporter::setMainFile()` / `addFiles()` with the size, type and address guards
- [x] Release lookup; location search
- [x] `ProdSearchService` with typed filters; author/group search
- [x] Phrase search over the six types with qualifier fields for the assistant (`EntitySearchService`)
- [x] Complete state reads for the six types with modification time, files and import origins
- [x] Batch title/URL lookup for entity refs
- [ ] Tests first for every new service method; update `docs/features/*.md` for each changed entity
- [x] Integration suite (`tests/Integration/`, separate testsuite) for the six data services, their lists and uploads
- [x] Golden-master suite for the imports; snapshots stored before any import code changes
- [x] Imports and writers as use cases over the same repositories, each in one transaction (section 11)
- [x] Release cloning through `ReleaseCreator`
