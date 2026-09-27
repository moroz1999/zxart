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

A field whose file is removed in the form (author/group/party `image`; picture `image`, `inspired`, `inspired2`, `sequence`, `exeFile`; tune `file`, `trackerFile`; release `file`) is cleared by a second `/ajax/` request (`deleteFile`) after the save, whose answer is never checked. Clear it within the save request itself.

Somewhat protected: `publicReceive` must answer `{id}` (`respondFormSaved`), so an empty response is treated as an error.

## 3. Editing-control actions — `zx-editing-controls` with `run: {action}`

- `claim` — authorship claim (author page)
- `clone` — clone a release
- `importScScreenshots` — prod page
- `importItchIoReleases` — prod page

Switch to `run: {execute}` calling the entity's data API service, the same way the conversions were.

## 5. Votes and playlists (legacy `responseStatus` envelope)

- `VoteService` → `vote`
- `PlaylistService` → `getPlaylistIds`, `addToPlaylist`, `removeFromPlaylist` (URL `/ajax/playlistId:N/`)
