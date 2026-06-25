## Statistics Section

The statistics section is an Angular-powered dashboard exposed through the `zx-stats` custom element, hosted by the legacy `stats` structure element page.

### Data Scope
- The dashboard summarises the whole collection: software (prods), music and graphics, plus the most active community members.
- Category tab totals count all works in the corresponding table, including works without a known year.
- Counts by year come from the `year` column of `module_zxprod`, `module_zxmusic` and `module_zxpicture` (rows with `year > 0`).
- "Rated above average" counts use works whose `votes` exceed `zx.averageVote`.
- Distributions group works by year: prods by top-level software category, required computer model and country; music by format and country; graphics by type and country.
- Daily history (last 30 days) comes from the aggregated events table via `EventsLog::countEvents`: software uploads (`addZxProd`), music plays (`play`), graphics views (`view`).
- Top members come from `EventsLog` event counts (`addZxProd` / `addZxMusic` / `addZxPicture`, `comment`, `tagAdded`) and from `votes_history` for voters. User name, profile URL and badge are read from the user structure element.

### API
- Endpoint: `/stats/`
- `action=overview` (default) returns collection totals for the KPI strip.
- `action=soft`, `action=music`, `action=gfx` return a full category section: summary, year series (all/rated), distributions, daily history and top uploaders.
- `action=users` returns the top voters, commenters and taggers.
- Category blocks are also available independently through `{category}-summary`, `{category}-series`, `{category}-daily` and `{category}-top`, where category is `soft`, `music` or `gfx`.
- Distribution charts are available as individual blocks: `soft-category-distribution`, `soft-computer-distribution`, `soft-country-distribution`, `music-format-distribution`, `music-country-distribution`, `gfx-type-distribution` and `gfx-country-distribution`.
- User lists are also available independently through `users-voters`, `users-comments` and `users-tags`.
- Response shapes are defined in `api/stats.yaml`. The controller maps service DTOs from `ZxArt\Stats\Dto` to REST DTOs from `ZxArt\Stats\Rest`.

### Frontend Behaviour
- The KPI strip loads `overview` once on mount and shows skeleton bones until it arrives.
- Each tab (software, music, graphics, users) instantiates only when opened, and each block inside the tab starts its own API request when it enters the viewport through `IntersectionObserver`.
- Block responses are cached for the session, so re-opening a tab or scrolling back does not refetch.
- Only the active tab's content is instantiated (`zx-tabs` renders the active template), so sections load lazily.
- Charts use Chart.js through `ng2-charts`: year totals as a stacked bar (rated plus remainder), distributions as stacked bars, and daily history as a bar chart.
- Chart colours are read from theme CSS variables and re-resolved when the theme changes.
- Class labels and section titles are translated through `ngx-translate`.
- Each section shows a tailored skeleton (`zx-stats-section-skeleton`) while loading.
