# Search — implementation

Domain rules: [../domain/search.md](../domain/search.md).

Public full search is served by `ZxArt\Controllers\Searchresults` at `/searchresults/`.
The routed Angular entrypoint is `/search`; its `phrase`, `types`, and `page` state lives in query parameters.

Angular header quick search uses the same REST endpoint with `mode=quick`. Quick mode matches titles only and does not search full content.

Relation pickers and tag pickers have endpoints of their own, apart from the public search:

- `GET /entity-lookup/?types=&query=` (`ZxArt\Controllers\EntityLookup` → `ZxArt\Search\Services\EntityLookupService`, spec `api/entity-lookup.yaml`): one page of the given structure types through the phrase engine, each item `{id, title}` named by `FoundElementTitle` (an author's real name and place, an alias's owner, a work's year and authors). Angular calls it only through `EntityLookupApiService` (`zx-entity-autocomplete`, `zx-multi-entity-autocomplete`, the country/city pickers of the detailed searches).
- `GET /tag-lookup/?query=` (`ZxArt\Controllers\TagLookup` → `ZxArt\Tags\Services\TagLookupService`, spec `api/tag-lookup.yaml`): the tags `TagSearchDbal` finds, loaded in the current language. Angular calls it only through `TagLookupApiService`.
  A tag's `title` is decorated for display (`Name, synonyms (description)`); the tag's own name is `value`. Every Angular tag picker turns results into items with `tagToItem()` (`shared/utils/tag-item.ts`), so forms save and searches filter by the real name. The search forms' tag pickers accept only existing tags (`allowCustomTags=false`).
- Tag filters (`tagsManager::getConnectedElementIdsByNames()`): included tags are intersected, and a name that is no tag, or tags no work carries, match nothing — the filter is never dropped. Excluded tags are gathered together.

## Title matching

Every search that compares a title with a phrase does it through
`ZxArt\Search\Dbal\TitleMatch`: the phrase search, the typed searches, the
detailed graphics and music searches with their `/api/` export filters
(`zxPictureTitleSearch`, `zxMusicTitleSearch`, and `zxMusicSearch` /
`authorSearch` of the zxnet and zxtune player APIs), the map's party search and
the import's lookups by name.

- Both sides are compared by their key: lower case, letters and digits of any
  script only (`\p{L}\p{N}`), HTML entities left out. `key()` builds it in PHP,
  `REGEXP_REPLACE` in SQL with the same patterns, so `rebel-star` finds
  `Rebelstar` and a title stored HTML-encoded is matched by what it reads.
- `where()` narrows a query by any of the given columns: `Contains` (the
  searches) or `Exact` (the import's lookups, `Resolver::alphanumericValueMatches()`).
  A phrase without a letter or a digit matches nothing. `%` and `_` never reach
  a `like` pattern: they are not part of a key.
- The query builder has no regular expressions, so `TitleMatch` is the only
  class that writes raw SQL: the column is wrapped by the grammar and every
  value is bound.
- Full texts (descriptions, articles) are matched by a plain `like` on the
  HTML-escaped phrase, in the full search only.

## Phrase search engine

Every phrase search runs through `ZxArt\Search\Services\PhraseSearchEngine::find(PhraseSearchQueryDto)`.
It answers the found elements (`PhraseSearchHitDto`: the `PhraseSearchType` it
was searched as, the element) in order, with the total; each caller maps them
to its own DTOs. The query sets:

- `mode` — `SinglePage` (each type's closest titles on one page) or `Paged` (a
  page cut at `offset` from one alphabetical list of the types in turn);
- `types` — `PhraseSearchType` cases (a structure type each, with its table,
  title columns and text columns); `aliases: MergeIntoOwner` makes `author` and
  `group` take in their aliases and answers an alias under its owner's type,
  `Separate` keeps an alias a type of its own;
- `contentMatching` — texts too, not only titles;
- `closestFirst` — reorder the page of all types together by how close a title's key is to
  the phrase's: equal, then starting with it, then the rest;
- `limit`, `languageId` (the language root elements are loaded under).

`ZxArt\Search\Dbal\PhraseSearchDbal` answers each type's ordered ids in one
query (a full scan: titles are matched by key, so no index helps); the engine
counts them, cuts the page out in PHP and loads the elements through
`structureManager`. Crack intros (the `cracktro` category) are left out of the
productions. Press articles are matched in one language only (the query's, or
the current one), the language the visitor reads them in.

| Caller | Settings |
|---|---|
| `SearchService::search()` (`/searchresults/`) | `Paged`, content matching, aliases merged, configured types |
| `SearchService::quickSearch()` (`mode=quick`) | `SinglePage`, titles only, aliases merged, configured types |
| `EntityLookupService::lookup()` (`/entity-lookup/`) | `SinglePage`, titles only, aliases separate, the picker's types |
| `EntitySearchService::search()` (assistant) | `SinglePage`, titles only, aliases separate, six types, closest first |

`SearchService` groups the hits into sets by type, in the order the types first
come. Tests: `tests/Integration/Search/` (the engine's settings, what the
public searches and the pickers answer, every title-matching search),
`tests/Search/` (the page shares and windows).

## Result ordering

The database orders the results, and an element stored in a row per language
(`module_author` and friends) is grouped into one.

- **Single page**: `SinglePageQuota` shares the limit out between the types
  found — evenly, a type with fewer results than its share leaving the rest to
  the others, the remainder going to the types that come first. Within a type
  the closest main titles come first (`TitleMatch::orderGroupsByCloseness()`:
  equal key, then a key starting with the phrase's), then alphabetically.
  An element the visitor may not see is left out of the page and the total.
- **Paged**: types are counted and concatenated in the configured order
  (`searchtypes-public.search`), and `PageWindow` cuts the page out of that one
  list, so a page can end one type and begin the next. Within a type the order
  is the main title alone, so every page is a part of one alphabetical list.
- The public searches never re-sort a page; only the engine's `closestFirst`
  setting (the assistant's) does.
- Ordering is the database collation, not a natural sort: `soft 10` comes before `Soft 2`.

## Detailed search

The graphics and music branches of the legacy `detailedSearch` element are replaced by full-AJAX Angular pages:

- Graphics: `zx-picture-search` (`features/picture-search/`)
- Music: `zx-music-search` (`features/music-search/`)

- Standalone SPA entrypoint: `/pictures/search` (`pages/picture-search`), mounting `zx-picture-search` with `manageUrl=false`.
- REST: `GET /picture-search/` (`ZxArt\Controllers\PictureSearch` → `ZxArt\PictureSearch\PictureSearchService`). Spec: `api/picture-search.yaml`.
- `PictureSearchService` builds the query in `ZxArt\PictureSearch\Dbal\PictureSearchDbal` — SQL directly against `module_zxpicture`, `module_author` and `structure_links` (author-location via the `authorPicture` link, tags via `tagsManager`) — and applies ordering, pagination and element loading through `PicturesManager` (`resultsType=author` runs the authors query through `AuthorsService`, narrowed to `displayInGraphics` authors of matching pictures).
- The response includes legacy-compatible `apiUrl` (`/api/...`) and `zipUrl` (`/zipItems/...`) links built from the request filters.
- **SPA URL scheme**: filters live in the router query params (`titleWord`, `startYear`, `endYear`, `rating`, `partyPlace`, `pictureType`, `realtime`, `inspiration`, `stages`, `fromGame`, `tagsInclude`, `tagsExclude`, `authorCountry`, `authorCity`, `resultsType`, `sortParameter`, `sortOrder`, `page`); only non-default values are emitted (`models/picture-search-query-params.ts`). `fromGame=1` restricts to pictures that belong to a game (`module_zxpicture.prod`); the graphics "games" menu links here. When embedded in a legacy page (`manageUrl=true`) the component still parses/pushes the legacy `name:value/` path segments (`models/picture-search-url.ts`).
- `action=locations&ids=...` resolves country/city element titles for restoring filter chips from URL ids.
- Picture format codes are duplicated in `features/picture-search/models/zx-picture-types.ts` and must stay in sync with the backend `ZxPictureTypesProvider` trait; labels live in the `picture-search.format.*` i18n keys.
- Author-result queries use only the current-language `module_author` row, so totals and pagination count each logical author once.
- Result skeletons are shown only during the initial request; subsequent pagination keeps the current results visible while pagination is locked.

Music search uses the same principles via `GET /music-search/` (`ZxArt\Controllers\MusicSearch` → `ZxArt\MusicSearch\MusicSearchService` → `ZxArt\MusicSearch\Dbal\MusicSearchDbal`). Standalone entrypoint `/music/search` (`pages/music-search`). Spec: `api/music-search.yaml`.

- Music-only filters: `formatGroup`, `format`, `realtime`; title search also matches `internalTitle`.
- Music search runs against `module_zxmusic` and loads elements through `TunesManager`; author location uses the `authorMusic` link and `displayInMusic`.
- The response includes distinct `formats` for the music format select.

## File search

Standalone SPA route `/file-search` (`pages/file-search` → `zx-parser`): the visitor
uploads a file and gets back the releases the archive's contents belong to. There is no
search by typed file name or md5.

- Endpoint: `POST /parser/` with a multipart `file` field (`parserApplication`,
  `project/modules/applications/parser.class.php`). Accepts up to 50 MB.
- `ZxParsingManager::parseFileStructure()` walks the upload recursively (archives, disk
  images, tapes) and yields a tree of items, each with its own md5.
- Every item's md5 is looked up in `files_registry`; matched elements are returned as
  `releases` with title, year, authors and clean SPA URLs from `EntityUrlResolver`.
- Items with neither matches nor children are flagged `notFound`, which drives the
  "not found only" filter in the UI.

## Typed searches

Searches by typed filters, for callers that know what they look for rather
than a phrase. Each answers one page of ids by title and the total
(`ZxArt\Shared\Dto\IdPageDto`); a part of a title, a name or a file name is
matched through `TitleMatch` (see [Title matching](#title-matching)):

Every filter given must hold; a list holds when any of its values does. What
an alias is credited with, made, published or holds as members counts for its
author or group, and an author or group is answered once, never its alias.

- `ZxArt\Prods\Services\ProdSearchService::search(ProdSearchFilterDto)` —
  the catalogue's own query filters for categories (with subcategories),
  hardware, languages, legal statuses, release types and formats of any
  release, and tag ids; then `ProdSearchDbal` narrows by:
  - title or alternative title; year, the year of any release, and the date
    added;
  - credited authors — all of `authorIds` by default, any of them with
    `anyAuthor` — and the roles they are credited in (`authorRoles` alone:
    anyone credited in those roles);
  - groups that made or published it, `groupLink` (`ProdLink`: `made`,
    `published`) narrowing to one of the two; publishers — authors or groups —
    by `publisherIds`;
  - country or city of an author or of a developer group (a publisher does not
    place a production);
  - party, compo and the highest place;
  - series, compilation, and the production a compilation includes.

  It runs its `ApiQuery` without temporary tables, so a search repeated after a
  write sees it. `idsQuery()` answers the same search as a query of the found
  ids, which the author and group searches nest.
- `ZxArt\Authors\Services\AuthorSearchService::search(AuthorSearchFilterDto)` —
  a part of the nickname, of the real name in any language or of an alias;
  countries, cities; membership of groups. An author is linked to the software
  it is credited on (`made`) or published (`published`): active years are the
  years of that software, nothing else; given productions or the ones a nested
  `ProdSearchFilterDto` finds, `prodLink` narrowing to one of the two links and
  `prodRoles` to the roles of the credit (roles leave out the publishers, who
  hold none); co-authors of an author — credited on the same software, the
  author itself left out.
- `ZxArt\Groups\Services\GroupSearchService::search(GroupSearchFilterDto)` —
  a part of the title, of the abbreviation or of an alias; countries, cities,
  types; founding and ending dates (a group without the date does not match);
  members (authors or their aliases); active years from the software they made
  or published; given productions or the ones a nested `ProdSearchFilterDto`
  finds, `prodLink` narrowing to made or published (alone: groups that
  had that role on any production); the subgroups of a group and the groups a
  group is a subgroup of.
- `ZxArt\Releases\Services\ReleaseSearchService::search(ReleaseSearchFilterDto)` —
  a part of the title or of the file name; the md5 of the release file or of
  any file inside it; given productions or the ones a nested
  `ProdSearchFilterDto` finds; year, type, formats, required hardware,
  languages; publishers — authors or groups, with their aliases; credited
  authors and roles as in the production search. `ReleaseSearchDbal` answers
  each release once, though it is stored in a row per language.
- `ZxArt\Tags\Dbal\TagSearchDbal::find()` — tags by a part of the name or of
  a synonym in any language, the most used first, each once and named by the
  language row that matched; the ids the production search filters by.
- `ZxArt\Geo\PlaceSearchService::find()` — countries and cities by a part of
  their name in any language, named in the current one; a city with its
  country.

- `ZxArt\Parties\Dbal\PartySearchDbal::find()` — parties by a part of the
  title, abbreviation or original name, of one year or any (the year is the title of the year element the
  party lies under); by title, each with its decoded title, abbreviation and
  year.

## Entity phrase search

`ZxArt\Search\Services\EntitySearchService::search(phrase, EntityType[] types, limit)`
finds prods, releases, authors, author aliases, groups and group aliases by a
phrase, for callers that need to tell entities of the same title apart (the
assistant's `search` tool), through the engine above (titles, an author's
real name, a prod's alternative title):

- The types are exactly those six, and an alias is a type of its own rather
  than merged into its author or group. No types means all six; any other
  `EntityType` is refused with `InvalidArgumentException`.
- The results of all types come in one list of `ZxArt\Search\Dto\FoundEntityDto`
  (type, id, decoded title, year, qualifier), the closest titles first
  whatever their type. The limit (1–100) caps the list.
- The year is a prod's year or a release's year (the prod's for an original
  release); the others have none. The qualifier is an author's real name and
  country, a prod's groups, a release's prod, an alias's author or group, a
  group's country.

