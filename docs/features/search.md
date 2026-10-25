# Search — implementation

Domain rules: [../domain/search.md](../domain/search.md).

Public full search is served by `ZxArt\Controllers\Searchresults` at `/searchresults/`.
The routed Angular entrypoint is `/search`; its `phrase`, `types`, and `page` state lives in query parameters.

Angular header quick search uses the same REST endpoint with `mode=quick`. Quick mode uses title-oriented instant search filters and does not search full content.

The legacy `/ajaxSearch/` application is still used by admin and non-Angular autocomplete widgets, and by the Angular tag/country/city autocompletes (`types:tag|country|city`, `mode:public`).
  A tag result's `title` is decorated for display (`Name, synonyms (description)`); the tag's own name is `value`. Every Angular tag picker turns results into items with `tagToItem()` (`shared/utils/tag-item.ts`), so forms save and searches filter by the real name. The search forms' tag pickers accept only existing tags (`allowCustomTags=false`).
- Tag filters (`tagsManager::getConnectedElementIdsByNames()`): included tags are intersected, and a name that is no tag, or tags no work carries, match nothing — the filter is never dropped. Excluded tags are gathered together.

## Phrase search engine

Every phrase search runs through `ZxArt\Search\Services\PhraseSearchEngine::find(PhraseSearchQueryDto)`,
the only code that sets up the legacy `Search`. It answers the found elements
(`PhraseSearchHitDto`: the type it was searched as, the element) in order, with
the total; each caller maps them to its own DTOs. The query sets:

- `mode` — `SinglePage` (each type's closest titles on one page) or `Paged` (a
  page cut at `offset` from one alphabetical list of the types in turn);
- `types` — structure types; `aliases: MergeIntoOwner` makes `author` and
  `group` take in their aliases and answers an alias under its owner's type,
  `Separate` keeps an alias a type of its own;
- `contentMatching` — texts too, not only titles;
- `closestFirst` — reorder the page of all types by how close a title is to
  the phrase: equal, then starting with it, then containing it;
- `limit`, `languageId`.

The phrase is HTML-escaped before matching, since titles and texts are stored
encoded.

| Caller | Settings |
|---|---|
| `SearchService::search()` (`/searchresults/`) | `Paged`, content matching, aliases merged, configured types |
| `SearchService::quickSearch()` (`mode=quick`) | `SinglePage`, titles only, aliases merged, configured types |
| `EntitySearchService::search()` (assistant) | `SinglePage`, titles only, aliases separate, six types, closest first |

`SearchService` groups the hits into sets by type, in the order the types first
come. Tests: `tests/Integration/Search/` (the engine's settings, and what the
public searches answer).

## Result ordering

The database returns the results already ordered, `Search` (`trickster-cms/cms/core/Search.php`)
only takes the ids in that order.

- The order depends on the kind of search, and the caller picks it with `Search::setRelevanceOrdering()`:
  - **paged search** (`SearchService::search()`) is ordered by title alone, so every page is a part
    of one and the same alphabetical list;
  - **instant search** (`SearchService::quickSearch()`, the legacy `/ajaxSearch/` application) shows
    a single page, so it puts the closest matches on top: exact title first, then titles starting
    with the phrase, then titles containing it, and alphabetically within every group.
- `searchQueryFilter` exposes the main title of each type as `searchQueryFilter::SORT_TITLE_COLUMN`
  and does not order anything itself. Keeping the title in the column list is what lets a `distinct`
  query (authors, press articles) be ordered by it. A query without that column is left unordered.
- `Search` asks `QueryFiltersManager` for the filter queries **without** the temporary table wrapper
  (`$wrapTemporaryTables = false`): the wrapper re-selects the results from a temporary table and
  would drop the `ORDER BY`. Ids are taken in the order the database returned them and are only
  de-duplicated (a row exists per language in `module_author` and friends).
- Types are queried separately and concatenated in the configured order (`searchtypes-public.search`),
  so the page window walks through one type after another. The public searches never re-sort a
  page; only the engine's `closestFirst` setting (the assistant's) does.
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
(`ZxArt\Shared\Dto\IdPageDto`):

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
  - groups that made or published it, `groupRole` (`GroupProdRole`) narrowing
    to one of the two;
  - country or city of an author or of a developer group (a publisher does not
    place a production);
  - party, compo and the highest place;
  - series, compilation, and the production a compilation includes.

  It runs its `ApiQuery` without temporary tables, so a search repeated after a
  write sees it. `idsQuery()` answers the same search as a query of the found
  ids, which the author and group searches nest.
- `ZxArt\Authors\Services\AuthorSearchService::search(AuthorSearchFilterDto)` —
  a part of the nickname, of the real name in any language or of an alias;
  countries, cities; membership of groups; active years — the years of the
  software they are credited on, nothing else; credits on given productions or
  on the productions a nested `ProdSearchFilterDto` finds, `prodRoles`
  narrowing the roles; co-authors of an author (the author itself left out).
- `ZxArt\Groups\Services\GroupSearchService::search(GroupSearchFilterDto)` —
  a part of the title, of the abbreviation or of an alias; countries, cities,
  types; founding and ending dates (a group without the date does not match);
  members (authors or their aliases); active years from the software they made
  or published; given productions or the ones a nested `ProdSearchFilterDto`
  finds, `prodRole` narrowing to developer or publisher (alone: groups that
  had that role on any production); the subgroups of a group and the groups a
  group is a subgroup of.
- `ZxArt\Releases\Services\ReleaseSearchService::search(ReleaseSearchFilterDto)` —
  a part of the title or of the file name; the md5 of the release file or of
  any file inside it; given productions or the ones a nested
  `ProdSearchFilterDto` finds; year, type, formats, required hardware,
  languages; publishers — authors or groups, with their aliases; credited
  authors and roles as in the production search. `ReleaseSearchDbal` answers
  each release once, though it is stored in a row per language.
- `ZxArt\Geo\PlaceSearchService::find()` — countries and cities by a part of
  their name in any language, named in the current one; a city with its
  country.

## Entity phrase search

`ZxArt\Search\Services\EntitySearchService::search(phrase, EntityType[] types, limit)`
finds prods, releases, authors, author aliases, groups and group aliases by a
phrase, for callers that need to tell entities of the same title apart (the
assistant's `search` tool), through the engine above (the `<type>AjaxSearch`
filters: titles, an author's real name, a prod's alternative title):

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

