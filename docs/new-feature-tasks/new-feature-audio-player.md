# Online Music Player + Radio (Angular rewrite) – Technical Specification

## Goal

Implement an **online music player** in Angular, replacing legacy player behavior.
Player UI and controls must match the provided screenshots.

Key behavior changes vs legacy:
- **One module (one tune list) = one playlist**.
- Global playlist across multiple lists on the page is **not supported**.
- **Radio** is a player mode (not page playlist) driven by criteria in the player UI.
- Multi-tab: **only one tab may play**; other tabs must **Stop + Hide + reset state**.

Out of scope:
- Fullscreen picture viewer
- Any separate “tune playback page” logic not described here

---

## 1) UI/UX Requirements

### 1.1 Tune list behavior (page modules)
- Page may contain **1..N tune lists** (modules).
- Clicking **Play** on a tune inside a list:
    - stops current playback immediately (if any)
    - switches player to that list’s playlist
    - starts from the clicked tune, then continues in list order

Playlist scope:
- Tracks only from the clicked list module.
- Switching to another list = hard stop + new playlist.

### 1.2 Bottom player visibility and lifecycle
- Player is **hidden by default**.
- Player appears **only after first Play**.
- Closing/hiding player:
    - **Stop audio**
    - **Hide player**
    - **Reset state** (as if nothing was playing)

System volume:
- No volume control in UI; rely on OS/browser volume.

### 1.3 Player controls (must exist)
From screenshot structure:
- Play/Pause
- Prev
- Next
- Track title/artist display: `artist - title`
- Progress bar:
    - shows current progress
    - clickable for seek
    - seek allowed in page playlist and radio
- Repeat mode selector:
    - `repeat: off | one | all`
- Shuffle toggle:
    - `shuffle: on | off`
- Advanced section:
    - expands upward inside player, increasing height

Radio-specific:
- Prev does not work in radio mode (disabled or no-op).

---

## 2) Playback Modes

### 2.1 Page Playlist mode
Source:
- A tune list module on the current page.

Next logic:
- If shuffle off:
    - next = next item by order
- If shuffle on:
    - compute a shuffled order once per cycle (one permutation)
    - play through permutation
    - when exhausted:
        - if repeat = all: reshuffle and continue
        - else: stop at end

Repeat logic:
- repeat off:
    - stop at end of list
- repeat one:
    - loop current track
- repeat all:
    - loop list (respecting shuffle setting)

### 2.2 Radio mode
Source:
- Player fetches next tune from backend using criteria currently selected in player.

Radio behavior:
- Auto-next always enabled:
    - when track ends, fetch next and play
    - pressing Next fetches next and plays
- Prev is disabled / no-op
- repeat/shuffle UI:
    - repeat one may be allowed (loop same track) if desired; otherwise disable in radio
    - shuffle is effectively server-driven; keep UI consistent but radio ignores shuffle toggle (documented in code)

Radio reset:
- Switching from radio to page playlist:
    - radio stops and clears its queue/state

---

## 3) Play Statistics (Play event threshold)

### 3.1 Threshold source
- Determine N% threshold from legacy implementation (must be discovered).
- Implement identical semantics.

### 3.2 Counting rule (real playback time)
Play event is sent when:
- accumulated **real playing time** reaches **N% of track duration**
- accumulation rules:
    - only count while audio is actively playing (not paused)
    - seeking does not grant time; user must actually play
    - accumulate across pauses/resumes for the same track
    - once threshold is met, send Play once per track
- when track ends:
    - reset accumulated time for that track to 0

### 3.3 Reporting destinations
- Send Play to:
    - backend endpoint (same purpose as legacy)
    - analytics endpoint/system (same as legacy)
- Both payload format and dedup rules must be matched to legacy.

---

## 4) Radio Presets → Criteria in Player

### 4.1 Discovery (mandatory)
Agent must extract from legacy:
- full list of presets shown in UI (from screenshot: e.g. “Непроголосованные”, “Лучшие”, “Из игр”, “Сцена”, “С прошлого года”, “AY”, “Бипер”, “Экзотика”, “Андеграунд”, etc.)
- exact criteria for each preset:
    - rating constraints
    - year constraints
    - country constraints
    - format constraints
    - any other hidden filters (e.g. categories/tags/platforms/competitions/authors/etc.)

No preset criteria may be guessed.

### 4.2 Advanced criteria UI
Player has “Advanced” panel containing criteria controls.
At minimum:
1. Rating range: min/max (both optional)
2. Years: include/exclude logic per legacy behavior
3. Countries: include/exclude list
4. Formats: include/exclude list
5. Any additional legacy preset filters discovered

Preset select:
- Selecting a preset sets criteria fields accordingly.
- Any manual change to criteria:
    - preset selector becomes `Custom` (no auto-detect back to preset).

### 4.3 Preset remote block
The existing “radio block” on page remains as a remote:
- clicking a preset in the block:
    - sends command to player: `switchToRadio + applyPreset(presetId)`
- player is the single source of truth for selected preset/criteria

---

## 5) Persistence of Radio Criteria

### 5.1 Authenticated users
- Persist last selected radio criteria set in **User Preferences**.
- If user is authenticated:
    - load from User Preferences on init
    - ignore localStorage entirely

### 5.2 Anonymous users
- Persist criteria in localStorage.
- Load from localStorage on init.

---

## 6) Multi-tab Behavior (exclusive playback)

Goal:
- If playback starts in one tab, all other tabs must:
    - Stop
    - Hide player
    - Reset state (as if nothing was playing)

Implementation requirement:
- Use browser messaging (e.g. `BroadcastChannel` or `storage` event fallback).
- A tab starting playback broadcasts an “exclusive lock” message.
- Receiving tabs immediately enforce Stop+Hide+Reset.

No shared progress/title state across tabs.

---

## 7) Media Session API (lock screen controls)

Implement browser Media Session integration:
- set metadata:
    - title: track title
    - artist: track artist
    - no artwork
- register handlers:
    - play
    - pause
    - nexttrack
    - previoustrack (disabled/no-op in radio mode)
    - seekto (and/or seek backward/forward if supported)
- keep Media Session position state updated while playing

---

## 8) Backend (PHP) – New/Updated APIs (TDD)

### 8.1 Endpoints (indicative)
Exact naming may follow local routing conventions; do not use `/api`.

Required capabilities:

1) Report Play event
- `POST /tunes/play`
    - payload: tuneId, playback context (radio/page), timestamp, etc. as per legacy

2) Radio: fetch next tune by criteria
- `POST /radio/next-tune`
    - request: criteria object (min/max rating, years, countries, formats, additional filters)
    - response: tune DTO for playback + minimal display data

### 8.2 Architecture
For new/changed backend features:
- Controller + Service + Repository
- Pattern reference: `Comments` controller
- Separate DTOs and Exceptions per layer
- Strict TDD:
    1. Repository tests
    2. Service tests
    3. Controller tests
    4. Implementation

### 8.3 Repository work
- Add/extend queries required to support:
    - radio criteria filtering
    - random selection consistent with legacy intent (but driven by criteria, not preset id)
    - exclusions if legacy did any (must be discovered)

### 8.4 Legacy parity tasks (mandatory)
Agent must locate and document:
- legacy Play threshold percent and exact counting semantics
- legacy Play reporting endpoints/payload requirements
- legacy radio preset criteria and selection logic

---

## 9) Frontend Architecture (Angular + FSD)

### 9.1 Features
- `features/player`:
    - player UI + state machine + playback engine
    - modes: page playlist, radio
    - repeat/shuffle/seek
    - advanced criteria UI
    - media session integration
    - multi-tab exclusivity

- `features/radio-remote`:
    - radio block UI (preset buttons)
    - dispatches commands to player

- `entities/zx-tune`:
    - tune model + DTO mapping
    - tune list item UI (play button, title link, author link, format, year, rating, downloads if needed)
    - NOTE: playback itself is not in entity, only display + data

### 9.2 Tune list modules integration
- Any tune list module renders play buttons that call player with:
    - playlist id (module instance id)
    - track list (tune ids in module order)
    - start index (clicked item)

---

## 10) Acceptance Criteria

1. Player appears only after Play.
2. Clicking Play in another list immediately stops current playback and switches playlist.
3. Page playlist respects repeat + shuffle requirements.
4. Radio mode:
    - Next fetches from backend using criteria
    - auto-next works at end
    - Prev disabled
5. Play event:
    - sent only after reaching legacy N% via accumulated real playback time
    - sent to backend + analytics as legacy
6. Advanced criteria UI exists; presets set criteria; manual edits switch to Custom.
7. Radio remote block switches player to radio and applies preset.
8. Criteria persistence:
    - authenticated: user preferences only
    - anonymous: localStorage
9. Multi-tab: starting playback in one tab stops/hides/resets player in all other tabs.
10. Media Session controls work (play/pause/next/seek; prev disabled in radio).
