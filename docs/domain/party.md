## party (Demoparty, Competitions)

### Purpose
Demoparty or competition - an event where competitions are held for creating graphics, music and programs for ZX Spectrum and other platforms. Central entity for organizing competition works.

### Main Fields
- **title** - party title
- **abbreviation** - abbreviation/short name
- **originalName** - original name (in original language)
- **website** - party website
- **image** - image ID (logo, poster)

### Hierarchy
- **year** - year of event (parent element in structure)
  - Party is always inside year element
  - Retrieved through `getElementsFirstParent()`

### Location
- **country** - country ID where event is held
- **city** - city ID where event is held
  - Implements `LocationProvider` interface

### Relations with Competition Works

#### Competitions (Compos)
Party contains various competitions (compos) where works participate:

##### Graphics competitions
- **partyPicture** - link to pictures
- Method `getPicturesCompos()` - returns pictures grouped by compo
- Field **compo** in zxPicture defines competition name
- Field **partyplace** defines place in competition

##### Music competitions
- **partyMusic** - link to music
- Method `getTunesCompos()` - returns tracks grouped by compo
- Field **compo** in zxMusic defines competition name
- Field **partyplace** defines place in competition

##### Software competitions
- **partyProd** - link to productions (prods and releases)
- Method `getProdsCompos()` - returns prods grouped by compo
- Field **compo** in zxProd/zxRelease defines competition name
- Field **partyplace** defines place in competition

#### Sorting Works in Compo
Works within each compo are sorted by:
1. **partyplace** - place in competition (ascending)
2. **id** - element ID (for stable sorting)

Sort key format: `{partyplace}_{id}`

#### Angular Party Place Display
- Party place numbers are rendered through shared-lib `zx-party-place`.
- `zx-party-place` uses the design system `zx-medal` primitive.
- Places 1, 2, and 3 use gold, silver, and bronze medal variants.
- Any place greater than 3 is still shown and uses the outlined medal variant.
- Place 0 or null is not shown.

#### Grouping by Compo
If work has no compo specified, it goes to group `'none'`

### Statistics
- **picturesQuantity** - number of pictures at party
- **tunesQuantity** - number of tracks at party
- Recalculated automatically through `recalculate()`

### Press
- **mentions** - press mentions (link `PRESS_PARTIES`, role parent)

### Results Export
Party provides methods for exporting results in various formats:

#### Export Formats
- **BB-code** (`getText('bb')`) - for forums
  - Pictures as previews with links
  - Music as list with MP3 links
- **Text** (`getText('text')`) - plain text
  - Full information about works
- **HTML** (`getText('html')`) - HTML markup
  - Embedded images and audio players

#### Export Structure
1. Grouping by compo
2. Reverse order of works (from last to first)
3. Compo name translations through translations:
   - `label.compo_{compo}` - for graphics
   - `musiccompo.compo_{compo}` - for music

### Archive Download
- Method `getSaveUrl()` - URL for downloading all party works in ZIP
- Format: `/zipItems/language:{lang}/filter:partyId={id}/structure:parties/`

### Party Image
- Method `getImageUrl(preset)` - party image URL
- Default preset: `'partyFull'`
- Fallback: `/images/zxprod_default.png`

### Party Collections
- Party collections use the shared responsive party-card list with five desktop, three tablet, and one mobile column.
- The recent-parties homepage module and the routed `/parties` collection use the same list presentation while loading their data independently.
- The routed party collection displays a page heading and an always-visible year link selector whose options match the party-year routes in the main menu.
- Authenticated users can open the Angular party creation form from the collection heading. A year route creates under that year; the collection root uses the newest catalogue year.
- Party creation and editing share the same Angular form component. Creation renders an empty form locally and submits it through `POST /formdata/`; editing loads persisted values through `GET /formdata/?id=`.
- `GET /parties-data/` returns only the 20 most recent parties; `GET /parties-data/?year=YYYY` returns only the parties from that year.
- The base `/parties` route shows the recent response; a year route requests and shows the selected year's response.
- The year selector is ordered from oldest to newest. Parties within a selected year are ordered alphabetically; the recent collection retains newest-first order.
- The base `/parties` route carries a section heading above the list; a year route drops it, since the selected year chip already names the listing.
- The collection can be viewed as responsive cards or a table with party title, year, country and city. Country and city are links to their geo pages and are each omitted when the party has no such link; they are part of the collection payload only (search results carry the plain party shape).
- Initial loading uses a view-specific skeleton that mirrors the active cards or table layout.

### Party Page REST API
The Angular party page (`zx-party-details`) loads data through dedicated endpoints; full schemas in `api/party-details.yaml`.
- `GET /party-details/?id=` - core payload: header, location, compos metadata, editions, counters and tabs. The Angular party page builds its breadcrumb links from the clean `/parties` and `/parties/:year` routes.
- `GET /party-overview/?id=` - "best works" dashboard: the winning entry (lowest positive `partyplace`) of each compo, grouped by medium (`prods`/`pictures`/`tunes`) in full card shapes.
- `GET /party-prods/?id=&compoType=` - one compo's prods (standard prods-list shape).
- `GET /party-pictures/?id=&compoType=` - one compo's pictures (`PictureRestDto` shape).
- `GET /party-music/?id=&compoType=` - one compo's tunes (`TuneRestDto` shape).
- `GET /party-ratings/?id=&page=&perPage=` and `GET /party-comments/?id=&page=&perPage=&lang=` - paginated votes / comments on the party's works (activity tab).

The heavy media (compos tab) is fetched lazily, one compo per request; Overview loads once. Compo names are resolved per medium by `PartyCompoNameResolver` (sections `party`/`zxPicture`/`musiccompo`).

Party edit/delete and upload actions are rendered inside Angular through `zx-party-editing-controls`, using the shared `zx-editing-controls` popover. The legacy `party.all.tpl` only mounts `zx-party-details`; it must not render separate editing buttons.

### Constraints and Rules
1. Party must always be child element of year
2. Each work can participate in only one party
3. Work can participate in one compo at party (compo field is string, not array)
4. partyplace defines place in competition (1 = first place, 2 = second, etc.)
5. If partyplace = 0 or null, work participated outside competition
6. picturesQuantity and tunesQuantity are recalculated automatically when links change
7. country is checked automatically through `checkCountry()`
8. Compo can have any name (string), translations through translations
9. Works in compo are sorted by place, then by ID
10. Party can have no works (new or planned party)
