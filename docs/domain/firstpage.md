# Firstpage (Main Page)

## Overview

The main page displays a configurable set of content modules. Each visitor can customize the set, order, and display limits of modules through personal preferences. Logged-in users persist configuration on the backend and mirror it in localStorage; anonymous visitors use localStorage only.

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
| `bestTunesOfMonth` | Best Tunes of Month | Best-rated tunes added during the last 30 days in the current year |
| `latestAddedProds` | Latest Added Programs | Prods sorted by `dateAdded` desc (no date cutoff) |
| `latestAddedReleases` | Latest Added Releases | Releases sorted by `dateAdded` desc |
| `supportProds` | Support Programs | Prods with `legalStatus` in (`insales`, `donationware`), random |
| `unvotedPictures` | Unvoted Pictures | Pictures the current user has not yet rated |
| `randomGoodPictures` | Random Good Pictures | Random highly-rated pictures |
| `unvotedTunes` | Unvoted Music | Tunes the current user has not yet rated |
| `randomGoodTunes` | Random Good Music | Random highly-rated tunes |

### Module Data Filtering

- **newProds**: Filters by `dateAdded >= now - 30 days`, `votes >= minRating`, and `year >= currentYear - startYearOffset`.
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

**`startYearOffset`**: Stored as an integer offset from the current year (0–10). The UI displays the corresponding year. For example, in 2026 selecting `2025` stores `1`; in 2027 the same preference automatically means `2026`.

### Module Order and Visibility

- **Order**: Stored in `homepage_order` as a comma-separated list of module type keys.
- **Disabled**: Stored in `homepage_disabled` as a comma-separated list. Omitted modules are shown by default.
- Modules not present in `homepage_order` are appended at the end in default order.

## Frontend Architecture

- Each module is a standalone Angular component extending `FirstpageModuleBase<T>`.
- Module settings are injected via `MODULE_SETTINGS` token (`InjectionToken<ModuleSettings>`).
- Modules are lazy-loaded using `IntersectionObserver` with `rootMargin: 200px` — data fetching starts only when the module approaches the viewport.
- Data is fetched from `/firstpage/?action={moduleType}&...params` via `FirstpageDataService`.
- Module catalogue links use the routed `/prods`, `/pictures/search`, and
  `/music/search` URLs with query parameters.

## Catalogue Homepages

- `/pictures` without query parameters displays newly added, unvoted, and best pictures of the month.
- `/music` without query parameters displays newly added, unvoted, and best tunes of the month.
- Each section is an independent lazy firstpage module with its own backend request and loading skeleton.

## User Configuration Dialog

- Available via a settings button on the main page.
- Allows reordering (CDK drag-and-drop), enabling/disabling, and configuring per-module settings.
- The dialog closes only after a successful save. Failed saves keep the draft open and show an error.
- Logged-in saves are validated as one batch and committed in a database transaction. The successful backend response replaces the frontend preference store and its localStorage mirror.
- Anonymous saves update the frontend preference store and localStorage without a backend request.
- Reset restores all `homepage_*` preferences through the same save flow. Logged-in users use backend-provided defaults; anonymous users use the matching frontend defaults without an HTTP request.
