# Angular Rewrite: `zxProd.details.tpl`

## Context

`project/templates/public/zxProd.details.tpl` is the public detail page for a ZX Spectrum production. Today it is a 350-line Smarty template that renders most of the UI server-side and embeds a handful of Angular custom elements.

Goal: replace the entire Smarty body with an Angular page composed of **independently loaded sections**. Each section is its own component with its own endpoint, its own skeleton, and its own intersection-observer-driven lazy load. This makes the first paint fast (only the slim core endpoint is needed for above-the-fold content), keeps each section reorderable / movable into tabs later, and lets us cache / invalidate sections independently.

---

## Architecture: many small endpoints, lazy sections

The Smarty `.tpl` collapses to:

```smarty
{$moduleTitle = $element->getH1()}
{capture assign="moduleContent"}
    <zx-prod-details element-id="{$element->id}"></zx-prod-details>
{/capture}
{assign moduleClass "zxprod_details"}
{assign moduleAttributes "id='gallery_{$currentElement->id}'"}
{include file=$theme->template("component.contentmodule.tpl")}
```

Then `<zx-prod-details>`:

1. Calls `GET /prod-details/?id={id}` immediately (slim core payload — info table + voting + description + emulator type).
2. Renders the core synchronously, then mounts every *section* component below in a fixed order.
3. Each section component sits dormant until scrolled into view (`InViewportDirective`), then fires its own HTTP request and shows a skeleton until data arrives.
4. **Empty-on-load = hide**: when a section endpoint returns an empty list, the section hides itself entirely (no header, no chrome). Until the section is scrolled into view it occupies zero visual height (just the in-viewport sentinel).

No presence flags in the core payload. The backend was already going to enumerate the data when each section endpoint fires; precomputing flags in `/prod-details/` would duplicate that work. The trade-off accepted here: a section that the user scrolls past *and* turns out to be empty costs one HTTP round-trip that returns `[]`. Sections the user never scrolls to cost zero.

---

## Components

All standalone, OnPush, in `features/prod-details/...`. Each section component owns: `.ts` / `.html` / `.scss`, a feature service, a DTO, an `InViewportDirective` lazy trigger, and a skeleton loading state via `ZxSkeletonComponent`.

### Page shell

| Selector | Path | Inputs | Endpoint | Notes |
|---|---|---|---|---|
| `zx-prod-details` | `features/prod-details/components/zx-prod-details/` | `elementId` | `GET /prod-details/?id={id}` (immediate) | Page shell. Reads core payload, renders core blocks synchronously, mounts section components below using presence flags. |

### Core blocks (no separate request — all fed by `/prod-details/` payload)

| Selector | Replaces | Inputs | Notes |
|---|---|---|---|
| `zx-prod-editing-controls` | top admin button row | `prodUrl`, `elementId`, `privileges` | privileges in core payload. **`publicDelete` requires confirmation** — clicking it opens a `zx-confirm-dialog` (see shared primitives below); only on user confirm does the navigation to `{prodUrl}id:{id}/action:publicDelete/` happen. Other actions (`showPublicForm`, `resize`, `join`, `split`, `showAiForm`) navigate directly without confirmation. |
| `zx-prod-info-table` | the info table | `core: ProdCoreDto` | reads everything from core payload |
| `zx-prod-language-links` | `component.languagelinks.tpl` | `languages`, `catalogueBaseUrl` | inside info table |
| `zx-prod-external-links` | `component.links.tpl` row | `links` | inside info table |
| `zx-prod-vote-row` | the votes row + legacy `zx-item-legacy-controls` | `elementId`, `type`, `votes`, `votesAmount`, `userVote`, `denyVoting` | thin wrapper around the existing typed `zx-item-controls` |
| `zx-youtube-embed` (`shared/ui/`) | YouTube iframe | `youtubeId` | shared primitive |
| `zx-prod-description` | `<details>` description block | `description`, `htmlDescription` | uses generic `zx-collapsible-section` |
| `zx-prod-instructions` | `<details>` instructions block | `instructions` | uses generic `zx-collapsible-section` |
| `zx-collapsible-section` (`shared/ui/`) | generic `<details>/<summary>` | `title`, `open?` | shared primitive |

### Lazy-loaded sections (each = own component + own service + own endpoint)

| Selector | Endpoint | Internal reuses | Notes |
|---|---|---|---|
| `zx-prod-screenshots-section` | `GET /prod-screenshots/?id={id}` | existing `PictureGalleryHostComponent` (`features/picture-gallery/`) | Replaces the `zxItem.images.tpl` include for `connectedFile`. |
| `zx-prod-inlays-section` | `GET /prod-inlays/?id={id}` | `PictureGalleryHostComponent` | Replaces `zxItem.images.tpl` include for `inlayFilesSelector`. |
| `zx-prod-maps-section` | `GET /prod-maps/?id={id}` | `PictureGalleryHostComponent` | Replaces `zxItem.images.tpl` include for `mapFilesSelector`. Includes `mapsUrl` from speccyMaps. |
| `zx-prod-releases-section` | `GET /prod-releases/?id={id}` | internal `zx-prod-release-row` (table row component) | Full Angular rewrite of `component.releasestable.tpl` + `zxRelease.table.tpl`. ~15 columns. Uses `LegacyPlayButtonComponent` for play. The release row hosts its own screenshots block (see below). |
| `zx-prod-release-row` | `GET /release-screenshots/?id={releaseId}` (per-release, lazy on scroll) | `PictureGalleryHostComponent` for the row's screenshots | One row per release in the releases table. Screenshots for that specific release lazy-load when the row scrolls into view. No aggregated endpoint. |
| `zx-prod-articles-section` | `GET /prod-articles/?id={id}` | new shared `zx-press-article-card` (built from scratch — see below) | Replaces `component.pressArticles.tpl`. |
| `zx-prod-mentions-section` | `GET /prod-mentions/?id={id}` | `zx-press-article-card` | Replaces `component.mentions.tpl`. |
| `zx-prod-compilation-items-section` | `GET /prod-compilation-items/?id={id}` | new shared `zx-prod-card` (or reuse existing list-card if suitable) | "Содержимое сборника" — prods this compilation includes. |
| `zx-prod-series-prods-section` | `GET /prod-series-prods/?id={id}` | `zx-prod-card` | "Программы из серии" — prods this series includes. |
| `zx-prod-compilations-section` | `GET /prod-compilations/?id={id}` | `zx-prod-card` | "Включён в сборники" — compilations that contain this prod. |
| `zx-prod-series-section` | `GET /prod-series/?id={id}` | `zx-prod-card` | "Программа из серии" — series this prod belongs to. For each series, a header link plus its sibling list (matches lines 330–340 of the current template). |
| `zx-prod-music-section` | `GET /musiclist/?elementId={id}` (existing) | existing `zx-music-list` component, wrapped with `InViewportDirective` + skeleton | Section header + lazy-mount the existing tunes list. |
| `zx-prod-pictures-section` | `GET /picturelist/?elementId={id}` (existing) | existing `zx-pictures-list` | Section header + lazy-mount. |
| `zx-prod-rzx-section` | `GET /prod-rzx/?id={id}` | new tiny `zx-prod-files-list` (replaces `zxItem.files.tpl`) | RZX files block. |

### Components reused as-is from elsewhere

| Selector | Source | Notes |
|---|---|---|
| `zx-comments-list` | `features/comments/` | Placed at the page level with typed `[elementId]`. Already does its own load. |
| `zx-ratings-list` | `features/ratings/` | Same. |
| `zx-tags-quick-form` | `features/tags-quick-form/` | Same. Already lazy via `InViewportDirective`. |
| `zx-item-controls` | `shared/ui/zx-item-controls/` | Used inside `zx-prod-vote-row`. Typed bindings, NOT the legacy bridge. |
| `LegacyPlayButtonComponent` | `features/player/` | Used inside the releases table for play buttons. Click triggers `EmulatorModalService.open(...)` — see "Emulator: modal launcher" below. |

### Emulator: modal launcher (Angular reimplementation)

The emulator opens in a modal dialog on play-button click. The WASM bundles under `htdocs/libs/{us,zx81,mame,mamenextsam}/` are **not** touched — we only rewrite the JS launcher (`project/js/public/component.emulator.js`) in Angular. There are several engines the launcher must dispatch to, picked by the release's `emulatorType` (resolved on the backend by `EmulatorResolverService` from `hardwareRequired` + `releaseFormat`):

- `usp` — Unreal Speccy Portable (`/libs/us/unreal_speccy_portable.js`); 48 / 128 / Gigascreen formats; supports F2 screenshot upload.
- `zx81` — ZX81 emulator (`/libs/zx81/`); also covers ZX80 hardware.
- `tsconf` — TS-Conf (likely under `/libs/us/` or its own bundle — verify during implementation).
- `samcoupe` — Sam Coupé (MAME-based, `/libs/mamenextsam/`).
- `zxnext` — ZX Spectrum Next (MAME-based, `/libs/mamenextsam/` or `/libs/mame/` — verify).

| Piece | Path | Purpose |
|---|---|---|
| `EmulatorModalService` | `features/emulator/services/emulator-modal.service.ts` | `open({emulatorType, fileUrl, uploadUrl, canScreenshot})` — opens the dialog via `@angular/cdk/dialog` (`Dialog` / `DialogRef`), same API `SearchDialogComponent` uses. |
| `zx-emulator-dialog` | `features/emulator/components/zx-emulator-dialog/` | Dialog body: canvas, fullscreen button, screenshot-format selector (USP only), notes block (e.g. samcoupe note from `emulator.samcoupe` translation), close button. Picks the engine adapter by `emulatorType`, hands it the canvas + file URL. |
| `EmulatorEngine` (interface) | `features/emulator/engines/emulator-engine.ts` | `start(canvas, fileUrl): Promise<void>`, `setFullscreen(): void`, `captureScreenshot(format): Promise<Blob \| null>` (optional — only USP implements it), `destroy(): void`. |
| `UspEngine`, `Zx81Engine`, `TsconfEngine`, `SamcoupeEngine`, `ZxNextEngine` | `features/emulator/engines/{usp,zx81,tsconf,samcoupe,zxnext}.engine.ts` | One adapter per WASM bundle. Each adapter lazily injects its `<script>` from `/libs/.../*.js`, sets up `window.Module` (`canvas`, `locateFile`, `onReady`), then calls `Module.ccall('OpenFile', ...)` etc. The WASM globals (`window.Module`, `FS`, etc.) are **not** changed. Adapters guard against double-load by reusing an existing `window.Module` if it already matches the engine. |
| `EmulatorScreenshotService` | `features/emulator/services/emulator-screenshot.service.ts` | The F2 screenshot logic currently in `component.emulator.js`: reads from emscripten FS, slices VRAM by 48/128/giga selector, POSTs to `{prodUrl}id:{releaseId}/action:uploadScreenshot/format:standard\|gigascreen`. Used only by `UspEngine`. F2 keybinding registered as a `@HostListener('window:keydown.F2')` on the dialog. |
| `LegacyPlayButtonComponent` (existing, updated) | `features/player/` | Click triggers `EmulatorModalService.open(...)`. The release row passes `emulatorType`, file URL, and `uploadUrl`. |

The modal is created once per launch and torn down on close. The dialog mounts the engine adapter, which loads its WASM bundle on first use of that engine in the session and reuses it on subsequent opens. The legacy `project/js/public/component.emulator.js` and `project/templates/public/component.emulator.tpl` are **deleted** after this rewrite — no `window.emulatorComponent` global, no `.emulator` DOM target.

### New shared primitives created for this work

| Selector | Path | Purpose |
|---|---|---|
| `zx-youtube-embed` | `shared/ui/zx-youtube-embed/` | Reusable YouTube iframe. |
| `zx-collapsible-section` | `shared/ui/zx-collapsible-section/` | Reusable `<details>/<summary>` with optional `open` state. |
| `zx-press-article-card` | `shared/ui/zx-press-article-card/` | **Built from scratch** — no existing equivalent. Fields: title, url, intro (sanitised HTML), authors, year. Used by both `zx-prod-articles-section` and `zx-prod-mentions-section`. |
| `zx-prod-card` | `shared/ui/zx-prod-card/` (or reuse if `entities/zx-prods-list` already has one extractable) | Compact prod card for related-prods sections. Fields: title, url, year, votes, primary image, legalStatus. If a suitable card already exists inside `entities/zx-prods-list/`, **extract it** rather than duplicate. |
| `zx-section-host` (optional) | `shared/ui/zx-section-host/` | Helper that bundles "title + skeleton + InViewport trigger + error / empty branches" so each section component doesn't repeat the boilerplate. Lift only after at least 3 sections look identical. |
| `zx-confirm-dialog` + `ConfirmDialogService` | `shared/ui/zx-confirm-dialog/` | Generic confirmation dialog. `ConfirmDialogService.confirm({title, message, confirmLabel, cancelLabel, danger?: boolean}): Observable<boolean>`. Built on `@angular/cdk/dialog` (`DialogRef`) — same API the existing `SearchDialogComponent` uses. Used here for `publicDelete`, but designed for reuse anywhere a destructive action needs confirmation. |

---

## Server endpoints

### Existing, reused as-is

| Endpoint | Used by |
|---|---|
| `GET /comments/id:{id}/`, `POST /comments/` | `zx-comments-list` |
| `GET /ratings/id:{id}/` | `zx-ratings-list` |
| `GET /tags/?id={id}`, `POST /tags/?id={id}` | `zx-tags-quick-form` |
| `GET /element-privileges/?id={id}&privileges=...` | `zx-tags-quick-form` (page-level privileges come from core payload) |
| `GET /musiclist/?elementId={id}` | `zx-prod-music-section` (wraps `zx-music-list`) |
| `GET /picturelist/?elementId={id}` | `zx-prod-pictures-section` (wraps `zx-pictures-list`) |
| `POST /{slug}/action:uploadScreenshot/` | emulator F2 screenshot |
| `POST /{slug}/action:vote/` (existing) | `zx-item-controls` via `VoteService` |

### New endpoints (one per section)

All under `project/core/ZxArt/Controllers/` following the `Tags.php` shape (`LoggedControllerApplication`, `json` renderer, constructor DI for `controller` / `Logger` / domain service / `ObjectMapper`, `assignError` helper, HTTP status via `CmsHttpResponse`, no legacy `responseStatus` wrapper). **Do not** copy `Prodlist.php` — it pulls services via `$this->getService()` which is the legacy pattern we are moving away from.

| Endpoint | Returns | Notes |
|---|---|---|
| `GET /prod-details/?id={id}` | `ProdCoreRestDto` — slim core: identity, info-table data (categoriesPaths, languages, hardware, links, party, authors, publishers, groups, year, legalStatus, externalLink), voting state, submitter, description, instructions, generatedDescription, h1, prodUrl, privileges. **No** heavy lists, **no** presence flags, **no** emulator info (emulator is launched per-release from the releases table). | First request on page load; everything above-the-fold is rendered from this. |
| `GET /prod-releases/?id={id}` | `{releases: ProdReleaseRestDto[]}` | Full release rows with all 15 column fields (title, year, version, releaseType, hardwareRequired, languages, party, partyplace, fileFormats, isDownloadable, downloadUrl, isPlayable, playUrl, externalLinks, downloadsCount, playsCount, legalStatus, externalLink). |
| `GET /prod-screenshots/?id={id}` | `{files: ProdFileRestDto[]}` | Files from `getFilesList('connectedFile')` mapped with preset `prodImage`. |
| `GET /prod-inlays/?id={id}` | `{files: ProdFileRestDto[]}` | `getFilesList('inlayFilesSelector')`, preset `prodImage`. |
| `GET /prod-maps/?id={id}` | `{files: ProdFileRestDto[], mapsUrl?: string}` | `getFilesList('mapFilesSelector')`, preset `prodMapImage`, plus `getSpeccyMapsUrl()`. |
| `GET /release-screenshots/?id={releaseId}` | `{files: ProdFileRestDto[]}` | **Per-release**, not per-prod. Each `zx-prod-release-row` calls this for its own `releaseId` lazily on scroll. Source: `getFilesList('screenshotsSelector')` on the `zxRelease` element. |
| `GET /prod-rzx/?id={id}` | `{files: ProdFileRestDto[]}` | `getFilesList('rzx')`. |
| `GET /prod-articles/?id={id}` | `{articles: PressArticlePreviewRestDto[]}` | From `articles` link. |
| `GET /prod-mentions/?id={id}` | `{articles: PressArticlePreviewRestDto[]}` | From `mentions` link. |
| `GET /prod-compilation-items/?id={id}` | `{prods: ProdSummaryRestDto[]}` | `compilationItems` mapped from `getElementData('list')`. |
| `GET /prod-series-prods/?id={id}` | `{prods: ProdSummaryRestDto[]}` | `seriesProds` mapped. |
| `GET /prod-compilations/?id={id}` | `{prods: ProdSummaryRestDto[]}` | `compilations` mapped. |
| `GET /prod-series/?id={id}` | `{series: [{id, title, url, prods: ProdSummaryRestDto[]}]}` | Each series this prod belongs to, with the series' own sibling prods inlined (matches the legacy template behaviour at lines 330–340). |

All endpoints validate `id`, return 400 on missing / non-numeric, 404 if the element does not exist or is not a `zxProd`, 500 on internal errors.

**Controller pattern: constructor DI, not `getService()`.** Use `Tags.php` as the reference, **not** `Prodlist.php`. Dependencies (services, `ObjectMapper`, `Logger`, `controller`) are declared as `private readonly` constructor parameters and resolved by PHP-DI. No `$this->getService(...)` calls in new controllers. Same rule for the new domain services (`ProdCoreService`, etc.) — they receive their collaborators (`structureManager`, `LanguagesManager`, `translationsManager`, `linksManager`, `Connection`, etc.) via constructor injection. Register any service that needs explicit wiring (e.g. binding `'publicStructureManager'` to the public SM factory) in `project/core/di-definitions.php`.

---

## PHP changes

### Service layer

A single `project/core/ZxArt/Prods/ProdDetailsService.php` does NOT make sense here — too many independent responsibilities. Split:

| Service | File | Responsibility |
|---|---|---|
| `ProdCoreService` | `project/core/ZxArt/Prods/ProdCoreService.php` | Builds the slim `ProdCoreDto` for `/prod-details/`. |
| `ProdReleasesService` | `project/core/ZxArt/Prods/ProdReleasesService.php` | Builds release row DTOs. |
| `ProdMediaService` | `project/core/ZxArt/Prods/ProdMediaService.php` | Builds file-DTO arrays for screenshots / inlays / maps / release-screenshots / rzx (one method per type, shared mapping helpers). |
| `ProdRelatedProdsService` | `project/core/ZxArt/Prods/ProdRelatedProdsService.php` | Builds prod-summary arrays for compilationItems / seriesProds / compilations / series. |
| `ProdArticlesService` | `project/core/ZxArt/Prods/ProdArticlesService.php` | Builds article previews for articles / mentions. |

All five services take `int $elementId`, resolve the element via `publicStructureManager`, validate it is `zxProd`, return DTOs (or `null` when not found — controller maps to 404).

**Service pattern: constructor DI.** All collaborators (`structureManager` — bound to `'publicStructureManager'`, `LanguagesManager`, `translationsManager`, `linksManager`, `Connection`, `ObjectMapper`, etc.) come in via `__construct(private readonly ...)`. No `$this->getService(...)` inside the new services. Register the public-SM binding for these services in `project/core/di-definitions.php` (the `Rss` registration there already shows the pattern for binding `'publicStructureManager'` into a constructor argument).

### Controllers

One controller per endpoint under `project/core/ZxArt/Controllers/`. Each is ~30–50 lines, all follow the same shape as `Tags.php` (constructor DI, json renderer, HTTP status codes). Class names are PascalCase derived from the URL via `ucwords($url, '-')` + dash strip — see `controller::detectApplication()`. So URL `/prod-details/` maps to `ProdDetails`:

- `ProdDetails.php` (core)
- `ProdReleases.php`
- `ProdScreenshots.php`
- `ProdInlays.php`
- `ProdMaps.php`
- `ReleaseScreenshots.php`
- `ProdRzx.php`
- `ProdArticles.php`
- `ProdMentions.php`
- `ProdCompilationItems.php`
- `ProdSeriesProds.php`
- `ProdCompilations.php`
- `ProdSeries.php`

Each is registered through the existing controller-discovery mechanism (whatever `Prodlist.php` uses).

### DTOs

In `project/core/ZxArt/Prods/Dto/` (internal) and `project/core/ZxArt/Prods/Rest/` (REST) — split because that is the existing convention shown in `Prodlist.php` (`ProdDto` → `ProdRestDto` via Symfony `ObjectMapper`).

- `ProdCoreDto` / `ProdCoreRestDto`
- `ProdReleaseDto` / `ProdReleaseRestDto`
- `ProdFileDto` / `ProdFileRestDto`
- `ProdExternalLinkDto` / `ProdExternalLinkRestDto`
- `ProdCategoryPathDto` / `ProdCategoryPathRestDto`
- `ProdAuthorInfoDto` / `ProdAuthorInfoRestDto`
- `ProdSummaryDto` / `ProdSummaryRestDto`
- `PressArticlePreviewDto` / `PressArticlePreviewRestDto`
- `ProdSeriesEntryDto` / `ProdSeriesEntryRestDto`
- `ProdPrivilegesDto` / `ProdPrivilegesRestDto`

### OpenAPI

One YAML per endpoint under `api/`:

- `api/prod-details.yaml`
- `api/prod-releases.yaml`
- `api/prod-screenshots.yaml` (+ inlays / maps / release-screenshots / rzx — these can share a YAML file `api/prod-files.yaml` exposing parametrised paths if it stays clean)
- `api/prod-articles.yaml`, `api/prod-mentions.yaml` (could also share)
- `api/prod-related-prods.yaml` (compilationItems / seriesProds / compilations / series — share)

`api/api.yaml` references all of them.

### Sources of data — already on `zxProdElement`

All endpoints pull from existing methods on `zxProdElement` / `zxReleaseElement`. No new methods on element classes:

- info table: `getCategoriesPaths`, `getLanguagesInfo`, `getHardwareInfo`, `getLinksInfo`, `getPartyInfo`, `getAuthorsInfo('prod')`, `getPublishersInfo`, `getGroupsInfo`, `getTagsList`, `getUserVote`, `getVotePercent`, `isVotingDenied`, `getUserElement`, `getDescription`, `getGeneratedDescription`, `getH1`, `getMetaTitle`, `getEmulatorType`, `getUrl`
- per-section: `getReleasesList()`, `getFilesList(...)`, `getSpeccyMapsUrl`, plus `getElementData('list')` for prod summaries
- per-release screenshots: `getFilesList('screenshotsSelector')` on `zxReleaseElement`
- core: `getEmulatorType()` is no longer needed in the core payload (emulator is launched per-release from the releases table); kept on releases instead via `zxReleaseElement::getEmulatorType()`

### Privileges in core payload

The `privileges` block on `ProdCoreDto` covers the buttons at the top of the page: `showPublicForm`, `showAiForm`, `resize`, `join`, `split`, `publicDelete`, `addRelease`, `addPressArticle`. Computed via the existing `userManager` / `privilegesManager` chain — same source as `currentElementPrivileges` in the Smarty template today.

### Smarty `.tpl` rewrite
After this change, `project/templates/public/zxProd.details.tpl` is just the shell shown in the Architecture section above. No `<script>` injection. No `window.galleriesInfo` / `window.elementsData` / `window.prodsList`.

---

## Translations

Add a `prod-details` namespace to `ng-zxart/src/assets/i18n/{en,ru,es}.json`. Keys mirror the section titles and labels currently in `zxProd.details.tpl`:

```
prod-details.{title,altTitle,externallink,categories,language,legalstatus,
              groups,publishers,authors,party,year,tags,votes,addedby,added,
              articles,mentions,releases,maps,inlays,screenshots,music,pictures,
              rzx,description,instructions,
              compilationItems,seriesProds,compilations,series,
              edit,resize,join,split,delete,addrelease,addpressarticle,
              delete-confirm-title,delete-confirm-message,delete-confirm-yes,delete-confirm-cancel,
              purchase,donate,open_externallink,
              role_*,error,empty,loading,retry}
```

Pre-translated values (legalStatus label, language title, hardware title, party compo label, link names) come *resolved* in the REST payload — Angular does not translate them.

**Translation source:** existing strings can be lifted from the legacy translation cache at `/temporary/translations/` — the keys used in `zxProd.details.tpl` (`zxprod.title`, `zxprod.delete`, `zxprod.purchase`, `legalstatus.*`, `party.compo_*`, `links.link_*`, etc.) all have ru/en/es entries cached there. Re-key them under the new `prod-details.*` namespace when copying into `assets/i18n/{en,ru,es}.json`.

---

## Files to modify / create

### PHP
- **new** `project/core/ZxArt/Controllers/ProdDetails.php`, `ProdReleases.php`, `ProdScreenshots.php`, `ProdInlays.php`, `ProdMaps.php`, `ReleaseScreenshots.php`, `ProdRzx.php`, `ProdArticles.php`, `ProdMentions.php`, `ProdCompilationItems.php`, `ProdSeriesProds.php`, `ProdCompilations.php`, `ProdSeries.php`.
- **new** `project/core/ZxArt/Prods/ProdCoreService.php`, `ProdReleasesService.php`, `ProdMediaService.php`, `ProdRelatedProdsService.php`, `ProdArticlesService.php`.
- **new** DTOs under `project/core/ZxArt/Prods/Dto/` and `project/core/ZxArt/Prods/Rest/` (list above).
- **new** YAML specs under `api/` (list above); **edit** `api/api.yaml`.
- **edit** `project/templates/public/zxProd.details.tpl` — collapse to thin shell.

### Angular
- **new feature folder** `ng-zxart/src/app/features/prod-details/`:
  - `components/zx-prod-details/` (page shell)
  - `components/zx-prod-{editing-controls,info-table,language-links,external-links,vote-row,description,instructions}/` (core blocks)
  - `components/zx-prod-{screenshots,inlays,maps,releases,articles,mentions,compilation-items,series-prods,compilations,series,music,pictures,rzx}-section/` (lazy sections)
  - `components/zx-prod-release-row/` (releases-table row — owns its own per-release screenshot lazy load via `/release-screenshots/?id={releaseId}`)
  - `services/prod-{core,releases,media-screenshots,media-inlays,media-maps,release-screenshots,media-rzx,articles,mentions,compilation-items,series-prods,compilations,series}-api.service.ts` — one per endpoint (or fewer if grouping cleanly).
  - `models/` — TS DTOs mirroring the REST shapes 1:1.
- **new feature folder** `ng-zxart/src/app/features/emulator/`:
  - `services/emulator-modal.service.ts`
  - `services/emulator-screenshot.service.ts` (USP-only)
  - `engines/emulator-engine.ts` (interface) + `usp.engine.ts`, `zx81.engine.ts`, `tsconf.engine.ts`, `samcoupe.engine.ts`, `zxnext.engine.ts`
  - `components/zx-emulator-dialog/`
  - Updates to `features/player/legacy-play-button/` so clicks open the modal via `EmulatorModalService` instead of mounting an inline canvas.
- **new shared** `ng-zxart/src/app/shared/ui/zx-youtube-embed/`, `zx-collapsible-section/`, `zx-press-article-card/`, `zx-prod-card/` (or extract from existing), `zx-confirm-dialog/` (with companion `ConfirmDialogService`).
- **edit** `ng-zxart/src/app/app.module.ts` — register `zx-prod-details` (and only that one) as a custom element. All sections are inner components, not custom elements.
- **edit** `ng-zxart/src/assets/i18n/{en,ru,es}.json` — add `prod-details` namespace.

### Legacy templates that become dead
After verification with `grep -rn`, these can be deleted (only if not referenced from other prod-detail-like pages):
- `project/templates/public/component.releasestable.tpl`
- `project/templates/public/component.pressArticles.tpl`
- `project/templates/public/component.mentions.tpl`
- `project/templates/public/component.links.tpl`
- `project/templates/public/component.languagelinks.tpl`
- `project/templates/public/zxItem.images.tpl` (only if unused elsewhere)
- `project/templates/public/zxItem.files.tpl` (only if unused elsewhere)
- `project/templates/public/tags.form.tpl` (already a thin Angular wrapper — keep or delete based on other callers)
- `project/templates/public/component.emulator.tpl` — **delete**, replaced by Angular `zx-emulator-dialog`.
- `project/js/public/component.emulator.js` — **delete**, launcher logic reimplemented in Angular engines (see "Emulator: modal launcher").

The WASM bundles under `htdocs/libs/{us,zx81,mame,mamenextsam}/` are kept as-is — Angular engines load them via dynamic `<script>` injection.

---

## Confirmed decisions

1. **Many small endpoints, not one.** Each page section has its own endpoint and its own component. Slim `/prod-details/` is the only synchronous request; everything else lazy-loads on scroll.
2. **No presence flags in the core payload.** Each section component decides itself whether to render based on its own response (empty → hides). Computing flags upfront would duplicate the same DB work the section endpoints already do.
3. **Lazy sections.** Every section uses `InViewportDirective` to defer its HTTP request until in view, with `ZxSkeletonComponent` while loading.
4. **Emulator runs in a modal** (`@angular/cdk/dialog`), not inline. The JS launcher (`component.emulator.js` + `component.emulator.tpl`) is **rewritten in Angular** as `EmulatorModalService` + per-engine adapters (`UspEngine`, `Zx81Engine`, `TsconfEngine`, `SamcoupeEngine`, `ZxNextEngine`) — one adapter per WASM bundle, dispatched by release `emulatorType`. The WASM bundles themselves (`htdocs/libs/{us,zx81,mame,mamenextsam}/`) are not modified.
5. **Releases table: full Angular rewrite** as `zx-prod-releases-section`, fed from `/prod-releases/`.
6. **`zx-press-article-card` is built from scratch.** No existing equivalent in `shared/ui/`.
7. **`zx-prod-card`**: extract from existing `entities/zx-prods-list/` if a card is already there; otherwise build new in `shared/ui/`. Decide during implementation.
8. **Routing: no Angular router for v1.** Legacy URL still resolves via the Smarty shell; the shell mounts the page custom element.

---

## Verification

1. `composer run build` — Angular build must pass.
2. Load `/zxprod/{id}-{slug}/` in a browser at three privilege levels (anonymous, regular user, admin). Verify:
   - Core info table renders identical to legacy (visual diff).
   - Admin / edit buttons appear/disappear per privilege.
   - Voting widget reflects user's vote.
   - Each section renders its skeleton while loading, then content; empty sections do not appear at all.
   - Comments / ratings / tags / music / pictures load.
   - Releases table — play opens emulator, download buttons fire, external link icons go to right URLs.
   - Screenshots / inlays / maps galleries open lightbox.
   - YouTube embed loads when `youtubeId` set.
   - Description / instructions toggles work.
   - Compilations / series / seriesProds / compilationItems / series-with-siblings sections render.
3. Network-tab check: only `/prod-details/` fires on initial paint. All section endpoints (including `/comments/`, `/ratings/`, `/musiclist/`, `/picturelist/`, `/tags/`, `/element-privileges/` and the new `/prod-*/` endpoints) fire only after their section scrolls into view.
4. `composer psalm` and `composer test` clean.
5. Spot-check three prod types: a compilation (has compilationItems), a series (has seriesProds), a single prod with multiple releases.

---

## Progress tracking

This task spans many sessions. Progress is tracked **in this file** via the checklist below — each new session starts by:

1. Re-reading `AGENTS.md` and the docs linked from it for the relevant area (per CLAUDE.md rule).
2. Re-reading this plan file (the contract may have evolved).
3. Picking the first unchecked item, doing it, flipping the checkbox in the same commit, and committing only after the change is verified (build / psalm / spot-check passes).

Rules for the checklist:
- One checkbox = one cohesive change that can be reviewed and merged on its own.
- Flip the checkbox in the same commit as the work — never in a follow-up.
- If a checkbox turns out to be wrong (out of date with the plan or impossible as written), edit the plan first, then proceed.
- New checkboxes can be added on the fly when something not covered comes up — write them in the place that matches the natural ordering, not at the end.

Phases are ordered by dependency: PHP contracts first (they unblock the Angular work), then shared primitives, then emulator (it's a self-contained dialog used by releases), then the page shell + core blocks, then each lazy section in turn, finally legacy cleanup.

### Phase 1 — PHP contracts and endpoints

- [x] Skeleton: empty `ProdDetails` controller + `ProdCoreService` + `ProdCoreDto`/`ProdCoreRestDto` returning a stub. OpenAPI `api/prod-details.yaml` + entry in `api/api.yaml`. Verifies the wiring (DI, routing, json renderer, HTTP status codes) before any real data shape lands.
- [x] Fill `ProdCoreDto` with info-table fields (categoriesPaths, languages, hardware, links, party, authors, publishers, groups, year, legalStatus, externalLink) + voting state + submitter + description blocks + privileges + prodUrl. Introduces `ZxArt\Shared\StructureType` enum for `structureType` strings (distinct from `EntityType`).
- [x] `ProdReleases` controller + `ProdReleasesService` + `ProdReleaseDto`/`ProdReleaseRestDto` with all 15 release-row fields. OpenAPI `api/prod-releases.yaml`. Shared `ProdInfoBuilder` extracted (party / languages / hardware / external links) to deduplicate across `ProdCoreService` and `ProdReleasesService`. `zxReleaseElement` magic properties (`compo`, `partyplace`, `party`, typed `hardwareRequired`/`language`/`releaseFormat`) annotated.
- [ ] `ReleaseScreenshots` controller + media service method for per-release screenshots (`getFilesList('screenshotsSelector')` on `zxRelease`). OpenAPI.
- [ ] `ProdScreenshots` / `ProdInlays` / `ProdMaps` controllers + `ProdMediaService` (shared helpers for file → DTO mapping). OpenAPI (likely shared `api/prod-files.yaml`).
- [ ] `ProdRzx` controller (reuses `ProdMediaService`). OpenAPI.
- [ ] `ProdArticles` / `ProdMentions` controllers + `ProdArticlesService` + `PressArticlePreviewDto`/`...RestDto`. OpenAPI.
- [ ] `ProdCompilationItems` / `ProdSeriesProds` / `ProdCompilations` / `ProdSeries` controllers + `ProdRelatedProdsService` + `ProdSummaryDto`/`...RestDto`. `ProdSeries` returns `[{id, title, url, prods: ProdSummaryRestDto[]}]`. OpenAPI.
- [ ] DI definitions: bind `'publicStructureManager'` into the new services in `project/core/di-definitions.php`.
- [ ] `composer psalm` and `composer test` clean for PHP changes.

### Phase 2 — Angular shared primitives

- [ ] `zx-confirm-dialog` + `ConfirmDialogService` (`shared/ui/zx-confirm-dialog/`). Built on `@angular/cdk/dialog`. Add `prod-details.delete-confirm-*` translation keys.
- [ ] `zx-collapsible-section` (`shared/ui/zx-collapsible-section/`) — generic `<details>/<summary>` with optional `open` input.
- [ ] `zx-youtube-embed` (`shared/ui/zx-youtube-embed/`) — iframe with `youtubeId` input.
- [ ] `zx-press-article-card` (`shared/ui/zx-press-article-card/`) — built from scratch.
- [ ] `zx-prod-card` (`shared/ui/zx-prod-card/`) — extract from `entities/zx-prods-list/` if a card lives inside it; otherwise build new.

### Phase 3 — Emulator (Angular reimplementation)

- [ ] `EmulatorEngine` interface (`features/emulator/engines/emulator-engine.ts`) + scaffold for 5 engines (`usp`, `zx81`, `tsconf`, `samcoupe`, `zxnext`). Engines start as no-ops; only `UspEngine` is fully implemented in this checkbox.
- [ ] `EmulatorScreenshotService` (USP only) — port screenshot logic from `component.emulator.js`. F2 keydown via `@HostListener('window:keydown.F2')` on the dialog.
- [ ] `zx-emulator-dialog` component + `EmulatorModalService` (`@angular/cdk/dialog`). `LegacyPlayButtonComponent` updated to call `EmulatorModalService.open(...)` instead of mounting an inline canvas.
- [ ] Implement `Zx81Engine`. Verify with a release using zx81 hardware.
- [ ] Implement `TsconfEngine`. Verify.
- [ ] Implement `SamcoupeEngine`. Verify.
- [ ] Implement `ZxNextEngine`. Verify.
- [ ] Delete `project/js/public/component.emulator.js` and `project/templates/public/component.emulator.tpl`. Confirm no other callers via `grep -rn`.

### Phase 4 — Angular page shell and core blocks

- [ ] `zx-prod-details` page shell + `ProdCoreApiService` + TS DTO. Fetches `/prod-details/?id={id}` and renders a placeholder. Registered in `app.module.ts` `ngDoBootstrap()`.
- [ ] Smarty `.tpl` collapsed to the shell shown in the Architecture section. Old admin/info markup deleted.
- [ ] `zx-prod-info-table` + child blocks `zx-prod-language-links`, `zx-prod-external-links`. Reads from core payload.
- [ ] `zx-prod-editing-controls` — `publicDelete` wired through `ConfirmDialogService` before navigation.
- [ ] `zx-prod-vote-row` (wraps typed `zx-item-controls`).
- [ ] `zx-prod-description` + `zx-prod-instructions` (built on `zx-collapsible-section`).
- [ ] YouTube embed wired via `zx-youtube-embed` (when `youtubeId` set).
- [ ] `prod-details` i18n namespace populated for ru/en/es from `/temporary/translations/`.

### Phase 5 — Lazy sections

Each is its own checkbox: section component + per-section service + lazy `InViewportDirective` trigger + skeleton + empty-hides-self behaviour. Order matches expected user value, but they are independent and can be done in any order.

- [ ] `zx-prod-screenshots-section` + `/prod-screenshots/`. Reuses `PictureGalleryHostComponent`.
- [ ] `zx-prod-releases-section` + `zx-prod-release-row` + `/prod-releases/` + `/release-screenshots/?id={releaseId}` (per row). Play buttons wired to `EmulatorModalService`.
- [ ] `zx-prod-articles-section` + `/prod-articles/`. Uses `zx-press-article-card`.
- [ ] `zx-prod-mentions-section` + `/prod-mentions/`. Uses `zx-press-article-card`.
- [ ] `zx-prod-compilation-items-section` + `/prod-compilation-items/`. Uses `zx-prod-card`.
- [ ] `zx-prod-series-prods-section` + `/prod-series-prods/`.
- [ ] `zx-prod-compilations-section` + `/prod-compilations/`.
- [ ] `zx-prod-series-section` + `/prod-series/` (renders each series as a header + sibling list).
- [ ] `zx-prod-music-section` (lazy wrapper around existing `zx-music-list`).
- [ ] `zx-prod-pictures-section` (lazy wrapper around existing `zx-pictures-list`).
- [ ] `zx-prod-inlays-section` + `/prod-inlays/`.
- [ ] `zx-prod-maps-section` + `/prod-maps/` (with `mapsUrl`).
- [ ] `zx-prod-rzx-section` + `/prod-rzx/`. Uses tiny `zx-prod-files-list`.

### Phase 6 — Legacy cleanup

- [ ] Verify with `grep -rn` that the following templates have no remaining callers; delete each that's safe:
  - [ ] `component.releasestable.tpl`
  - [ ] `component.pressArticles.tpl`
  - [ ] `component.mentions.tpl`
  - [ ] `component.links.tpl`
  - [ ] `component.languagelinks.tpl`
  - [ ] `zxItem.images.tpl`
  - [ ] `zxItem.files.tpl`
  - [ ] `tags.form.tpl`
- [ ] Final QA pass per the Verification section.

---

## Out of scope (explicit)

- Angular router / navigation (page is still mounted by Smarty shell).
- Tabbed UI for sections — sections are independent, so we can add tabs later without changing the data layer.
- Refactor of `zx-music-list` / `zx-pictures-list` to lazy-load themselves. We wrap them with `InViewportDirective` at the section level instead.
- The WASM emulator engines themselves (`htdocs/libs/{us,zx81,mame,mamenextsam}/`). We only rewrite the JS launcher around them.
