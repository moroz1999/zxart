# Search — implementation

Domain rules: [../domain/search.md](../domain/search.md).

Public full search is served by `ZxArt\Controllers\Searchresults` at `/searchresults/`.
The routed Angular entrypoint is `/search`; its `phrase`, `types`, and `page` state lives in query parameters.

Angular header quick search uses the same REST endpoint with `mode=quick`. Quick mode uses title-oriented instant search filters and does not search full content.

The legacy `/ajaxSearch/` application is still used by admin and non-Angular autocomplete widgets, and by the Angular tag/country/city autocompletes (`types:tag|country|city`, `mode:public`).

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
  so the page window walks through one type after another. `SearchService` groups the sets it gets
  into public types (aliases merged into `author`/`group`) and never re-sorts a page.
- Ordering is the database collation, not a natural sort: `soft 10` comes before `Soft 2`.

## Detailed search

The graphics and music branches of the legacy `detailedSearch` element are replaced by full-AJAX Angular pages:

- Graphics: `zx-picture-search` (`features/picture-search/`)
- Music: `zx-music-search` (`features/music-search/`)

- Standalone SPA entrypoint: `/pictures/search` (`pages/picture-search`), mounting `zx-picture-search` with `manageUrl=false`.
- REST: `GET /picture-search/` (`ZxArt\Controllers\PictureSearch` → `ZxArt\PictureSearch\PictureSearchService`). Spec: `api/picture-search.yaml`.
- `PictureSearchService` builds the query in `ZxArt\PictureSearch\Repositories\PictureSearchRepository` — SQL directly against `module_zxpicture`, `module_author` and `structure_links` (author-location via the `authorPicture` link, tags via `tagsManager`) — and applies ordering, pagination and element loading through `PicturesManager` (`resultsType=author` runs the authors query through `AuthorsService`, narrowed to `displayInGraphics` authors of matching pictures).
- The response includes legacy-compatible `apiUrl` (`/api/...`) and `zipUrl` (`/zipItems/...`) links built from the request filters.
- **SPA URL scheme**: filters live in the router query params (`titleWord`, `startYear`, `endYear`, `rating`, `partyPlace`, `pictureType`, `realtime`, `inspiration`, `stages`, `fromGame`, `tagsInclude`, `tagsExclude`, `authorCountry`, `authorCity`, `resultsType`, `sortParameter`, `sortOrder`, `page`); only non-default values are emitted (`models/picture-search-query-params.ts`). `fromGame=1` restricts to pictures that belong to a game (`module_zxpicture.prod`); the graphics "games" menu links here. When embedded in a legacy page (`manageUrl=true`) the component still parses/pushes the legacy `name:value/` path segments (`models/picture-search-url.ts`).
- `action=locations&ids=...` resolves country/city element titles for restoring filter chips from URL ids.
- Picture format codes are duplicated in `features/picture-search/models/zx-picture-types.ts` and must stay in sync with the backend `ZxPictureTypesProvider` trait; labels live in the `picture-search.format.*` i18n keys.
- Author-result queries use only the current-language `module_author` row, so totals and pagination count each logical author once.
- Result skeletons are shown only during the initial request; subsequent pagination keeps the current results visible while pagination is locked.

Music search uses the same principles via `GET /music-search/` (`ZxArt\Controllers\MusicSearch` → `ZxArt\MusicSearch\MusicSearchService` → `ZxArt\MusicSearch\Repositories\MusicSearchRepository`). Standalone entrypoint `/music/search` (`pages/music-search`). Spec: `api/music-search.yaml`.

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
