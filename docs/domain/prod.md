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
- **tagsText** - tags (text)
- **compo** - competition name (compo)
- **language** - interface languages (array)

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
- Prod details hero groups authors by roles before publishers, developer groups, and party metadata. Authors without roles remain under the generic authors label.
- Prod details hero displays the `unknown` author role under the generic authors label, not under the global unknown-role translation.
- Prod details core data must include author/group aliases when they are stored directly in authorship, publishers, or developer group links.
- Product description loading state renders one paragraph skeleton with three thin ribs.
- Emulator screenshots launched from prod details release rows are uploaded to the parent prod. The `uploadScreenshot` privilege must be requested once for the prod element and reused by all release play buttons.

### Release Label Pipe
- `ProdReleaseLabelPipe` (`features/prod-details/pipes/prod-release-label.pipe.ts`) formats a release reference into a display string: `Release Title (Publisher, Type, Year)`. Any of the optional fields (year, type label, publishers) can be omitted.
- Input type: `ProdReleaseLabelInput` — requires `releaseTitle`; optional `releaseYear`, `releaseTypeLabel`, `releaseBy[]`.
- Used in `zx-prod-inlays-section` (figcaption) and `zx-prod-instructions-section` (table cell). Add the pipe to `imports` in any standalone component that needs it.

### Angular Prod Lists
- Outside category browser views, product cards must be rendered through `zx-prods-list`.
- `zx-prods-list` accepts `Observable<ZxProd[] | null>` through `items$`; `null` means "not loaded yet" and renders the list skeleton.
- Each usage configures the skeleton card count locally through `skeletonCount`.
- Prod details related product sections get their observables from `ProdRelatedProdsService`; the service owns `null` loading state and starts REST loading lazily on first subscription.
