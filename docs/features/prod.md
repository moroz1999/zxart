# Productions — implementation

Domain rules: [../domain/prod.md](../domain/prod.md).

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
    a migration extending the enum, an entry in the trait, and the
    `language.<code>` translation in all three i18n bundles. The names are SPA
    translations and are never sent from the backend.
  - The migration **appends** to the enum: appending is an in-place change,
    while inserting in the middle rebuilds the whole table. Enum order carries
    no meaning — the selector is ordered by the trait.

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

A production's press articles are added at `/prod/:id/articles/add`, which opens
the article form in creation mode with the production as `parentId`. The article
is created and saved by the same `publicReceive` action that edits it later, so
the button and the route guard both check `pressArticle.publicReceive` on the
production. The action links the new article to its production with `prodArticle`.

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
Catalogue hardware selectors and product summaries carry each code together with
its name, short name and category, localized for the request language. Only the
category heading is still an SPA string (`hardware-group.<code>`).

### Relations with Other Entities

#### Authorship
- **authors** - authors with roles (code, graphics, music, etc.)

#### Groups and Publishers
- **publishers** - publishers (link `zxProdPublishers`, role child)
- **groups** - developer groups (link `zxProdGroups`, role child)

#### Hardware
- **hardwareRequired** - the production's own hardware: the set shared by its
  releases, so it is not repeated on each of them
  - Stored in `module_zxprod_hw_required` as catalog ids; the property works in codes
  - `getAggregatedHardwareCodes()` adds every release's set — that is what cards,
    structured data, the catalogue selector and the stats charts use
  - Full model, including where the catalog lives: [hardware.md](hardware.md)

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

### Split Form

`/prod/:id/split` renders the Angular split form behind the `showSplitForm`
privilege. `/prod-split-data/` answers with everything the production can give
away, grouped as `properties`, `authors`, `publishers`, `groups`, `releases`,
`screenshots` and `links`; empty groups are omitted. Each item carries the `key`
the `split` action reads it back under — an authorship record for a credit, an
element id for a related entity or a screenshot, `origin;id` for an external
link — plus its title, the page it links to (an SPA route for entities, the
external address for links) and a thumbnail for a screenshot. Group and property
names are SPA translations (`split-form.group-*`, `split-form.property-*`); the
titles come from the elements.

The form posts the checked items as `splitData[group][key] = 1` to the legacy
`split` action. It creates the new production under the same parent and with the
same categories, copies the checked properties to it, moves the checked credits,
publishers, groups, releases, screenshots and import origins over, and answers
with the new id, which the SPA opens.

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
- Articles about the prod and press mentions of it are two different things and get two different tabs:
  - `hasArticles` — the prod's own press articles (`prodArticle` links, written under the prod). They are a top-level tab, `/prod/:id/articles`, holding `zx-prod-articles-section` alone.
  - `hasMentions` — press articles that merely mention the prod (`pressSoftware` links, written under a magazine issue). They stay a sub-tab of the related-links tab, `/prod/:id/mentions`.
  - Each tab is rendered only when its flag is set, and its section loads from its own endpoint when the tab is opened, so a prod with no articles costs no request.
- Both lists render `zx-article-preview` and load behind `zx-article-preview-skeleton`; the mentions list additionally carries the publication cover and read link, which the skeleton takes as flags.
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
- Emulator screenshots launched from prod details release rows are uploaded to the parent prod through `/screenshot-upload/`. The `uploadScreenshot` privilege must be requested once for the prod element and reused by all release play buttons.
- Screenshots come in two kinds: native ZX screens (`scr`, `img`, `mlt`, `ifl`, `ssx`, `s80`, `s81`, …) and PC images (`png`, `gif`, `jpg`, `bmp`). A native screen is rendered by the ZX converter (`zximages`), which reads from both the uploads and the releases folder; a PC image goes through an image preset, and animated `gif` is served whole by `screenshot/`. Because prod and release files live in the releases folder, every preset used for them must declare `'path' => 'releases'` — a preset without it (e.g. `adminImage`) resolves nothing and leaves PC screenshots without a thumbnail. The edit form's file selectors therefore use the same `prodImage` preset as the public gallery.
- The covers of a prod are its own inlays plus the inlays **and** advertising materials (`adFilesSelector`) of its releases. `/prod-inlays/` returns them grouped by kind — `{groups: [{kind: 'inlay'|'ad', items: []}]}`, one group per `ProdCoverKind` that has files, empty kinds omitted — and the page renders a heading per group. The `hasCovers` tab flag counts both link types, so a prod whose releases carry only ads still gets the tab. Cover-kind labels live in the shared `covers.<kind>` translations, because the release page groups its own covers the same way.
- The covers tab is `/prod/:id/covers`. Its previous id, `inlays`, is still accepted and resolves to the same tab: an unknown tab segment silently falls back to the first tab, so dropping the old id would send existing links to the wrong place.
- Cover thumbnails use the `prodCover` preset instead of the screenshot preset: a single `reduce` to 800×200, so neither portrait nor landscape material is cropped. Cover images share a 200px display height; each tile derives its width from the thumbnail's aspect ratio, and wrapping layouts do not stretch tiles into uniform columns.
- Map thumbnails use the same tile component and wrapping layout as covers. The `prodMapImage` preset reduces them to 800×200, and the shared 200px display height leaves each tile's width proportional to its image.
- The full-screen gallery image of a cover uses `prodCoverOriginal`: a preset with no filters at all, so the stored dimensions survive and only the encoding changes. A preset name containing `full` cannot be used for this — `fileElement::getImageUrl()` reads that marker as "skip presets" and links the untouched file through `screenshot/` instead. The download link keeps pointing at `screenshot/`, which serves the stored file byte for byte.
- Both cover presets pin their output to `webp`. A preset that names another format is only converted to webp for browsers the image application recognizes as webp-capable, and it excludes Safari from that list; pinning the format keeps every client on one cached rendition.
- Screenshot upload for prods and releases uses the shared `/screenshot-upload/` endpoint: the raw screen dump is the request body, the target element is `id` and the dump format is `format` (`standard`, `gigascreen`, `s80`, `s81`). The endpoint resolves the element and runs its own `uploadScreenshot` action, which enforces the privilege, validates the body length against the format and stores the file; the response is the updated screenshots payload.
- Screenshot ordering for prods and releases uses the shared `/prod-screenshot-move/` operation; the historical prod URL is retained for client compatibility, while the operation checks `publicReceive` on the target element and reorders its appropriate file link collection.

### Release Label Pipe
- `ProdReleaseLabelPipe` (`features/prod-details/pipes/prod-release-label.pipe.ts`) formats a release reference into a display string: `Release Title (Publisher, Type, Year)`. Any of the optional fields (year, type label, publishers) can be omitted.
- Input type: `ProdReleaseLabelInput` — requires `releaseTitle`; optional `releaseYear`, `releaseTypeLabel`, `releaseBy[]`.
- Used in `zx-prod-inlays-section` (figcaption) and `zx-prod-instructions-section` (table cell). Add the pipe to `imports` in any standalone component that needs it.

### Angular Prod Lists
- Outside specialized views, product cards must be rendered through `zx-prods-list`.
- `zx-prod-block` is product-only: it must not read or render release-specific fields. Release lists must use `zx-prod-release-card` with `ProdReleaseDto`.
- The last breadcrumb of an entity page is the entity's own name, not its SEO text: the prod page trails `core.title`, while the SEO `core.h1` stays reserved for the page heading.
- The release card's expanded section is a list of label/value rows (languages, format, downloads, plays); downloads and plays carry an icon next to their label. The action row below it holds only the play and open buttons, so translated button labels do not have to share the line with counters.
- `zx-prods-list` accepts `Observable<ZxProd[] | null>` through `items$`; `null` means "not loaded yet" and renders the list skeleton.
- Product card grids use `zxProdsGrid`; desktop cards are fixed at `256px` and do not stretch.
- Author software views may render `zx-prod-block` directly because they add author-role metadata around each card, but they must use `zxProdsGrid` for card layout.
- Each usage configures the skeleton card count locally through `skeletonCount`.
- Prod details related product sections get their observables from `ProdRelatedProdsService`; the service owns `null` loading state and starts REST loading lazily on first subscription.
