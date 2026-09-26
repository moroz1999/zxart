# Remaining `/ajax/` usages

Move each of these off the legacy `/ajax/` application to the entity type's own
data endpoint (`<Entity>Data` controller, `<Entity>DataService`, typed DTOs, HTTP
status codes and an `{"errorMessage": ...}` body on failure, recorded in the
actions log) — see `docs/php/rest-api.md`. `/prod-data/?action=delete` with `ProdDataApiService` is the reference.

`/ajax/` answers `200` with `responseStatus: success` whenever it finds the
element, whether or not the action did anything, so a silently skipped action
looks like a success.

## 1. Entity form saves — `FormSaveApiService.save()` (POST, default action `publicReceive`)

- Edit pages: author, author alias, group, prod, release, party, press, picture, tune
- `join-form` — `join`
- `ai-form` — `receiveAiForm`
- `split-form` — `split`

Each call goes through the entity's `<Entity>DataApiService` (`shared/api/`) instead of the generic `FormSaveApiService`.

Somewhat protected: `publicReceive` must answer `{id}` (`respondFormSaved`), so an empty response is treated as an error.

## 2. Live removals on edit forms — `FormSaveApiService` (GET, errors swallowed)

- `deleteMember` → `deleteAuthor` — removes an author from a prod, release or group
- `deleteFileElement` → `delete` — removes one file of a multi-file selector (prod, release)
- `deleteFile` → `deleteFile` — clears a single-file field after save

The response is never checked, so a failure goes unnoticed.

## 3. Editing-control actions — `zx-editing-controls` with `run: {action}`

- `claim` — authorship claim (author page)
- `clone` — clone a release
- `importScScreenshots` — prod page
- `importItchIoReleases` — prod page

Switch to `run: {execute}` calling the entity's data API service, the same way the conversions were.

## 5. Votes and playlists (legacy `responseStatus` envelope)

- `VoteService` → `vote`
- `PlaylistService` → `getPlaylistIds`, `addToPlaylist`, `removeFromPlaylist` (URL `/ajax/playlistId:N/`)
