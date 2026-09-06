# Geo section — implementation

Domain rules: [../domain/geo.md](../domain/geo.md).

The geo section is an Angular-powered map interface exposed through the `zx-geo` custom element and the standalone SPA route `/geo` (`pages/countries`).

### Data Scope
- The map displays authors, groups, and demoparties as independent layers.
- Countries and cities are filters, not separate navigation pages in the Angular interface.
- Country and city coordinates come from `country` and `city` structure elements.
- Entity counters are calculated from `module_author`, `module_group`, and `module_party` location fields.
- `countryElement` and `cityElement` `getUrl()` point at the nearest `countriesList` ancestor (found via `getFirstParentElement`) with a `?country=<id>` / `?city=<id>` query, so any country/city link across the product opens the geo section in that filtered state.
- Direct public requests to country or city legacy URLs return a 301 redirect to the geo section with the matching `country` or `city` query.

### API
- Endpoint: `/geo-data/` (`ZxArt\Controllers\GeoData`)
- `action=map` returns countries, cities, coordinates, and aggregate counters.
- `action=authors`, `action=groups`, and `action=parties` return paginated entity lists.
- List actions accept `start`, `limit`, `sorting`, `search`, `countryId`, `cityId`, `north`, `south`, `east`, and `west`.

### Managing countries and cities

Countries hang under the single `countries` container and carry a `countries`
link from every geo section — that is what a section lists;
`ZxArt\Geo\PlacesManageService` establishes both when a country is created.
A city is an ordinary child of its country.

They are edited at `/manage/countries` through `/countries-data/`, behind the
places' own element actions — `country/receive`, `country/delete`,
`city/receive`, `city/delete`, held by the `countries-managers` group; see
[manage-section.md](manage-section.md). A country and a city are separate types
and therefore separate privileges. A place holds a title per interface language
— a blank one is refused — and a pair of coordinates.

`countryId` states which country a city belongs to on every save, creation and
update alike, and naming another one moves the city there. It has no usable
default and the request is refused without it: a city with no country hangs off
nothing, is unreachable through the section it should belong to, and its own
`getUrl()` cannot find the geo section it must link back to.

Deletion is refused while a country still has cities, or while an author, a
group or a party still names the place: those references are plain id columns
and would be left pointing at nothing.

The container lives under the admin root, outside the public URL tree, so the
public structure manager reaches it by id rather than by path, and only for a
user whose group holds `countries`/`showFullList` on the public root; without
that type privilege a country could not be created at all.

Nothing recalculates the section projection on its own. The admin section form
(`receiveCountriesList`) re-links every country to a section wholesale, and that
is the only other place those links are written; there is no job to run after a
change here. A link written straight through `linksManager` does not clear the
parent's cached copy — only `persistStructureLinks()` does — so a country
creation and a city move drop the affected elements from the element cache
themselves.

### Frontend Behavior
- Zoom below the city threshold displays country markers.
- Higher zoom displays city markers, plus a country-center marker for entities that have a country but no city (the country counter minus its city counters).
- Selecting a country fits its cities into the map viewport and limits entity lists to that country.
- Selecting a city centers the map on that city and limits entity lists to that city.
- The active country/city filter is mirrored in the child route (`/geo/country/<id>` / `/geo/city/<id>`), restored on load, and re-applied on browser back/forward.
- The map payload arrives after the map is built: when the route selects no place, the markers, the scope counters and the entity list are filled in from the current viewport as soon as it lands.
- The basemap uses dark CARTO tiles under the dark theme and OpenStreetMap tiles otherwise, switching live with `ThemeService`.
- Active filters remain applied while the user pans or zooms the map.
- Each layer toggle (authors, groups, parties) also shows that type's count for the current scope; a disabled layer is dimmed.
- Each marker shows a per-type breakdown (an authors/groups/parties icon with its count) for the enabled layers, not a single total.
- The places panel lists viewport countries (or cities, above the city zoom) by default, the selected country's cities under a country filter, and is hidden under a city filter (the viewport place list is not assembled then).
- The places panel omits rows whose enabled-layer total is zero; the map payload already excludes cities with no entities at all.
- Author, group, and party list rows show the entity name and location (city, country); group rows also show the localized group type, omitting the `unknown` type.
- Entity lists use server-side pagination with 50 items per page. The panel is too narrow for a numbered page selector, so the pager is a prev/next pair with the current range; the list itself is marked with `zxLoadingState` while a page loads.
- The search input is debounced and only the latest list request is kept; superseded requests are cancelled.
- Entity lists use the selected country or city when a filter is active, and the current map bounding box otherwise.
- The component height is the viewport height minus the current site header height.
- Author geo counters and lists use the current language row from `module_author`.
