## Statistics Section

The statistics section is an Angular-powered dashboard exposed through the `zx-stats` custom element, hosted by the legacy `stats` structure element page.

### Data Scope
- The dashboard summarises the whole collection: software (prods), music and graphics, plus the most active community members.
- Counts by year come from the `year` column of `module_zxprod`, `module_zxmusic` and `module_zxpicture` (rows with `year > 0`).
- "Rated above average" counts use works whose `votes` exceed `zx.averageVote`.
- Average rating by year is `AVG(votes)` over works with `votes > 0`.
- Distributions group works by a direct column per year: prods by linked category title (`zxProdCategory` links to `module_zxprodcategory`), music by `type`, graphics by `type`. Only the six largest classes are kept; the remainder is aggregated into an `other` class.
- Daily history (last 30 days) comes from the aggregated events table via `EventsLog::countEvents`: software uploads (`addZxProd`), music plays (`play`), graphics views (`view`).
- Top members come from `EventsLog` event counts (`addZxProd` / `addZxMusic` / `addZxPicture`, `comment`, `tagAdded`) and from `votes_history` for voters. User name, profile URL and badge are read from the user structure element.

### API
- Endpoint: `/stats/`
- `action=overview` (default) returns collection totals for the KPI strip.
- `action=soft`, `action=music`, `action=gfx` return a category section: year series (all/rated/avg), distributions, daily history and top uploaders.
- `action=users` returns the top voters, commenters and taggers.
- Response shapes are defined in `api/stats.yaml`. The JSON renderer serialises the DTOs in `ZxArt\Stats\Dto` directly.

### Frontend Behaviour
- The KPI strip loads `overview` once on mount and shows skeleton bones until it arrives.
- Each tab (software, music, graphics, users) loads its own section the first time it is opened; the result is cached for the session, so re-opening a tab does not refetch.
- Only the active tab's content is instantiated (`zx-tabs` renders the active template), so sections load lazily.
- Charts use Chart.js through `ng2-charts`: year totals as a stacked bar (rated plus remainder), average rating as a line chart, distributions as 100% stacked bars, and daily history as a bar chart.
- Chart colours are read from theme CSS variables and re-resolved when the theme changes.
- Class labels and section titles are translated through `ngx-translate`; the `other` distribution class maps to `stats.dist.other`.
- Each section shows a tailored skeleton (`zx-stats-section-skeleton`) while loading.
