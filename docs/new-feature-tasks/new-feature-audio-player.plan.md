# Online Music Player + Radio - Work Plan

## Status
- Last updated: 2026-02-09
- Owner: Codex

## Plan
1) Read relevant docs (Angular, styles/design system, PHP/CMS/testing) and map spec to integration points.
2) Build a concrete implementation checklist per layer (backend APIs, Angular player/remote, criteria persistence, media session, multi-tab lock, tune list wiring, legacy cleanup).
3) Implement backend endpoints + services/repositories with TDD.
4) Implement Angular features and integrate with legacy tune list data until all lists are migrated.
5) Update API docs and remove legacy scripts/CSS after Angular replacement.

## Progress
- [x] Read `docs/domain.md` and `docs/domain/tune.md`.
- [x] Capture legacy play threshold, endpoint, and analytics behavior.
- [x] Capture radio presets, criteria, and legacy radio block markup.
- [x] Capture user clarifications for labels, criteria tweaks, analytics, and legacy removal.
- [x] Document music/radio domain rules in `docs/domain/music-player-radio.md`.
- [x] Read Angular/styles/design-system/PHP/testing rules and finalize implementation plan.
- [x] Read REST API, repositories, and services rules for backend implementation.
- [x] Add backend radio criteria classes, services, and controllers.
- [x] Add backend tune play service and controller.
- [x] Add repository support for criteria-based random tune selection.
- [x] Update `zxMusicLessListened` to use `plays < 10`.
- [x] Add radio criteria user preference code and default value.
- [x] Add unit tests for radio criteria factory, radio service, tune play service, and repository criteria selection.
- [x] Document new radio and tunes endpoints in `api/api.yaml`.
- [x] Implement remaining backend pieces (cleanup of legacy endpoints).
- [x] Add Angular player services (audio engine, radio API, play logging, criteria storage, preset mapping).
- [x] Add Angular player UI (bottom sheet) and radio remote component.
- [x] Wire tune lists to player via play button.
- [x] Add player and radio remote custom elements to legacy templates.
- [x] Add i18n keys for player and radio UI (en/ru/es).
- [x] Implement remaining frontend pieces (legacy tune lists integration, legacy player cleanup, CSS updates).
- [x] Finish remaining frontend checks (tests).
- [x] Update API docs and remove legacy scripts/CSS.

## Legacy Parity Findings (Verified)
### Play threshold + logging
- Threshold: 75% of track duration, accumulated real playback time (only while playing).
- Pause/resume accumulates time; seeking does not grant time.
- Play logged once per track, resets when playback exceeds duration.
- Legacy endpoint: `POST /ajax/id:{id}/action:logPlay/` (JsonRequest).
- Analytics: `ym(94686067, 'reachGoal', 'musicplay')` (keep as-is).
- Source: `project/js/public/logics.musicLogger.js`.
- Backend play handler: `project/modules/structureElements/zxMusic/action.logPlay.class.php` and `project/modules/structureElements/zxMusic/structure.class.php`.

### Radio presets + criteria (legacy)
Preset buttons from right column (`project/templates/public/component.column.right.tpl`):
- discover: "Непроголосованные"
- randomgood: "Лучшие"
- games: "Из игр"
- demoscene: "Сцена"
- lastyear: "С прошлого года"
- ay: "AY"
- beeper: "Бипер"
- exotic: "Экзотика"
- underground: "Андерграунд"

Legacy criteria from `project/modules/applications/randomTune.class.php`:
- randomgood: `zxMusicMinRating = averageVote + 0.2` (averageVote = 3.8 => 4.0)
- games: `zxMusicGame = true`, `zxMusicMinRating = averageVote + 0.2`
- demoscene: `zxMusicMinPartyPlace = 1000`, `zxMusicMinRating = averageVote + 0.2`
- ay: `zxMusicFormatGroup in [ay, aycovox, aydigitalay, ts]`, `zxMusicMinRating = averageVote + 0.2`
- beeper: `zxMusicFormatGroup in [beeper, aybeeper]`, `zxMusicMinRating = averageVote + 0.2`
- exotic: `zxMusicFormatGroup in [digitalbeeper, tsfm, fm, digitalay, saa]`, `zxMusicMinRating = averageVote + 0.2`
- discover: `zxMusicNotVotedBy = currentUser.id`, `zxMusicBestVotes = 100`
- underground: `zxMusicLessListened = 1000`, `zxMusicBestVotes = 500`
- lastyear: if month < 3 => `zxMusicYear in [Y-1, Y]`, else `zxMusicYear in [Y]`, plus `zxMusicMinRating = averageVote + 0.2`
- Always add: `zxMusicPlayable = true`

Relevant filters:
- `zxMusicMinPartyPlace`: partyplace <= argument and != 0.
- `zxMusicPlayable`: mp3Name != ''.

### Multi-tab legacy
- Legacy broadcasts via `localStorage` storage-event: `project/js/public/logics.broadcast.js`.

## User Decisions / Clarifications
- Translation labels are stored in the database; CMS translations service resolves them at runtime.
- Labels to use for radio presets are the Russian strings listed above (DB-backed).
- `zxMusicNotVotedBy` works only for logged-in users; allow guest behavior as-is (anonymous id applies).
- Update `zxMusicLessListened` semantics: filter should be "plays < 10" instead of "plays > 0".
- Build a new radio endpoint; remove the old endpoint later.
- Remove legacy scripts and CSS for the old player/radio once Angular replacement is ready.
- Keep analytics `ym(94686067, 'reachGoal', 'musicplay')`.

## Pending Decisions / Questions
- None pending after latest clarifications.

## Implementation Checklist (Draft)
Backend (PHP, TDD):
- Add `POST /tunes/play` with legacy payload parity and play threshold semantics.
- Add `POST /radio/next-tune` that accepts criteria and returns tune DTO.
- Implement new radio criteria filters; update `zxMusicLessListened` to `plays < 10`.
- Maintain legacy analytics logging and play count rules.
- Deprecate and then remove `/randomTune/type:{type}` application.
- Use ObjectMapper to map internal DTOs to REST DTOs; store DTOs in `models/`.
- Repository rules: no raw SQL, use query builder, avoid `pluck()->all()`.
- Use PHP-DI constructor injection; avoid `$this->getService()` in new services.

Frontend (Angular):
- Player component with playlist scope per module, repeat/shuffle, seek, advanced criteria, media session, and radio mode.
- Radio remote block: sends `switchToRadio + applyPreset`.
- Multi-tab exclusive playback via `BroadcastChannel` + `storage` fallback.
- Criteria persistence: user prefs if authenticated, localStorage otherwise.
- Ensure player hidden by default, reset on close, no system volume control.
- Use Material UI for all components; standalone only; `zx-` selector prefix.
- Use design system components from `shared/ui` when available.
- All UI strings in Angular must use `ngx-translate` and be added to `en.json`, `ru.json`, `es.json`.
- SCSS rules: no custom CSS unless required, no base variables, no negative margins, use component variables and `zx-stack` layout.

Legacy cleanup:
- Remove `project/js/public/logics.musicPlayer.js`, `project/js/public/logics.music.js`, `project/js/public/logics.musicLogger.js`, `project/js/public/logics.musicRadio.js`, `project/js/public/logics.radioControls.js`, and related CSS once replaced.
- Remove or update legacy Smarty templates that inject old player controls if no longer needed.
- Remove legacy `/randomTune/type:{type}` app and any wiring in `project/modules/designThemes/project.class.php`.

## Notes
- Translation labels are stored in the database and resolved by the CMS translations service.
- Legacy radio block labels are DB-backed; Angular UI text still must use `ngx-translate`.



