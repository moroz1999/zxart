# Firstpage (Main Page)

## Overview

The main page displays a configurable set of content modules. Each user can customize the set, order, and display limits of modules through personal preferences. Configuration is stored server-side as user preferences.

## Module System

### Module Types

| Type | Title key | Data source |
|---|---|---|
| `newProds` | New Programs | Recent prods added in last 30 days, sorted by `dateAdded` desc |
| `newPictures` | New Pictures | Recently added pictures |
| `newTunes` | New Music | Recently added tunes |
| `bestNewDemos` | Best New Demos | Random demos from current/previous year with high rating |
| `bestNewGames` | Best New Games | Random games from current/previous year with high rating |
| `recentParties` | Recent Parties | Most recent demoparties |
| `bestPicturesOfMonth` | Best Pictures of Month | Best-rated pictures of the current month |
| `latestAddedProds` | Latest Added Programs | Prods sorted by `dateAdded` desc (no date cutoff) |
| `latestAddedReleases` | Latest Added Releases | Releases sorted by `dateAdded` desc |
| `supportProds` | Support Programs | Prods with `legalStatus` in (`insales`, `donationware`), random |
| `unvotedPictures` | Unvoted Pictures | Pictures the current user has not yet rated |
| `randomGoodPictures` | Random Good Pictures | Random highly-rated pictures |
| `unvotedTunes` | Unvoted Music | Tunes the current user has not yet rated |
| `randomGoodTunes` | Random Good Music | Random highly-rated tunes |

### Module Data Filtering

- **newProds**: Filters by `dateAdded >= now - 30 days` AND `votes >= minRating`. Optional `startYear` — if set (offset > 0), additionally filters by `year >= currentYear - offset`.
- **bestNewDemos / bestNewGames**: Filters by category, `votes >= minRating`, and `year >= currentYear - 1`. Results are randomized.
- **supportProds**: Filters by `legalStatus IN ('insales', 'donationware')`. Results are randomized.
- **unvotedPictures / unvotedTunes**: Show items the current user has not voted on; only meaningful for authorized users.

### Module Settings (User Preferences)

Each module has a `limit` preference (`homepage_{type}_limit`).

Some modules support additional settings:

| Setting | Modules | Preference code |
|---|---|---|
| `minRating` | newProds, bestNewDemos, bestNewGames | `homepage_{type}_min_rating` |
| `startYearOffset` | newProds | `homepage_new_prods_start_year` |

**`startYearOffset`**: Stored as an integer offset (0–10). `0` means no year filter. `N` means filter `year >= currentYear - N`. In the UI, shown as a year select (e.g. 2026, 2025, ..., 2016).

### Module Order and Visibility

- **Order**: Stored in `homepage_order` as a comma-separated list of module type keys.
- **Disabled**: Stored in `homepage_disabled` as a comma-separated list. Omitted modules are shown by default.
- Modules not present in `homepage_order` are appended at the end in default order.

## Frontend Architecture

- Each module is a standalone Angular component extending `FirstpageModuleBase<T>`.
- Module settings are injected via `MODULE_SETTINGS` token (`InjectionToken<ModuleSettings>`).
- Modules are lazy-loaded using `IntersectionObserver` with `rootMargin: 200px` — data fetching starts only when the module approaches the viewport.
- Data is fetched from `/firstpage/?action={moduleType}&...params` via `FirstpageDataService`.

## User Configuration Dialog

- Available via a settings button on the main page.
- Allows reordering (CDK drag-and-drop), enabling/disabling, and configuring per-module settings.
- Changes are saved to user preferences server-side; the config service reloads on save.
- Reset button restores all `homepage_*` preferences to their server-side defaults.