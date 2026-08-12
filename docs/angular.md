### Angular SPA Shell

Canonical public routes render `index.spa.tpl`. Historical structure URLs are
resolved by the backend and redirected to clean SPA URLs with HTTP 301. Unknown
routes are logged by the backend and return the Angular shell with HTTP 404.

The Smarty shell body contains only the `<app-root>` host. Its head contains the
Angular assets and server-rendered crawler metadata. After client-side navigation,
Angular applies entity-page metadata from the entity response, or at least its
loaded title when that response has no metadata object, and builds fixed-route
metadata from route translation keys. Form routes provide a static translated
title immediately; existing-entity forms replace it after loading with a translated
action title containing the localized `entityTitle` from the form-data response.
Canonical links are not emitted.

The shell must **not** contain a `<base>` element: the browser resolves bare
`#anchor` links against the document base URL, so a `<base href="/">` would send
every in-page anchor to the home page. The router gets its base href from the
`APP_BASE_HREF` provider in `app.config.ts` instead.

All Angular components MUST be standalone. The application is bootstrapped with
`bootstrapApplication`, and public navigation is owned by the Angular router.

The SPA is the only public frontend: `bootstrapApplication` mounts `<app-root>`
and nothing else. No component is registered as a custom element, no component
reads prefetched data from `window`, and every component that owns URL state
goes through the router. Components embedded in a page (dashboards, widgets)
take their query as inputs and neither read nor write the URL.

Every component referenced by a route is stored in `pages/` and composed with
`zx-page-layout`. Each routed page provides one page-level
`<h1 appHeading="display" zxPageHeader>`; the layout owns spacing below the
header and between independent page content blocks.

#### Form Select Options

Options of entity select fields (compos, chip and channel types, frequencies,
palettes, languages, release types, hardware) are not enumerated in Angular. The
element exposes them through a provider method (`getCompoTypes()`,
`getPaletteTypes()`, …), `Formdata::enumSpecs()` maps each field to that method
and a label strategy. Backend-owned labels arrive translated in the `enums` map
of the `/formdata/` response. A form renders `enums['<field>']` for those fields.

Every element type whose form has such a field needs its own entry in
`enumSpecs()` — including the transient upload-form elements, whose selects stay
empty otherwise. Only lists that are fixed by the hardware (border colour,
rotation angles) are constants in the component.

A spec marked `clientLabels` sends the bare values instead, for lists the SPA
translates itself. Software languages work that way: the backend names the codes
a production or release can carry and the SPA resolves them through
`language.<code>`.

Hardware does **not**. Its names live in an editable catalog, so every response
carries `name`, `shortName` and `category` per code, localized for the request
language, and the SPA renders what it is given. A spec marked `mode: 'options'`
receives ready `{value, label, group}` rows; `buildHardwareGroups()`
(`shared/utils/hardware-groups.ts`) turns them into the grouped picker every
hardware form uses. Only the category heading is still an SPA string
(`hardware-group.<code>`), because the category set is a code enum rather than
editable data.

A `zx-select` displays what its form control holds and reports only what the
user picks: loading options, writing a value and changing the disabled state
never invoke `onChange`. A single select whose value matches no option shows a
blank option of its own, so it never displays an option the control does not
hold — the value on screen is always the value the form submits.

The default value therefore belongs to the form that creates the control, not to
the select. For backend-driven enums `enumDefaultValue()`
(`shared/utils/enum-default.ts`) provides it: an entity with nothing stored yet
starts on the first option. Lists where "nothing" is a valid choice carry an
empty first option (`emptyBlank`, `emptyLabelKey`) and keep the empty value.

#### Pagination and URL

Components with pagination **must** reflect the current page in the URL and restore it on load.

**Rules:**
- Routed SPA pages store the page in the `page` query parameter and navigate with
  Angular Router.
- Pass the remaining filter state to `<zx-pagination [queryParams]="params">` so
  page links have correct shareable `href` attributes.
- Page 1 omits the `page` query parameter.
- `zx-pagination` builds its links from the router only; there is no path-based
  (`page:N/`) pagination and no `window.history` manipulation anywhere.
- A `BrowserBaseComponent` subclass reads its own filter query params in
  `onQueryParams` and stores them in `filterParams`; the base keeps them in the URL
  on page/sorting changes and exposes them as `paginationQueryParams` for
  `<zx-pagination [queryParams]>`. Filters must not be passed in as `@Input()` — the
  router emits the new params before input bindings are updated.
- `zx-pagination` rebuilds its page links only when `currentPage`, `pagesAmount`
  or `visibleAmount` change, and tracks them by identity. Hosts are free to build
  `queryParams` in a getter: the fresh object identity makes `ngOnChanges` run on
  every check, and rebuilding there would replace the pressed link while the
  button's ripple runs a check between pointerdown and click, so the click would
  be swallowed and nothing would happen.

**Reference implementation:** `CommentsPageComponent` (`features/comments/components/comments-page/`) and `BrowserBaseComponent` (`shared/browser-base.component.ts`).

Entity detail tabs paginate the same way: the tab component reads `page` from
`ActivatedRoute.queryParamMap` and writes it back with
`queryParamsHandling: 'merge'`.

#### Tabs

`zx-tabs` renders a tab with an `href` as a `routerLink` anchor. Switching such
a tab is an ordinary navigation: the route changes, the parent recomputes the
active index from the route and feeds it back through `initialActiveIndex`. Tab
hrefs must therefore be real routed URLs (`/author/:id/:tab`). Tabs without an
`href` stay local and only swap the rendered template.

The tab segment is a child route of the page (see [Optional Trailing Route
Segments](#optional-trailing-route-segments)) and declares
`data: {inPageTab: true}`. `SpaRootComponent` strips that segment when it derives
the view key it uses to decide whether a navigation scrolls back to the top, so
switching a tab keeps the reader where they are.

#### Breadcrumbs

`zx-breadcrumb-bar` renders the trail once in the shell, above every page's
`<h1>`; no page renders `zx-breadcrumbs` itself. `BreadcrumbService` builds the
trail from the top menu for routes that carry a `titleKey`, and entity detail
pages push their own richer trail with `setEntityTrail()` once loaded — or
`setNotFoundTrail()` when the entity does not exist.

Until the trail of the current page is known, the bar renders
`zx-breadcrumbs-skeleton`, which occupies exactly the row the trail will take,
so the page below it never moves when the trail arrives. Reserving that row is
what makes a routed page whose trail loads asynchronously render at its final
position from the first frame; only the home page, which has no breadcrumbs,
renders nothing.

The trail itself stays on that one row: it never wraps, and scrolls sideways
when it is longer than the viewport. Its length is only known once the entity
loads, so a wrapping trail would take a second row at that moment and push the
whole page down.

Two consequences for routes:

- A route with no `titleKey` and no entity id gets no breadcrumbs at all, so
  every routed page that is not an entity page needs a `titleKey`.
- The trail belongs to the entity, not to the URL: it survives in-page
  navigation (tab segments, query parameters) and is replaced by the skeleton
  only when another entity opens.

#### Scroll on navigation

`SpaRootComponent` scrolls to the top on `NavigationEnd`, but only when the view
key — the URL path without query, fragment and in-page tab segment — actually
changed, and only for `imperative` navigations. Back and forward are left to the
browser, which restores its own recorded position.

#### Build and Verification
After making any changes to the Angular part of the project (`ng-zxart`), including styles (SCSS) and theme files, you must:
1. Perform a project build: `composer run build` from the project root.
2. Ensure the build completes without errors.
3. Verify the result in a browser.

**Note: Any change to angular files requires a mandatory Angular build to reflect changes in the application.**

#### Local Dev Server
For live Angular changes, start the stack with Docker Compose:
`docker compose up`.

The first run installs npm dependencies into the `node_modules_ng` Docker volume when Angular CLI is missing.

The PHP app uses `NG_DEV_SERVER_URL` to load Angular dev-server modules instead of `htdocs/js/ng-zxart/manifest.json`. Without this environment variable, the public SPA shell uses the production manifest generated by the Angular build.

### Analytics

`AnalyticsService` initializes Yandex Metrika in Angular. A page view is sent
on every `NavigationEnd`; metadata processing remains active independently.
The previous Angular URL is sent as the referrer for client-side navigations. Google
Analytics and Google Ads scripts are not loaded by the public frontend.

### Architecture and Code Structure

#### Popover Motion

- Header and other panel-style CDK popovers use `PopoverAnimation`.
- Search-result CDK dropdowns use the shorter `DropdownPopoverAnimation`.

#### Heavy Third-Party Libraries

A large third-party library must never end up in a chunk that a page loads just
to render a button. Wrap it in a standalone component that is referenced only
inside a `@defer` block, so the compiler emits it as its own lazy chunk.

- The wrapper component imports the library; nothing else imports the wrapper
  outside a `@defer` block. A library `NgModule` listed in a component's
  `imports` is never deferred, so the wrapper is what makes deferral possible.
- The `@defer` trigger is the condition that actually needs the library
  (`@defer (when isPdf)`), and `@placeholder` shows the loading state.

`ZxPdfViewerComponent` (`shared/ui/zx-pdf-viewer/`) wraps `ngx-extended-pdf-viewer`
and is deferred by `ZxFileViewerDialogComponent`: prod and release pages no
longer download the PDF engine.

#### Feature Sliced Design (FSD)
All new functionality in Angular must follow Feature Sliced Design principles and the [Design System](design-system.md).

**CRITICAL**:
- **No new Material imports.** Material UI is being phased out (see [design-system.md](design-system.md) for the full plan).
- **ONLY standalone components** are allowed.
- **Angular CDK** (`@angular/cdk`) is the approved foundation for overlays, drag-and-drop, and accessibility.
- Use design system components and theme variables. Custom CSS is forbidden without direct instruction.
- Components must be used semantically.
- Skeleton components are listed in [Skeletons](design-system/skeletons.md). Import concrete skeleton components directly; a shared facade that imports multiple skeleton variants is forbidden.

### Documentation Scope

General Angular documentation must contain domain-neutral architecture rules only. Entity-specific behavior, feature-specific REST contracts, and business rules belong in `docs/domain/*.md` or a narrowly scoped feature document.

When adding a reusable Angular pattern, document the generic rule here and place concrete entity examples in the relevant domain document.

### Interface Language

The SPA owns the interface language (`LanguageService`, `shared/services/`). The
selected language lives in localStorage; `languageInterceptor`
(`shared/interceptors/`) sends it to every same-origin request as an `X-Language`
header so backend responses are localized. Never read the language from the URL or
the backend session. Full behavior: [domain/language-auth.md](domain/language-auth.md).

### Internal Links

Links to routed SPA pages use Angular `RouterLink` directly on the anchor. Build
internal routes in the template from entity identifiers where the response
carries them, for example `<a [routerLink]="['/prod', prod.id]">`. Where a
response carries a URL field instead, it is always a clean SPA route from
`EntityUrlResolver` and can be bound to `routerLink` as it is — see
[domain/entity-urls.md](domain/entity-urls.md). Keep plain `[href]` for external
and download URLs. Do not add global click interception.

### Destructive Actions

A destructive action never gets a route or a page of its own. It runs from the
component that owns the entity, behind a `ConfirmDialogService` dialog with
`danger: true`, calls the backend directly, and then navigates to a URL the
caller supplies. The button is rendered only after the element's privilege has
been confirmed through `ElementPrivilegesApiService`, so an unprivileged user
never sees it.

Page-level actions of this kind are projected into the page header: a second
element carrying `zxPageHeader` beside the `<h1>` lands on the heading line, at
the opposite end of the row. Entity deletion works this way — see
[domain/entity-deletion.md](domain/entity-deletion.md).

### Entity Prefetch Resolvers

A routed page ships in its own chunk, so its API services cannot request
anything until that chunk and everything it imports has arrived. On a phone
connection that is over a second of idle connection before the first entity
request even starts, and every request the page makes *after* mounting adds a
further round trip to the largest paint.

An entity route therefore resolves its requests from the initial bundle:

```typescript
{
  path: 'prod/:id',
  loadComponent: () => import('./pages/prod/prod-page.component').then(m => m.ProdPageComponent),
  resolve: {prefetch: prefetchEntityResolver(['/prod-details/', '/prod-screenshots/'], 'id')},
}
```

`prefetchEntityResolver` (`shared/resolvers/`) hands the URLs to
`EntityPrefetchService` (`shared/services/`) and returns immediately, so the
navigation is never held up: the requests travel while the router still
downloads the chunk. The page's own API service then reads through
`EntityPrefetchService.get()`, which hands over the response already in flight,
or issues a fresh request when there is nothing prefetched. The service holds
only the entries of the navigation in flight and gives each one away on first
read, so revisiting a page still fetches current data.

Two rules follow:

- Prefetch the requests that stand between the navigation and the page's
  **largest paint** — the entity core and whatever carries its main image. There
  is nothing to win by prefetching what a visitor reaches only after scrolling,
  and the bytes compete with the chunk download.
- The resolver keeps its endpoints in the route config, and the service stays
  domain-agnostic: it deals in URLs, never in DTOs. Resolvers run after the
  guards, so the prefetched response carries the interface language the visitor's
  account asks for, exactly like the page's own request.

### Route Guards

Cross-cutting per-navigation logic belongs in a route guard, not in components.
`authGuard` (a blocking `canActivateChild` on a pathless parent that wraps all
routes) resolves the current user before the first render (auto-login) and applies
the user's language once. Guards that must complete before rendering return an
Observable that emits when ready.

### Reused Parameterized Routes

Angular reuses a routed component when only a route parameter changes. A child
detail component that receives the parameter through an input must reload its
data when that input changes; initialization-only loading is not sufficient.

#### Optional Trailing Route Segments

A page whose trailing segment is optional — an in-page tab (`/prod/:id/:tab`), a
browsed letter (`/groups/:letter`) or a year (`/parties/:year`) — declares that
segment as a **childless child route**, never as a second top-level path:

```typescript
{
  path: 'prod/:id',
  loadComponent: () => import('./pages/prod/prod-page.component').then(m => m.ProdPageComponent),
  data: {metadataSource: 'entity'},
  children: [{path: ':tab', children: [], data: {metadataSource: 'entity', inPageTab: true}}],
}
```

Two sibling paths are two route configs, and the router keeps a component alive
only while the config stays the same. With siblings, the first click on a tab
destroys the page and reloads everything it had already fetched, and only the
clicks after that are cheap.

The page component stays on the parent route, so the parameter is not in its own
`paramMap`. It reads the value with `childRouteParam(route, router, name)`
(`shared/utils/child-route-param.ts`), which re-reads `route.firstChild` after
every navigation and yields `null` while no child route is active.

Two consequences of the nesting:

- The `:param` child swallows any single trailing segment, so action routes of
  the same entity (`prod/:id/edit`, `prod/:id/join`, …) must be declared **before**
  the entity route.
- Route data taken from the deepest route — `metadataSource`, `inPageTab`,
  `titleKey`, `noIndex` — must be repeated on the child.

### LocalStorage

All localStorage access MUST go through `LocalStorageService` (`shared/services/local-storage.service.ts`). Direct use of `localStorage` (e.g. `localStorage.getItem/setItem/removeItem`) is **forbidden**.

`LocalStorageService` automatically namespaces every key with `zx-${storageVersion}-`. This prefix is bumped on deploy (via `environment.prod.ts`) to invalidate stale cached data after breaking schema changes. Bypassing the service breaks this mechanism.

### RxJS and Reactive Data Flow

RxJS is the primary data-flow mechanism in this project. All data fetching and state management MUST be built on Observables. Imperative patterns (calling `load()` / `fetch()` from components) are forbidden.

#### Core Rules

1. **Services own their state.** A service exposes a ready-to-use `readonly observable$`. Components subscribe — they never trigger loading manually.
2. **`shareReplay` for remote data.** Any Observable backed by an HTTP call MUST use `shareReplay({bufferSize: 1, refCount: false})` so the request is made exactly once and late subscribers get the cached value.
3. **`BehaviorSubject` for mutable state (preferred).** When a service holds state that can change (e.g. current user, selected item, toggle), use a `BehaviorSubject` as the single state store:
   ```typescript
   private readonly store = new BehaviorSubject<T | null>(null);
   readonly data$ = this.store.asObservable();
   ```
   - `null` means "not yet loaded". HTTP is triggered lazily on first subscription via `defer`.
   - A `loading` boolean flag prevents duplicate in-flight requests.
   - Mutations (`save`, `login`, `logout`) use `tap(value => this.store.next(value))` in the returned Observable.
   - Derived observables are built with `map` on top of `data$` — no synchronous getters.
   - No internal persistent `subscribe()` to maintain a shadow value.
   - Do **not** use `merge + Subject + shareReplay` for mutable state — it obscures ownership and makes synchronous reads impossible.
4. **No nested subscribes.** Use `switchMap` / `concatMap` / `mergeMap` to compose asynchronous chains. `subscribe()` inside `subscribe()` is forbidden.
5. **`tap` for side effects.** Side effects inside an Observable pipeline belong in `tap`, not in `subscribe` callbacks. `subscribe` is only for the final consumer (usually a component).
6. **Always unsubscribe.** Components MUST collect subscriptions in a `Subscription` and call `unsubscribe()` in `ngOnDestroy`. Never leave a subscription open.
7. **`catchError` at service level.** HTTP errors must be caught in the service, not in the component. Return a sensible fallback Observable (`of(fallback)`) so the component's stream never errors out permanently.
8. **`AsyncPipe` preferred in templates.** Use `| async` instead of manual subscriptions when practical — it handles unsubscription automatically.

#### Anti-patterns (FORBIDDEN)

```typescript
// ✗ public load() called from component — component must never trigger fetching
load(): void { this.http.get(...).subscribe(...); }
// ngOnInit: this.service.load(); ← forbidden

// ✗ cold Observable without shareReplay — new request on every subscription
readonly data$ = this.http.get<T>(this.url);

// ✗ nested subscribe
this.a$.subscribe(a => this.b$.subscribe(b => ...));
```

#### Correct Pattern — mutable state (BehaviorSubject store)

```typescript
// Service
private readonly store = new BehaviorSubject<CurrentUser | null>(null);
private loading = false;

// defer() triggers lazy load on first subscription; filter skips null until loaded
readonly user$: Observable<CurrentUser> = defer(() => {
  if (this.store.getValue() === null && !this.loading) {
    this.loadCurrentUser();
  }
  return this.store.pipe(filter((u): u is CurrentUser => u !== null));
});

// Derived observables — no synchronous getters
readonly isAuthenticated$ = this.user$.pipe(map(u => u.userName !== 'anonymous'));
readonly userId$ = this.user$.pipe(map(u => u.id));

constructor(private http: HttpClient) {}

// Private — triggered automatically by user$, never called from components
private loadCurrentUser(): void {
  this.loading = true;
  this.http.get<CurrentUser>(this.url).pipe(
    catchError(() => of(ANONYMOUS_USER)),
  ).subscribe(user => {
    this.loading = false;
    this.store.next(user);
  });
}

login(name: string, pass: string): Observable<CurrentUser> {
  return this.http.post<CurrentUser>(this.url, {name, pass}).pipe(
    tap(user => this.store.next(user)),
  );
}

// Component — subscribes, never triggers loading
ngOnInit(): void {
  this.subscription.add(this.service.user$.subscribe(user => this.user = user));
}
ngOnDestroy(): void { this.subscription.unsubscribe(); }
```

#### Correct Pattern — read-only cached data (shareReplay)

```typescript
// Service — data never changes after initial load
readonly items$: Observable<Item[]> = this.http.get<Item[]>(this.url).pipe(
  catchError(() => of([])),
  shareReplay({bufferSize: 1, refCount: false}),
);
```

### OnPush Change Detection

**All components MUST use `changeDetection: ChangeDetectionStrategy.OnPush`.**

OnPush only re-checks a component when:
- an `@Input()` reference changes
- an event originates inside the component
- `async pipe` emits
- `ChangeDetectorRef.markForCheck()` is called

This means **imperative state mutations inside `.subscribe()` callbacks are invisible to OnPush** and MUST NOT be used. Always follow the BehaviorSubject pattern (see RxJS section above).

#### ViewModel pattern for base-class components

When a component inherits from a base `@Directive` class that manages async state (e.g., `FirstpageModuleBase`), the base class MUST expose a single `vm$: Observable<Vm>` combining all template-relevant state via `combineLatest` + `map`. Templates subscribe once with `*ngIf="vm$ | async as vm"` and access all fields from `vm`.

```typescript
// Base class
export interface ModuleVm<T> {
  items: T[];
  loading: boolean;
  error: boolean;
  empty: boolean;
  viewAllUrl: string | undefined;
  viewAllLabelKey: string | undefined;
}

readonly vm$: Observable<ModuleVm<T>> = combineLatest([stateStore, linkStore]).pipe(
  map(([state, link]) => ({ ...state, empty: !state.loading && !state.error && state.items.length === 0, ...link }))
);
```

```html
<!-- Template -->
<ng-container *ngIf="vm$ | async as vm">
  <zx-wrapper [loading]="vm.loading" [error]="vm.error" [empty]="vm.empty">
    <item *ngFor="let item of vm.items"></item>
  </zx-wrapper>
</ng-container>
```

For synchronous access to items inside component methods (e.g., event handlers), use a protected getter that reads from the BehaviorSubject: `protected get currentItems(): T[] { return this.stateStore.getValue().items; }`.

#### Native DOM events: @HostListener vs markForCheck()

`addEventListener` on a native DOM element bypasses Zone.js — Angular does not see these events and OnPush components will not re-render.

**Rule: never use `addEventListener` in a component when an Angular alternative exists.**

| Situation | Solution |
|-----------|----------|
| Event on the component's own host element | `@HostListener('eventname', ['$event'])` on the method |
| Event on `window` or `document` | `@HostListener('window:eventname')` / `@HostListener('document:eventname')` |
| Event conditionally registered/removed (e.g. drag tracking) | keep native `addEventListener` + `this.cdr.markForCheck()` in the handler |
| Event on a specific unrelated DOM element (not host) | keep native `addEventListener` + `this.cdr.markForCheck()` |
| Event in a service (no host element) | native `addEventListener` is fine — services have no CD context |

`@HostListener` routes events through Zone.js automatically — no `markForCheck()` needed. Angular also handles cleanup on component destroy.

```typescript
// ✓ host event — no addEventListener, no markForCheck needed
@HostListener('pointerenter', ['$event'])
onEnter(event: PointerEvent): void { this.active = true; }

// ✓ window event
@HostListener('window:popstate', ['$event'])
onPopState(event: PopStateEvent): void { ... }

// ✓ conditional drag listener — cannot use @HostListener, use markForCheck
private readonly onDragMove = (e: PointerEvent): void => {
  this.updateValue(e.clientX);
  this.cdr.markForCheck();
};
```

#### When to use ChangeDetectorRef.markForCheck()

Only when Angular-external async callbacks (e.g., `IntersectionObserver`, `setTimeout`, native DOM events that cannot use `@HostListener`) mutate local state. Prefer `@HostListener` or Observable/async pipe first.

### No Props Drilling

If an `@Input()` exists only to be forwarded to a child — remove it. The child injects the service directly.

This applies to `CurrentUserService` data and any other shared state — do not pass them down the component tree as inputs.

### Deprecated Practices
1. **Material UI**: No new Material imports anywhere. Existing Material usage will be replaced in phases (see design-system.md, section 9).
2. **Direct Material UI in Design System Primitives**: `shared/ui` form primitives must be implemented with native/custom markup and our theme variables. Material wrappers are transitional and must be removed.
3. **Sass @import**: The `@import` rule in SCSS is deprecated in favor of `@use` and `@forward`.
4. **Legacy CSS**: Custom styles that duplicate Material functionality or legacy theme styles should be avoided.
5. **CSS-based popover positioning**: Do not use `position: absolute` inside `position: relative` hosts for overlay patterns. Use CDK `CdkConnectedOverlay` instead.

#### Layer Hierarchy (FSD)

Code is strictly divided into layers with a one-directional dependency rule: upper layers may import from lower ones, never the reverse.

```
shared/    ← no domain knowledge; no imports from entities or features
entities/  ← domain objects and their UI; may import from shared only
features/  ← user scenarios and business logic; may import from entities and shared
```

**`shared/ui/`** — domain-agnostic design system primitives: buttons, inputs, layout components, typography, skeletons. Must contain no domain DTOs or business rules.

**`entities/{entity-name}/`** — UI representation of a single domain object (e.g. `release`, `prod`, `tune`, `picture`). Contains:
- `components/` — the card/row/block component for that entity
- `models/` — the entity's DTO and interfaces

Rules for entities:
- An entity component receives its DTO as `@Input()` and renders it — nothing more.
- Entity components MUST NOT trigger data loading or contain business logic.
- Entity components MAY import from `shared/` only.
- The DTO used by the component lives in the same `entities/{entity}/models/` folder.

If a component is used in more than one feature, it belongs in `entities/`, not inside any single feature.

**`features/{feature-name}/`** — one user scenario per directory. May import from `entities/` and `shared/`.

- Each feature must be located in its own directory within `src/app/features/`.
- Example of the `comments` feature structure:
  ```
  features/comments/
    components/      # Feature components
    services/        # Feature-specific services
    models/          # DTOs and interfaces
  ```

- **Naming and Storage Rules**:
    1. **Component Prefix**: All component selectors MUST use the `zx-` prefix (e.g., `zx-picture-card`, `zx-fp-new-tunes`). The `app-` prefix is forbidden.
    2. **Standalone Components**: All components MUST be standalone. Explicitly specify all required imports (modules, other components, pipes) in the `imports` array of the `@Component` decorator. Modules (except `AppModule` for registration) are prohibited.
    2. **DTOs**: All interfaces and DTOs must be stored in the `models/` folder within the corresponding module/feature. Do not mix type definitions with service or component code.
    3. **File Separation**: For each component, the template (HTML), styles (SCSS), and logic (TS) must reside in separate files. Using inline templates and styles within the `@Component` decorator is prohibited.
    4. **Services**: Shared services are stored in `app/shared/services/`, while feature-specific services are stored in `features/{feature-name}/services/`.
    5. **Translations**: All user-facing text must be implemented using `ngx-translate`. Translations must be added to `src/assets/i18n/` for three languages: English (`en.json`), Russian (`ru.json`), and Spanish (`es.json`). Hardcoding strings in templates or components is forbidden.
    6. **Component Wrapper**: If a component's template consists of a single wrapper element, that wrapper is unnecessary. Instead, apply the required styles and classes directly to the component's host element using the `:host` selector in SCSS and `@HostBinding('class.className')` in the TypeScript class.
