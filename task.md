# Remaining `/ajax/` usages

Move each of these off the legacy `/ajax/` application to a dedicated SPA data
endpoint: its own controller, service, typed DTOs, HTTP status codes and an
`{"errorMessage": ...}` body on failure. `/entity-conversion-data/` is the reference.

`/ajax/` answers `200` with `responseStatus: success` whenever it finds the
element, whether or not the action did anything, so a silently skipped action
looks like a success.

## 1. Entity form saves — `FormSaveApiService.save()` (POST, default action `publicReceive`)

- Edit pages: author, author alias, group, prod, release, party, press, picture, tune
- `join-form` — `join`
- `ai-form` — `receiveAiForm`
- `split-form` — `split`

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

Switch to `run: {endpoint, params}` the same way the conversions were.

## 4. Entity deletion — `EntityDeleteApiService` → `publicDelete`

Reads `{"success": true}` from the body instead of the HTTP status.

## 5. Votes and playlists (legacy `responseStatus` envelope)

- `VoteService` → `vote`
- `PlaylistService` → `getPlaylistIds`, `addToPlaylist`, `removeFromPlaylist` (URL `/ajax/playlistId:N/`)

## Outside the SPA

- `project/js/public/logics.playlist.js`
- `project/js/public/logics.zxPictures.js`
- `trickster-cms/homepage/js/public/component.ajaxForm.js`

Probably dead now that the SPA is the only public frontend; check whether anything still loads them.
