# First Page Rewrite – Technical Specification

## Goal

Rewrite the main page as a **frontend-assembled Angular page** with **user-configurable modules**.
Backend no longer assembles the page from DB-defined modules.

- New Angular page: `firstpage`
- Page consists of independent **First Page Modules**
- User can:
    - enable / disable modules
    - reorder modules
    - configure module settings
- All settings persisted via **User Preferences Service**
- Backend delivers **data only**, no layout logic

Out of scope:
- Fullscreen image viewer
- Audio playback for tunes

---

## 1) Frontend Architecture

### 1.1 Page & layout
- New Angular page: `firstpage`
- Integrated via `layout.default.tpl` (manual relocation later)
- Page renders modules sequentially according to user config

### 1.2 Customization UI
- Button at top: **“Configure homepage”**
- Opens modal dialog

Modal contains:
- Sortable list of modules (drag & drop)
- Each module entry:
    - enable / disable checkbox
    - collapsible settings section
        - initially collapsed
        - expandable per module
- All changes:
    - saved immediately (“live save”)
    - persisted via User Preferences Service

---

## 2) User Preferences (Homepage)

- Preferences stored via existing **Settings / User Preferences Service**
- Stored data:
    - enabled modules
    - module order
    - per-module settings:
        - item count
        - optional minimal rating
        - other module-specific params
- Preference structure:
    - single logical preference for homepage config
    - JSON value (validated on backend)

---

## 3) First Page Modules (functional requirements)

Each module supports:
- configurable item count
- optional minimal rating
- enable / disable
- reorderable position

### Modules list

1. **New Programs**
    - ZX prods
    - rating > 3.92
    - added in last 30 days

2. **New Pictures**
    - ZX pictures
    - ordered by added date desc

3. **New Tunes**
    - ZX tunes
    - ordered by added date desc

4. **Best New Demos**
    - ZX prods
    - category = DEMO (enum id from backend)
    - current + previous year
    - rating > 4
    - random order

5. **Best New Games**
    - same as demos
    - category = GAME

6. **Recent Parties**
    - parties
    - ordered by year + added date

7. **Best Pictures of the Month**
    - pictures added in last 30 days of current year
    - ordered by rating desc

8. **Latest Added Programs**
    - ZX prods
    - all categories
    - ordered by added date desc
    - no rating filter

9. **Latest Added Releases**
    - ZX releases
    - ordered by added date desc

10. **Support by Purchase / Donation**
    - random ZX prods
    - status = “for sale” or “donation”

11. **Unvoted Pictures**
    - pictures not voted by current user
    - random sample from top 500 by rating

12. **Random Good Pictures**
    - random sample from top 2000 pictures by rating

13. **Unvoted Tunes**
    - same logic as pictures, for tunes

14. **Random Good Tunes**
    - same logic as pictures, for tunes

---

## 4) Entities & FSD

Entities to be implemented / extended:
- `zx-prod` (exists)
- `zx-release` (exists)
- `zx-picture` (new)
- `zx-tune` (new)

Each entity:
- Angular entity layer (FSD)
- Backend DTO mapping
- Shared UI primitives where applicable

---

## 5) Legacy template analysis (mandatory)

Before implementation of picture/tune modules:
- Analyze:
    - `zxPicture.short.tpl`
    - `zxMusic.table.tpl`

Produce a checklist per entity:
- displayed fields
- badges/icons
- rating display
- vote state
- links/navigation
- user-related state (voted / not voted)

Nothing from legacy templates may be skipped silently.

---

## 6) Backend API

### General rules
- Backend provides **data only**
- No module composition logic on backend
- Each module may have its own endpoint

### Controllers
- One controller per logical data group if needed
- Pattern reference: `Comments` controller

Example endpoints:
- `/firstpage/new-programs`
- `/firstpage/new-pictures`
- `/firstpage/unvoted-pictures`
- etc.

Each endpoint:
- accepts filter params (limit, minRating, year, etc.)
- resolves current user where required
- returns DTO list only

---

## 7) Backend Architecture & TDD

For each data source:

Layers:
1. Controller
2. Application Service
3. Domain (minimal rules)
4. Infrastructure (repository)

Rules:
- Own DTOs per layer
- Own exceptions per layer
- No cross-layer leaks

### TDD order (strict)
1. Repository tests
2. Service tests
3. Controller tests
4. Minimal implementation

---

## 8) Frontend Data Flow

For each module:
- Angular service calls corresponding backend endpoint
- Module component renders data
- Respects user config (count, rating, enabled)
- No shared state between modules except ordering/config

---

## 9) Non-goals / exclusions

- No fullscreen image viewer
- No tune playback
- No server-side page composition
- No legacy template reuse

---

## 10) Acceptance criteria

- Main page fully rendered by Angular
- Module order, visibility and settings persist per user
- All listed modules implemented
- Legacy picture/tune behavior fully covered (except excluded features)
- Backend endpoints covered by tests
- No legacy main-page backend composition remains
