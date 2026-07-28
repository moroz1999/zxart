## zxProd (Software Production)

### Purpose
Software production for ZX Spectrum - games, demos, utilities and other software. Represents an abstract work that can have multiple concrete releases (zxRelease).

### Main Fields
- **title** - production title
- **altTitle** - alternative title
- **year** - year of creation
- **description** - description (HTML)
- **instructions** - instructions (HTML)
- **youtubeId** - YouTube video ID
- **legalStatus** - legal distribution status:
  - `unknown` - unknown
  - `allowed` - allowed
  - `allowedzxart` - allowed only on zxart
  - `forbidden` - forbidden
  - `forbiddenzxart` - forbidden on zxart
  - `insales` - in sales
  - `mia` - missing in action (lost)
  - `unreleased` - not released
  - `recovered` - recovered
  - `donationware` - donationware
- **externalLink** - external link
  - With `insales` or `donationware` the prod hero bar offers it as a call to action ("purchase" / "donate" button) and the plain link in the links row is labelled as the homepage; otherwise it is only the plain link.
- **tagsText** - tags (text)
- **compo** - competition name (compo)
- **language** - interface languages (array)
  - The codes a prod or release can carry are the `engine_zxitem_language.value`
    enum and `LanguageCodesProviderTrait::getLanguageCodes()`; adding one means
    extending both. Their names are SPA translations (`language.<code>`) and are
    never sent from the backend.

### Catalogue Route

The software catalogue uses `/prods` with Angular Router query parameters. Its
filter state includes `cat`, `years`, `hw`, `languages`, `statuses`, `formats`,
`types`, `letter`, `sorting`, `tags`, `countries`, `releases`,
`includeSubcategoriesProds`, and `page`. Links from production and release
details use the same query parameters.
Authenticated users can open the Angular batch upload form from the catalogue
page heading. It submits through the backend `zxProdsUploadForm` batch pipeline.
The batch page reuses the Angular prod form with batch-specific file fields; it
loads and submits its transient form through `/formdata/`.

The same form is reachable from an author, group or party page
(`/author/:id/prods/add`, `/group/:id/prods/add`, `/party/:id/prods/add`). The
entity id is passed to `/formdata/` as `parentId`, the upload form is created
under that element, and the uploaded productions are attached to it. Pictures and
music have the same routes (`…/pictures/add`, `…/music/add`) backed by the
`picturesUploadForm` and `musicUploadForm` pipelines, and a production's releases
are added at `/prod/:id/releases/add`.

The batch form shows every field its upload pipeline accepts; the values are
shared by all works created from the selected files. `/formdata/` echoes the
`parentId` back as `parent` (id, title, structure type) and the form prefills the
field that element belongs in: the author becomes the works' author (a member for
productions), the party their release party, the group their developer, and a
browsed category the productions' category. The catalogue's upload button
therefore carries the browsed category as `?cat=`. Fields the pipeline cannot
apply are hidden in batch mode (for a production: compilation items, series and
the extra file selectors; for a tune: the playback restriction).
Catalogue responses expose entity identifiers and structure types. Angular
templates build internal routes from those identifiers and do not receive routed
URLs from the API.
When a category is browsed it becomes the page's subject: its name replaces the
section name in the `<h1>` and in the document title, and the catalogue root
restores the route title. The catalogue breadcrumbs show the selected category
chain. The categories
selector marks the whole ancestor chain of the current category as selected, so
the chain is derived from the loaded selector and its links are built from the
category identifiers as `/prods?cat={id}`. At the catalogue root the trail falls
back to the route-driven one built from the menu.
Prod and release detail responses expose category IDs and raw language, hardware,
year, and format values. Angular templates build catalogue filter links from
those values.

### Relations with Other Entities

#### Authorship
- **authors** - authors with roles (code, graphics, music, etc.)

#### Groups and Publishers
- **publishers** - publishers (link `zxProdPublishers`, role child)
- **groups** - developer groups (link `zxProdGroups`, role child)

#### Categories
- **categories** - production categories (array of IDs)
  - Define software type: games, demos, utilities, etc.
  - Special categories for compilations
  - The categories are the production's structural parents, so it can never be
    left without one: the Angular form requires at least one, and the backend
    falls back to `misc` when the submitted set is empty.

#### Production Hierarchy
- **compilationItems** - compilation items (link `compilation`, role parent)
  - If prod is a compilation, contains list of included products
- **compilations** - compilations that include this prod (link `compilation`, role child)
- **seriesProds** - products in series (link `series`, role parent)
  - If prod is a series, contains list of series products
- **series** - series that include this prod (link `series`, role child)

#### Party (Competitions)
- **party** - party ID (demoparty, competition)
- **partyplace** - place in competition
- **compo** - competition name (compo)

#### Press and Articles
- **articles** - articles about the product (link `prodArticle`, role parent)
- **mentions** - press mentions (link `PRESS_SOFTWARE`, role parent)

#### Files and Media
- **connectedFile** - main file
- **inlayFilesSelector** - inlay files (covers)
- **mapFilesSelector** - map files
- **rzx** - playthrough recording files (RZX)
- **screenshots** - screenshots
- **bestPictures** - best pictures

#### Child Elements
- **releases** - product releases (zxRelease, link `structure`)
  - Each prod can have multiple releases
  - Flag **releasesOnly** - show only releases (hide the prod itself)

### Voting and Comments
- **votes** - average rating
- **votesAmount** - number of votes
- **denyVoting** - deny voting
- **commentsAmount** - number of comments
- **denyComments** - deny comments

### Metadata
- **dateAdded** - date added
- **userId** - ID of user who added the element

### Special Operations
- **joinAndDelete** - merge and delete products
- **splitData** - data for splitting product
- **aiRestartSeo** - restart AI for SEO
- **aiRestartIntro** - restart AI for intro
- **aiRestartCategories** - restart AI for categories

### Constraints and Rules
1. Prod is an abstraction - concrete files are stored in releases
2. Prod can be a compilation (contains other prods) or be included in compilations
3. Prod can be a series (contains other prods) or be included in series
4. Prod cannot simultaneously be a compilation and be included in a compilation (cyclic links are forbidden)
5. LegalStatus determines file distribution possibility
6. If releasesOnly flag is set, prod is hidden, only its releases are shown

### Angular Details Page
- Related prod lists (compilation items, compilations, series products) reuse `zx-prods-list`, which renders products through `zx-prod-block`.
- Prod details related prod lists are loaded through REST endpoints; the legacy details template must not inject inline global data.
- The series tab is controlled by two independent flags from the tabs API:
  - `hasSeriesProds` — this prod is a series container (e.g. "Dizzy series"); shows `zx-prod-series-prods-section` which calls `/prod-series-prods/` and returns `$prod->seriesProds`.
  - `isInSeries` — this prod is a member of one or more series; shows `zx-prod-series-section` which calls `/prod-series/` and returns all prods from each series container this prod belongs to.
  - Both flags can be true simultaneously; when either is true the "Series" tab is shown, and both sections appear independently under their own flag.
- `/prod-series/` returns product summaries from the same series as the selected prod, not the series container entity.
- Prod details core data does not include edit/delete privileges. Editing controls use shared `zx-editing-controls` and request privileges separately for authenticated users only.
- Prod editing controls are action buttons, not links. They render through `zx-button` without `href` and navigate to legacy action URLs from click handlers.
- Prod details core data includes the privilege-gated add-release URL. The button opens the legacy `zxRelease` public add form under the current prod.
- Prod details tabs render real links and restore the selected tab from the `/tabs:{id}/` URL segment on load. Nested tab IDs such as `graphics`, `music`, or `series` activate their parent tab automatically.
- The legacy details template mounts `zx-prod-details` directly; Angular renders the page title.
- Prod details hero groups authors by roles before publishers and developer groups. Authors without roles remain under the generic authors label. The party appearance is rendered separately, in the hero provenance callout.
- Prod details hero displays the `unknown` author role under the generic authors label, not under the global unknown-role translation.
- Prod details core data includes `downloadsCount` and `playsCount` summed over the prod's releases: a prod owns no files of its own, so its hero statistics aggregate the release counters.
- Prod details core data must include author/group aliases when they are stored directly in authorship, publishers, or developer group links.
- Product description loading state renders one paragraph skeleton with three thin ribs.
- Emulator screenshots launched from prod details release rows are uploaded to the parent prod. The `uploadScreenshot` privilege must be requested once for the prod element and reused by all release play buttons.
- Screenshot ordering for prods and releases uses the shared `/prod-screenshot-move/` operation; the historical prod URL is retained for client compatibility, while the operation checks `publicReceive` on the target element and reorders its appropriate file link collection.

### Release Label Pipe
- `ProdReleaseLabelPipe` (`features/prod-details/pipes/prod-release-label.pipe.ts`) formats a release reference into a display string: `Release Title (Publisher, Type, Year)`. Any of the optional fields (year, type label, publishers) can be omitted.
- Input type: `ProdReleaseLabelInput` — requires `releaseTitle`; optional `releaseYear`, `releaseTypeLabel`, `releaseBy[]`.
- Used in `zx-prod-inlays-section` (figcaption) and `zx-prod-instructions-section` (table cell). Add the pipe to `imports` in any standalone component that needs it.

### Angular Prod Lists
- Outside specialized views, product cards must be rendered through `zx-prods-list`.
- `zx-prod-block` is product-only: it must not read or render release-specific fields. Release lists must use `zx-prod-release-card` with `ProdReleaseDto`.
- `zx-prods-list` accepts `Observable<ZxProd[] | null>` through `items$`; `null` means "not loaded yet" and renders the list skeleton.
- Product card grids use `zxProdsGrid`; desktop cards are fixed at `256px` and do not stretch.
- Author software views may render `zx-prod-block` directly because they add author-role metadata around each card, but they must use `zxProdsGrid` for card layout.
- Each usage configures the skeleton card count locally through `skeletonCount`.
- Prod details related product sections get their observables from `ProdRelatedProdsService`; the service owns `null` loading state and starts REST loading lazily on first subscription.
