### Angular Integration into Legacy (Smarty)

The integration of Angular components into existing legacy Smarty templates is implemented using Custom Elements (Web Components).

#### Core Principles
1. **Standalone Components**: All Angular components MUST be standalone. `AppModule` is used only for bootstrapping and registering custom elements.
2. **Custom Elements**: Angular components are registered in `AppModule` as custom elements with a `zx-` prefix. This allows them to be used like standard HTML tags within `.tpl` files.
    - If a component is registered as a custom element for legacy templates and also reused inside Angular templates, provide a separate Angular-only selector for internal usage. Do not nest the registered custom element tag inside Angular templates, because the browser custom element lifecycle can conflict with Angular input binding.
3. **Data Passing**:
    - **Attributes**: Element IDs and simple settings are passed via tag attributes (e.g., `element-id="{$element->id}"`). These attributes are received in Angular components using the `@Input()` decorator.

#### Routing and Navigation
Currently, the legacy part of the system is responsible for routing. Clicking on links results in a full browser page reload. Angular components are initialized "on the fly" during page load if the corresponding tag is present in the rendered HTML.

#### Pagination and URL

Components with pagination **must** reflect the current page in the URL and restore it on load.

**Rules:**
- On init: parse the page number from `window.location.pathname` using the pattern `/page:(\d+)/`.
- On page change: call `window.history.pushState(null, '', newPath)` — no full reload.
- `urlBase` (path without the `page:N` segment) must be passed to `<zx-pagination [urlBase]="urlBase">` so page links have correct `href` attributes (required for right-click → open in new tab).
- Page 1 must produce a clean URL (no `page:1` segment).

**Reference implementation:** `CommentsPageComponent` (`features/comments/components/comments-page/`) and `BrowserBaseComponent` (`shared/browser-base.component.ts`).

#### Build and Verification
After making any changes to the Angular part of the project (`ng-zxart`), including styles (SCSS) and theme files, you must:
1. Perform a project build: `composer run build` from the project root.
2. Ensure the build completes without errors.
3. Verify the result in a browser.

**Note: Any change to angular files requires a mandatory Angular build to reflect changes in the application.**

### Architecture and Code Structure

#### Feature Sliced Design (FSD)
All new functionality in Angular must follow Feature Sliced Design principles and the [Design System](design-system.md).

**CRITICAL**:
- **No new Material imports.** Material UI is being phased out (see [design-system.md](design-system.md) for the full plan).
- **ONLY standalone components** are allowed.
- **Angular CDK** (`@angular/cdk`) is the approved foundation for overlays, drag-and-drop, and accessibility.
- Use design system components and theme variables. Custom CSS is forbidden without direct instruction.
- Components must be used semantically.

### Documentation Scope

General Angular documentation must contain domain-neutral architecture rules only. Entity-specific behavior, feature-specific REST contracts, and business rules belong in `docs/domain/*.md` or a narrowly scoped feature document.

When adding a reusable Angular pattern, document the generic rule here and place concrete entity examples in the relevant domain document.

### Static Backend Section Links

All static section URLs (comments, support, search, registration, password reminder, profile, playlists, home, catalogue base URLs) for the current language are fetched via a single `GET /backend-links/?lang={code}` call. Use `BackendLinksService.links$` (`features/header/services/backend-links.service.ts`) to access them. The service caches results in LocalStorage per language code and emits once via a BehaviorSubject — all consumers subscribe and wait for the single emission.

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

This applies to `BackendLinksService` URLs, `CurrentUserService` data, and any other shared state — do not pass them down the component tree as inputs.

### Deprecated Practices
1. **Material UI**: No new Material imports anywhere. Existing Material usage will be replaced in phases (see design-system.md, section 9).
2. **Direct Material UI in Design System Primitives**: `shared/ui` form primitives must be implemented with native/custom markup and our theme variables. Material wrappers are transitional and must be removed.
3. **Sass @import**: The `@import` rule in SCSS is deprecated in favor of `@use` and `@forward`.
4. **Legacy CSS**: Custom styles that duplicate Material functionality or legacy theme styles should be avoided.
5. **CSS-based popover positioning**: Do not use `position: absolute` inside `position: relative` hosts for overlay patterns. Use CDK `CdkConnectedOverlay` instead.

- Code is divided into layers, such as `features`, `entities`, and `shared`.
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
