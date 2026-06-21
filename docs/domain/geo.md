## Geo Section

The geo section is an Angular-powered map interface exposed through the `zx-geo` custom element.

### Data Scope
- The map displays authors, groups, and demoparties as independent layers.
- Countries and cities are filters, not separate navigation pages in the Angular interface.
- Country and city coordinates come from `country` and `city` structure elements.
- Entity counters are calculated from `module_author`, `module_group`, and `module_party` location fields.
- `countryElement` and `cityElement` `getUrl()` point at the nearest `countriesList` ancestor (found via `getFirstParentElement`) with a `?country=<id>` / `?city=<id>` query, so any country/city link across the product opens the geo section in that filtered state.
- Direct public requests to country or city legacy URLs return a 301 redirect to the geo section with the matching `country` or `city` query.

### API
- Endpoint: `/geo/`
- `action=map` returns countries, cities, coordinates, and aggregate counters.
- `action=authors`, `action=groups`, and `action=parties` return paginated entity lists.
- List actions accept `start`, `limit`, `sorting`, `search`, `countryId`, `cityId`, `north`, `south`, `east`, and `west`.

### Frontend Behavior
- Zoom below the city threshold displays country markers.
- Higher zoom displays city markers, plus a country-center marker for entities that have a country but no city (the country counter minus its city counters).
- Selecting a country fits its cities into the map viewport and limits entity lists to that country.
- Selecting a city centers the map on that city and limits entity lists to that city.
- The active country/city filter is mirrored in the URL query (`?country=<id>` / `?city=<id>`) via `history.pushState`, restored on load, and re-applied on browser back/forward.
- The basemap uses dark CARTO tiles under the dark theme and OpenStreetMap tiles otherwise, switching live with `ThemeService`.
- Active filters remain applied while the user pans or zooms the map.
- Each layer toggle (authors, groups, parties) also shows that type's count for the current scope; a disabled layer is dimmed.
- Each marker shows a per-type breakdown (an authors/groups/parties icon with its count) for the enabled layers, not a single total.
- The places panel lists viewport countries (or cities, above the city zoom) by default, the selected country's cities under a country filter, and is hidden under a city filter (the viewport place list is not assembled then).
- The places panel omits rows whose enabled-layer total is zero; the map payload already excludes cities with no entities at all.
- Author, group, and party list rows show the entity name and location (city, country); group rows also show the localized group type, omitting the `unknown` type.
- Entity lists use server-side pagination with 50 items per page.
- The search input is debounced and only the latest list request is kept; superseded requests are cancelled.
- Entity lists use the selected country or city when a filter is active, and the current map bounding box otherwise.
- The component height is the viewport height minus the current site header height.
- Author geo counters and lists use the current language row from `module_author`.
