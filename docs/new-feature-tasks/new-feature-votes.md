# Recent Ratings Module – Implementation Plan

## Goal

Implement the “Latest ratings” widget in Angular (UI identical to screenshot) backed by a new PHP backend stack (controller + service + repository) built via TDD.

Requirements:
- Ratings are persisted in DB already.
- Last **N** ratings are cached in **Redis**.
- Cache logic must be copied from legacy behavior (same semantics, keys, TTL, trimming, invalidation).
- After rollout: remove legacy template, legacy data-prep code, and legacy CSS for ratings.

---

## 1) Backend (PHP) – TDD-first stack

### 1.1 Contract (DTOs and endpoint)
Create a dedicated read endpoint returning “latest ratings” for the widget.

- `GET /recent-ratings?limit=N`
    - Default limit: use the same N as legacy (or the configured value used there).
    - Response is a list ordered newest → oldest.

Controller DTOs (controller-only):
- `GetRecentRatingsResponseDto`
    - `items: RecentRatingItemDto[]`
- `RecentRatingItemDto`
    - `user` (whatever shape your existing username component expects)
    - `rating` (int)
    - `targetTitle` (string)
    - `targetUrl` (string)
    - `createdAt` (string/iso, if used by UI; otherwise omit)

Errors:
- Follow the same error mapping conventions as `Comments` controller.

### 1.2 Layers (DTOs + exceptions per layer)
Implement in 4 layers (same pattern as previous work):

**Controller**
- Maps HTTP ↔ application DTOs
- Own controller DTOs + controller exceptions only

**Application**
- Service orchestration + merging cache / DB
- App DTOs + app exceptions

**Domain**
- Validation / invariants (if any; keep minimal)
- Domain exceptions

**Infrastructure**
- Repository for DB reads (latest ratings query)
- Cache adapter for Redis (if you split it)
- Infra DTOs + infra exceptions

### 1.3 Repository (DB)
Create repository to query the latest ratings from DB:

- `findLatest(int $limit): LatestRatingRowDto[]`

Row DTO fields must match what the widget needs:
- username component payload (user id + display name + flags used for icon)
- rating value
- target title + target URL (or target id + type if URL is built elsewhere)

### 1.4 Service (cache logic)
Implement `RecentRatingsService` using the legacy Redis strategy.

Read flow (cache-aside):
1. Try Redis: `getLatest(limit)`
2. If hit: return cached DTO list
3. If miss:
    - Load from DB repository: `findLatest(limit)`
    - Map to app DTOs
    - Store to Redis with same legacy rules (key format, TTL, serialization)
    - Return list

Write/update flow:
- If ratings are created elsewhere, integrate the cache refresh there OR implement a small method used by that write path:
    - `onRatingCreated(RecentRatingItemDto $item)`
    - Update Redis list: push newest, trim to N, keep TTL rules identical to legacy

### 1.5 Backend TDD order
Write tests in this exact order.

**(A) Repository integration tests**
- returns newest → oldest
- respects limit
- returns required joined fields (user + target)

**(B) Service unit tests**
- cache hit: returns Redis data, no DB call
- cache miss: loads from DB, stores Redis, returns mapped data
- cache store: correct key, ttl, serialization format (match legacy)
- on new rating: prepends and trims to N (match legacy)

**(C) Controller/feature tests**
- GET returns list in correct shape
- limit param respected (or capped per legacy rules)
- auth/guest behavior matches legacy (if legacy hides something, keep it)

Only after tests fail, implement minimal production code to pass.

---

## 2) Frontend (Angular)

### 2.1 Component structure
Create widget component, e.g. `recent-ratings-widget`.

UI must match screenshot:
- Title: “Последние оценки”
- Dark panel container
- Vertical list rows:
    - left: user icon + username (reuse existing username component)
    - middle: numeric rating (aligned)
    - right: target title as link (blue), wraps to next line for long titles

### 2.2 Reuse username component
- Use existing username component for display (and whatever icon logic it provides).
- Do not duplicate username rendering.

### 2.3 API client + state
- Angular service: `RecentRatingsApiClient`
    - `getRecentRatings(limit?: number)`
- Widget:
    - loads on init
    - displays list
    - shows empty state exactly as legacy (if legacy had one; otherwise keep silent)

### 2.4 Styling
- Implement new scoped styles for the widget (component-level), matching the legacy look from screenshot.
- Keep CSS minimal and local. No global pollution.

---

## 3) Redis cache details (must match legacy)
Tasks:
1. Locate legacy cache code for latest ratings:
    - key naming
    - data format (json/serialized)
    - TTL
    - trimming behavior
    - update trigger (on insert vs scheduled rebuild)
2. Mirror behavior in new service.
3. Add tests asserting those specifics (key, ttl, list size, ordering).

---

## 4) Removal of legacy
After new widget is verified end-to-end:

1. Delete old template responsible for the widget block.
2. Delete legacy “data preparation” code that feeds that template.
3. Delete legacy ratings-specific styles (only the ones for this widget, not shared typography/theme).

Safety checks before deletion:
- Search usages/references to old template/data-prep/styles.
- Confirm no other module imports those styles.

---

## 5) Acceptance checklist
- Widget in Angular matches screenshot layout and behavior.
- Backend returns latest ratings via `/recent-ratings`.
- Redis cache used and behaves exactly like legacy.
- No legacy ratings widget template/data-prep/styles remain.
- Tests green (repo/service/controller + frontend build).
