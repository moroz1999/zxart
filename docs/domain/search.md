## Search

Public full search is served by `ZxArt\Controllers\Searchresults` at `/searchresults/`.
The routed Angular entrypoint is `/search`; its `phrase`, `types`, and `page` state lives in query parameters.

Angular header quick search uses the same REST endpoint with `mode=quick`. Quick mode uses title-oriented instant search filters and does not search full content.

The legacy `/ajaxSearch/` application is still used by admin and non-Angular autocomplete widgets, and by the Angular tag/country/city autocompletes (`types:tag|country|city`, `mode:public`).

## Detailed search

The graphics and music branches of the legacy `detailedSearch` element are replaced by full-AJAX Angular pages:

- Graphics: `zx-picture-search` (`features/picture-search/`)
- Music: `zx-music-search` (`features/music-search/`)

- Standalone SPA entrypoint: `/pictures/search` (`pages/picture-search`), mounting `zx-picture-search` with `manageUrl=false`.
- REST: `GET /picture-search/` (`ZxArt\Controllers\PictureSearch` → `ZxArt\PictureSearch\PictureSearchService`). Spec: `api/picture-search.yaml`.
- `PictureSearchService` builds the query in `ZxArt\PictureSearch\Repositories\PictureSearchRepository` — SQL directly against `module_zxpicture`, `module_author` and `structure_links` (author-location via the `authorPicture` link, tags via `tagsManager`) — and applies ordering, pagination and element loading through `PicturesManager` (`resultsType=author` runs the authors query through `AuthorsService`, narrowed to `displayInGraphics` authors of matching pictures).
- The response includes legacy-compatible `apiUrl` (`/api/...`) and `zipUrl` (`/zipItems/...`) links built from the request filters.
- **SPA URL scheme**: filters live in the router query params (`titleWord`, `startYear`, `endYear`, `rating`, `partyPlace`, `pictureType`, `realtime`, `inspiration`, `stages`, `fromGame`, `tagsInclude`, `tagsExclude`, `authorCountry`, `authorCity`, `resultsType`, `sortParameter`, `sortOrder`, `page`); only non-default values are emitted (`models/picture-search-query-params.ts`). `fromGame=1` restricts to pictures that belong to a game (`module_zxpicture.game`); the graphics "games" menu links here. When embedded in a legacy page (`manageUrl=true`) the component still parses/pushes the legacy `name:value/` path segments (`models/picture-search-url.ts`).
- `action=locations&ids=...` resolves country/city element titles for restoring filter chips from URL ids.
- Picture format codes are duplicated in `features/picture-search/models/zx-picture-types.ts` and must stay in sync with the backend `ZxPictureTypesProvider` trait; labels live in the `picture-search.format.*` i18n keys.
- Author-result queries use only the current-language `module_author` row, so totals and pagination count each logical author once.
- Result skeletons are shown only during the initial request; subsequent pagination keeps the current results visible while pagination is locked.

Music search uses the same principles via `GET /music-search/` (`ZxArt\Controllers\MusicSearch` → `ZxArt\MusicSearch\MusicSearchService` → `ZxArt\MusicSearch\Repositories\MusicSearchRepository`). Standalone entrypoint `/music/search` (`pages/music-search`). Spec: `api/music-search.yaml`.

- Music-only filters: `formatGroup`, `format`, `realtime`; title search also matches `internalTitle`.
- Music search runs against `module_zxmusic` and loads elements through `TunesManager`; author location uses the `authorMusic` link and `displayInMusic`.
- The response includes distinct `formats` for the music format select.

## File search

Standalone SPA route `/file-search` (`pages/file-search` → `zx-file-search`): searches
the file registry by file name (substring) or md5 (exact, 32 hex chars) and links each
match to the entity it belongs to.

- Endpoint: `GET /file-search-data/?q=<term>` (`ZxArt\Controllers\FileSearchData` →
  `ZxArt\FileSearch\FileSearchService`). Spec: `api/file-search.yaml`.
- `FileSearchRepository` queries `files_registry` (each row maps a file's `fileName`/`md5`
  to its `elementId`); the service loads the elements and resolves their SPA URL via
  `EntityUrlResolver`. Results are capped and require a 2+ character query.
