## Search

Public full search is served by `ZxArt\Controllers\Searchresults` at `/searchresults/`.

Angular header quick search uses the same REST endpoint with `mode=quick`. Quick mode uses title-oriented instant search filters and does not search full content.

The legacy `/ajaxSearch/` application is still used by admin and non-Angular autocomplete widgets, and by the Angular tag/country/city autocompletes (`types:tag|country|city`, `mode:public`).

## Detailed search

The graphics and music branches of the legacy `detailedSearch` element are replaced by full-AJAX Angular pages:

- Graphics: `zx-picture-search` (`features/picture-search/`)
- Music: `zx-music-search` (`features/music-search/`)

- REST: `GET /picture-search/` (`ZxArt\Controllers\PictureSearch` → `ZxArt\PictureSearch\PictureSearchService`). Spec: `api/picture-search.yaml`.
- The service ports `detailedSearchElement::getQueryParameters()` (graphics branch) and runs it through `ApiQueriesManager`/`ApiQuery` with export type `zxPicture` or `author` (`resultsType=author` adds the `authorOfItemType=authorPicture` filter).
- The response includes legacy-compatible `apiUrl` (`/api/...`) and `zipUrl` (`/zipItems/...`) links built from the same filtration parameters.
- **URL scheme is preserved from legacy**: filters are `name:value/` path segments appended to the page URL (`titleWord`, `startYear`, `endYear`, `rating`, `partyPlace`, `pictureType`, `realtime`, `inspiration`, `stages`, `tagsInclude`, `tagsExclude`, `authorCountry`, `authorCity`, `resultsType`, `sortParameter`, `sortOrder`, `page`). The Angular component parses them on load and pushes them via `history.pushState` on every search/page change (`models/picture-search-url.ts`).
- `action=locations&ids=...` resolves country/city element titles for restoring filter chips from URL ids.
- Picture format codes are duplicated in `features/picture-search/models/zx-picture-types.ts` and must stay in sync with the backend `ZxPictureTypesProvider` trait; labels live in the `picture-search.format.*` i18n keys.

Music search uses the same URL and response principles via `GET /music-search/` (`ZxArt\Controllers\MusicSearch` → `ZxArt\MusicSearch\MusicSearchService`). Spec: `api/music-search.yaml`.

- Music-only URL/filter segments: `formatGroup`, `format`, `realtime`.
- Music result export type is `zxMusic`; `resultsType=author` adds `authorOfItemType=authorMusic`.
- The response includes distinct `formats` for the music format select.
- Tags exclusion uses the `zxMusicTagsExclude` query filter.
