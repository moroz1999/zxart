# Full Angular Migration Plan — Views, Routing, Form Components

> **Status: planning / inventory. Do NOT implement until the user approves.**
> This is the master document for migrating every view, module and form to Angular.

## Context

`ng-zxart` currently ships as a set of **web components** (`createCustomElement`) embedded into legacy
PHP/Smarty pages. There is **no Angular Router** (`app-routing.module.ts` has `routes = []`), language
is the first path segment of every legacy URL, and **every editing flow is still legacy**: the Angular
"editing controls" (`zx-*-editing-controls.component.ts`) are just buttons that build legacy URLs
(`type:author/action:showPublicForm/`) and full-page-redirect to PHP forms.

Goal: a **complete** migration to a real Angular Router SPA with native
edit/add/join/split/convert/AI/privileges forms and a brand-new clean routing scheme (no language in
the URL). All link building moves to the frontend; the backend stops passing links.

## Locked decisions

1. **Routing model:** full **Angular Router SPA**. One `<app-root>` + `RouterOutlet` owns all URLs
   client-side and builds all links itself. Legacy URLs 301-redirect to the new ones (PHP) for old
   bookmarks/SEO; **internal navigation is pure Angular routing, not redirects.**
2. **Backend links are removed entirely** — see Phase 2.
3. **Scope:** core entities (author, group + aliases, prod, picture, music/tune, release, party, press)
   **and batch uploads** are in scope now. Narrow admin reference CRUD (country, city, year, letter,
   tag positions, catalogues, positions editors, user/ban) is a **LATER** phase (still checklisted).

### Architecture rules (locked)

- **R1 — Invent nothing that wasn't in legacy.** No new widgets, behaviours, or selectors that did not
  exist in the legacy app. In particular, do not bolt artificial `*-view` selectors onto components
  that are not views (e.g. the player, the browsers). The migration reproduces legacy behaviour in the
  Angular app — it does not add features.
- **R2 — Everything is the routed Angular app; no custom elements in the target.** There are **no more
  custom elements** in the end state — the whole frontend is one bootstrapped Angular SPA driven by the
  router. **The entity id always comes from the route param** and is passed to feature components via
  normal property binding (`[elementId]`). **Pages are their own components in the FSD `pages/` layer**
  (one component per route); they import feature components and compose them — they never embed a
  registered custom element. During the transition the legacy custom-element registrations remain only
  for pages not yet routed, and are removed as each route lands (final cutover removes them all).

---

## PART 1 — Inventory of views/forms NOT yet in Angular

Already migrated (read-only detail + browser/list views): prod, author, group, party, picture, tune
detail pages; prod/author/group/picture/music browsers; authors page; comments, ratings,
search-results, firstpage, stats, header, player, feedback, geo.

Everything below is a legacy view Angular does **not** implement natively. Source of truth:
`project/modules/structureElements/<entity>/action.*.class.php`.

| Entity | Missing views / forms |
|---|---|
| **author** | edit/add (`showPublicForm`/`publicReceive`), merge (`showJoinForm`+`join`), `convertToGroup`, `claim`+`approveClaim`, privileges (`showPrivileges`+`receivePrivileges`), delete (modal+ajax), `deleteFile` |
| **authorAlias** | edit/add, merge, `convertToAuthor`, delete |
| **group** | edit/add, merge, `convertToAuthor`, privileges, `deleteAuthor`, delete, `deleteFile` |
| **groupAlias** | edit/add, merge, `convertToGroup`, `deleteAuthor`, delete |
| **zxProd** | edit/add, merge, split (`showSplitForm`+`split`), AI (`showAiForm`+`receiveAiForm`), privileges, `receiveFiles`, screenshots upload/move/delete, `resize`, `submitTags`, `deleteAuthor`, group/software **role selector** per person, delete |
| **zxRelease** | edit/add, `receiveFiles`, screenshots upload/move, `clone`, privileges, `deleteAuthor`, `deleteFile`, `viewFile`/`downloadDenied` |
| **zxMusic (tune)** | edit/add, `submitTags`, `deleteFile`, delete |
| **zxPicture** | edit/add, `resize`, `submitTags`, privileges, `deleteFile`, delete |
| **party** | edit/add, `deleteFile`, delete |
| **pressArticle** | add/edit, AI (`showAiForm`+`receiveAiForm`), comment, delete |
| **comment** | confirm add/edit/delete parity with legacy `publicForm`/`publicReceive` |
| **tag** | public `submitTags` voting UI (tag admin edit → LATER) |
| **playlist / userPlaylists** | create/rename/delete, reorder items (`showPositions`+`receivePositions`) |
| **batch upload** | `musicUploadForm` / `picturesUploadForm` / `zxProdsUploadForm` (`batchUploadForm`+`batchUpload`) — **Phase scope now** |
| **auth/user** | login form/logout, registration + `verifyEmail`/`sendEmail`, profile edit, user privileges/`ban` |

**LATER (reference / admin CRUD):** country, city, year, letter (`show`/`showForm`/`showFullList`/
`receive`/`delete`); tag admin (`showForm`, `showPositions`); `catalogues`/`tags` positions editors;
`zxProdCategory` (`showForm`, `showSeoForm`, `showPositions`, privileges); admin catalogue/list screens
(`musicCatalogue`, `picturesCatalogue`, `zxItemsList`, `commentsList`, `votesList`,
`partiesCatalogue/List`, `authorsCatalogue/List`, `groupsCatalogue/List`); `detailedSearch` admin.

---

## PART 2 — New routing standard (full URL list)

Rules: **no language segment** (language from cookie/preference, switched in-app). **Plural =
collection/list, singular = one entity.** Action = trailing segment. Add forms under the collection
(`authors/add`). **No `/delete` routes** — deletion is a confirm modal + ajax with privileges checked
on the backend. **No separate `authors/active` route** — it's a filter/tab inside the authors browser.

> Vocabulary below is a proposal — confirm/rename: `prods` vs `productions`, `pictures` vs `graphics`,
> `music` vs `tunes`, `press` vs `articles`, `tune/:id` vs `music/:id`.

### Home / global
| URL | Page |
|---|---|
| `/` | Firstpage |
| `/search?q=…` | Search results |
| `/search/advanced` | Detailed search |
| `/stats` · `/comments` · `/ratings` · `/geo` · `/radio` | Standalone pages |
| `/login` · `/logout` · `/register` · `/verify-email/:token` | Auth |
| `/profile` · `/profile/edit` · `/preferences` · `/playlists` · `/playlist/:id` | Current user |

### Authors & groups
| URL | Page |
|---|---|
| `authors` (filters incl. "active" as tab) · `authors/add` | List / create |
| `author/:id` (`/gfx`·`/music`·`/software`·`/collaborators`·`/mentions`·`/discussion` tabs) | Detail |
| `author/:id/edit` · `/join` · `/convert-to-group` · `/claim` · `/privileges` | Forms |
| `author-alias/add` · `author-alias/:id` · `/edit` · `/join` · `/convert-to-author` | Aliases |
| `groups` · `groups/add` · `group/:id` · `/edit` · `/join` · `/convert-to-author` · `/privileges` | Groups |
| `group-alias/add` · `group-alias/:id` · `/edit` · `/join` · `/convert-to-group` | Group aliases |

### Productions / releases
| URL | Page |
|---|---|
| `prods` · `prods/add` · `prods/upload` (batch) | List / create / batch |
| `prod/:id` · `/edit` · `/join` · `/split` · `/ai` · `/files` · `/screenshots` · `/privileges` | Prod |
| `release/:id` · `/edit` · `/files` · `/screenshots` · `/clone` · `/privileges` | Release |

### Pictures / music / parties / press / tags
| URL | Page |
|---|---|
| `pictures` · `pictures/add` · `pictures/upload` · `pictures/search` · `picture/:id` · `/edit` · `/resize` · `/privileges` | Pictures |
| `music` · `music/add` · `music/upload` · `music/search` · `tune/:id` · `/edit` | Music/tunes |
| `parties` · `parties/add` · `party/:id` · `/edit` | Parties |
| `press` · `press/add` · `press/:id` · `/edit` · `/ai` | Press |
| `tags` · `tag/:id` | Tags |

### LATER (clean URLs reserved, legacy at first)
`countries`/`country/:id/edit`; `cities`/`city/:id`; `years`/`year/:id`; `letters`/`letter/:id`;
`tag/:id/edit`, `tags/positions`; `categories`, `category/:id/edit`, `category/:id/seo`,
`categories/positions`; `admin/users`, `user/:id/edit`, `user/:id/privileges`, `user/:id/ban`.

---

## PART 2.5 — Forms architecture (decided, follows the existing comments/feedback pattern)

Existing precedent: the Angular **comments** and **feedback** forms already POST to an
action-dispatched REST controller and get JSON back (`assignSuccess`/`assignError`, `objectMapper`).
Entity edit/add forms follow the same shape — a fresh, clean REST contract (not the legacy
`formData[{id}][field]` CSRF field names):

- **Backend:** one action-dispatched REST controller per entity form (or extend the entity's detail
  controller), pattern from `Controllers/Comments.php`:
  - `GET ?action=editData&id=` → current field values + select options as JSON. **Privilege-gated**
    (reuse the legacy `showPublicForm`/`publicReceive` privilege on the element).
  - `POST ?action=save` → validate, persist via a service (reuse `structureElement::persistElementData()`
    and the entity's existing `setExpectedFields`/`setValidators` logic where feasible), return
    `{success, redirectUrl}` or `{errors}` JSON.
- **Frontend:** reactive form on the `zx-form/*` scaffolding; load via GET, submit via POST; **show the
  backend error above the form** (per R1 rules), validate on the front where cheap; route guard checks
  the privilege via the single cached privileges service (Phase 3).
- **Routes/pages:** `{entity}/:id/edit`, `{entity}/add` → FSD `pages/` components (id from route).
- **First form: `party`** (self-contained, already a migrated detail page): title, abbreviation
  (`zx-input`), country/city (`zx-entity-autocomplete` — new, search via `Geo` controller), image
  (`zx-image-upload` — new). Establishes the end-to-end pattern, then fan out.

## PART 3 — Form components (build / adapt)

Already present in `ng-zxart/src/app/shared/ui/`: `zx-input`, `zx-textarea`, `zx-checkbox(-field)`,
`zx-toggle`, `zx-select`, `zx-tags-input`, `zx-chips`, `zx-input-range`, `zx-min-max-range`,
`zx-sort-select`, `zx-filter-picker`, `zx-button`, `zx-dialog`, `zx-confirm-dialog`, and the
`zx-form/*` scaffolding (field, label, message, section, actions, fieldset, control-errors).

**To build / adapt** (corrections applied — WYSIWYG, date-picker, dropzone, sortable-list, multi-text,
privileges-matrix, coordinates, captcha, BE error-mapping all dropped):

| # | Component | Build/Adapt | Notes |
|---|---|---|---|
| 1 | `zx-entity-autocomplete` (single+multi) | **Adapt** from `shared/ui/zx-tags-input` + `shared/services/tags-search.service.ts` (the software tag search) | city/country/author/group/party pickers + merge target picker |
| 2 | `zx-multilang-field` (text / textarea / image) | Build | per-language repeating inputs |
| 3 | `zx-year-picker` | Build | year only — no full date picker |
| 4 | `zx-file-upload` (single + multiple) | Build | **drag-and-drop built in**, shows existing files + delete |
| 5 | `zx-image-upload` (multiple) | Build | png/gif **preview**; **screenshot manager is part of this** (upload + reorder + delete) |
| 6 | `zx-enum-select` | Build | static options (array/index/serialized) — compo type, border, rotation |
| 7 | `zx-password-input` · `zx-email-input` · `zx-number-input` | Build | login, registration, numeric fields |
| 8 | `zx-user-search` | Build | privileges forms |
| 9 | `zx-ai-form` (prompt + run + preview/accept) | Build | prod + press, wired to `project/core/ZxArt/Ai/*` |
| 10 | `zx-merge-form` / `zx-split-form` | Build | author/group/prod merge, prod split (use #1 for targets) |
| 11 | `zx-tag-editor` | **Adapt** existing `zx-tags-input` / `tags-quick-form` | tag submission/join |
| 12 | `zx-category-tree-picker` | **Adapt** `entities/zx-prods-category/components/categories-tree-selector` | tree + **quick name filter, no ajax** |
| 13 | `zx-group-role-selector` | **Adapt** `entities/zx-collaborator-person-card` / `shared/ui/zx-prod-people-row` | person's role in group/software, **works without refresh** |
| 14 | Form infra | Build | show backend error **above the form**; validate on the frontend where possible; dirty-navigation guard. No BE field-error mapping, no captcha. |

---

## Execution phases (checklists)

### Phase 1 — Router foundation
- [x] Add Angular Router; bootstrap a routed root (`SpaRootComponent` / `<zx-spa-root>`) hosting the
      `RouterOutlet`. Bootstrapped only when `<zx-spa-root>` is present, so legacy pages stay untouched
      (router `initialNavigation: 'disabled'`).
- [x] PHP serves the SPA shell (`project/templates/public/index.spa.tpl` via `ZxArt\Spa\SpaRouter`,
      branch in `publicApplication::execute()`). Currently scoped to the 7 entity detail routes.
- [x] Detail pages for author/group/party/prod/release/picture/tune as separate FSD `pages/` components
      (id from route param → `[elementId]`). Collection routes are sequenced to **Phase 10** (they need
      the wrapper-element id removed first).
- [x] **Legacy→new 301 redirects (simple, no URL table).** Let the existing mechanisms resolve the
      incoming legacy URL as they do today: if it resolves to a structureElement of a **migrated
      business-entity type**, 301 to its new clean URL. A small backend **type→URL resolver**
      (`structureType` + id → `/prod/{id}`, `/author/{id}`, …) builds the target. No per-URL redirect
      map — the redirect is driven by the resolved entity or section type. Migrated catalogue and
      standalone section roots resolve to their SPA routes and preserve the request query string.
      Language roots and their configured legacy first pages redirect to the SPA root.
      Done in `publicApplication::execute()`
      right after `getCurrentElement()`.
- [ ] Language resolved in-app (cookie/preference), removed from URLs.
- [ ] Map all existing web-component views onto routes.

### Phase 2 — Remove backend links & manual URL parsing
**Link strategy (decided):** a single backend `ZxArt\Urls\EntityUrlResolver` (`structureType` + id →
`/prod/123`, `/author/123`, …) is the one source of truth, shared by the 301 redirects **and** the REST
DTO link fields. Entity DTOs emit the **new** clean URL in their `url` field (with a legacy fallback for
not-yet-routed types) **and** always include `id`. The frontend keeps rendering `[href]="dto.url"`
unchanged, so every link (legacy pages included) lands on the new SPA pages immediately; `routerLink`
swap-in happens later once legacy content pages are retired.
- [x] `EntityUrlResolver` created; wired into the legacy→new 301 in `publicApplication::execute()`
      (guarded so `action:`/form URLs stay on legacy).
- [x] Author DTOs (`AuthorDetailsService`, `AuthorCollaboratorsService`) emit new URLs via the resolver.
- [ ] Roll the resolver through the remaining entity DTO builders (prod, group, picture, tune, release,
      party, press, lists, prod authors/links, breadcrumbs) so every `url` field is new-standard + `id`.
- [ ] **Menu becomes hardcoded in the frontend.** Since all links are routing links, the menu needs
      nothing from the backend: hardcode it in the Angular app and **remove the `/menu` endpoint** (and
      its controller/service) entirely.
- [ ] Remove link fields emitted in DTOs from the backend.
- [ ] Replace all redirect-based navigation (`zx-editing-controls`, `*-editing-controls`) with Angular router links/navigation.
- [ ] Build all URLs on the frontend via a single link/router helper.
- [ ] Find every component doing manual URL/param parsing and convert to **route params** (`window.location`/`pathname`/`URLSearchParams` usages — ~30 files incl. `browser-base.component.ts`, `zx-prods-category`, `zx-music-search`, `zx-picture-search`, `zx-search-results`, `zx-group-browser`, `zx-author-browser`, author-details tabs, `comments-*`, `zx-prod-details`, `zx-author-details`, `zx-party-details`, `zx-group-details`, `zx-release-file-structure`, `zx-geo`, header `search-dialog`/`login-trigger`).

### Phase 3 — Privilege guards & single privileges service
- [ ] One privileges service (extend `shared/services/element-privileges-api.service.ts` + `current-user.service.ts`) that **caches privileges for the front session** and never re-fetches needlessly.
- [ ] Route guards on **all form routes** read from that single service.
- [ ] Hide/disable entry points the same way (no privilege = no link).

### Phase 4 — Form component library
- [ ] Build/adapt components #1–#14 from Part 3 on the existing `zx-form/*` scaffolding.
- [ ] Demo route to exercise each control.

### Phase 5 — Core entity forms
- [ ] author + authorAlias: edit/add, merge, convert, claim, privileges, delete-modal.
- [ ] group + groupAlias: edit/add, merge, convert, privileges, deleteAuthor, delete-modal.
- [ ] prod: edit/add, merge, split, files, screenshots, privileges, **role selector**, delete-modal.
- [ ] release: edit/add, files, screenshots, clone, privileges, delete-modal.
- [ ] picture: edit/add, resize, privileges, delete-modal.
- [ ] music/tune: edit/add, delete-modal.
- [ ] party: edit/add, delete-modal.
- [ ] press: add/edit, delete-modal.
- [ ] tag submission (`submitTags`) public UI; comment add/edit/delete parity.
- [ ] playlists: create/rename/delete + reorder.

### Phase 6 — Batch uploads (in scope now)
- [ ] `prods/upload`, `pictures/upload`, `music/upload` using `zx-file-upload` / `zx-image-upload`.

### Phase 7 — AI flows
- [ ] `zx-ai-form` for prod and press, wired to `core/ZxArt/Ai` endpoints.

### Phase 8 — Auth / user
- [ ] login/logout, registration + email verification, profile edit, preferences.
- [ ] user privileges + ban (admin).

### Phase 9 — LATER (reference / admin CRUD)
- [ ] country / city / year / letter CRUD.
- [ ] tag admin edit + positions; catalogues positions editors.
- [ ] zxProdCategory edit / SEO / positions / privileges.
- [ ] admin catalogue & list screens; detailedSearch admin.
- [ ] category tree picker reuse (#12) once category admin lands.

### Phase 10 — Eliminate technical wrapper elements (late, implemented separately)
Goal: only **real business entities** (author, group, prod, picture, tune, release, party, press, …)
remain as structure entities. The technical wrapper modules/elements that today exist only to host a
list and its settings (e.g. `zxItemsList`, `authorsList`, `groupsList`, `partiesList`, the various
catalogue/list wrapper elements) are removed from the data model.

- [ ] **List endpoints must work without a wrapper-element id.** Today browsers require the wrapper
      element's `element-id` (`/authorlist/?elementId=N`, etc.). Provide list endpoints that work
      **without that module/element at all** — list by entity type + filters, no technical id.
- [ ] **Move wrapper settings into the frontend view config.** The per-list settings currently stored
      in the DB on the wrapper module (sorting, limit, default filters, which columns/sections, etc.)
      become inputs/config on the Angular list page/view (hardcoded in the view, passed to the browser
      component) — no longer fetched from the backend.
- [ ] Once nothing needs them, drop the wrapper structure modules and their DB rows.
- [ ] This unblocks the collection routes (`authors`, `groups`, `prods`, `pictures`, `music`) which are
      otherwise stuck needing a section-root id (see Phase 1 carve-out).

## Critical files
- Router/bootstrap: `ng-zxart/src/app/app-routing.module.ts`, `app.module.ts`.
- Replace redirect entry points: `shared/ui/zx-editing-controls/*`, `features/*/components/*-editing-controls/*`.
- Privileges: `shared/services/element-privileges-api.service.ts`, `shared/services/current-user.service.ts`.
- Reuse for form controls: `shared/ui/zx-tags-input`, `shared/services/tags-search.service.ts`,
  `entities/zx-prods-category/components/categories-tree-selector`, `entities/zx-collaborator-person-card`,
  `shared/ui/zx-prod-people-row`, `shared/ui/zx-form/*`.
- Legacy source of truth: `project/modules/structureElements/<entity>/action.*.class.php`,
  `project/templates/{public,admin}/*.form.tpl`, `trickster-cms/cms/templates/admin/component.*.tpl`,
  `project/js/{admin,public}/component.*.js`.
- Redirects: `htdocs/.htaccess`, `trickster-cms/cms/core/controller.class.php`, `project/core/SectionLogics.class.php`.

## Verification (per migrated route)
1. Build with `composer run build` (never `ng build`); load in dev env (Host `zxart.loc`).
2. New URL renders the Angular view; legacy URL 301→new; internal links navigate via router (no full reload).
3. Form: load → edit → submit → persists via API; backend error shown above the form; frontend validation where possible; privilege guard blocks unauthorized access.
4. Merge/split/convert/AI produce the same DB result as the legacy action (compare on staging).
5. Backend errors: check docker logs first.

## Open items for user review
- Confirm URL vocabulary (`prods`/`productions`, `pictures`/`graphics`, `music`/`tunes`, `press`/`articles`, `tune`/`music`).
- Confirm add-form convention (`authors/add` vs `author/add`).
- Any entity/view missing from Part 1 that must be in an early phase?
- Confirm which shared actions (vote, playlist add/remove, submitTags) are already fully in Angular vs still legacy.
