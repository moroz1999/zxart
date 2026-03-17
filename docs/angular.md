### Angular Integration into Legacy (Smarty)

The integration of Angular components into existing legacy Smarty templates is implemented using Custom Elements (Web Components).

#### Core Principles
1. **Standalone Components**: All Angular components MUST be standalone. `AppModule` is used only for bootstrapping and registering custom elements.
2. **Custom Elements**: Angular components are registered in `AppModule` as custom elements with a `zx-` prefix. This allows them to be used like standard HTML tags within `.tpl` files.
3. **Data Passing**:
    - **Attributes**: Element IDs and simple settings are passed via tag attributes (e.g., `element-id="{$element->id}"`). These attributes are received in Angular components using the `@Input()` decorator.

#### Routing and Navigation
Currently, the legacy part of the system is responsible for routing. Clicking on links results in a full browser page reload. Angular components are initialized "on the fly" during page load if the corresponding tag is present in the rendered HTML.

#### Build and Verification
After making any changes to the Angular part of the project (`ng-zxart`), including styles (SCSS) and theme files, you must:
1. Perform a project build: `composer run build` from the project root.
2. Ensure the build completes without errors.
3. Verify the result in a browser.

**Note: Any change to angular files requires a mandatory Angular build to reflect changes in the application.**

#### Example of Comments Integration
To integrate the new comments list, the `<app-comments-list>` tag is used in the relevant detailed templates (e.g., `zxProd.details.tpl`):

```html
<app-comments-list element-id="{$element->id}"></app-comments-list>
```

The component independently requests data from the backend using the provided `element-id` via `CommentsService`. The old comments mechanism using `{include file=$theme->template('component.comments.tpl')}` in public templates is no longer used.

### Architecture and Code Structure

#### Feature Sliced Design (FSD)
All new functionality in Angular must follow Feature Sliced Design principles and the [Design System](design-system.md).

**CRITICAL**:
- **No new Material imports.** Material UI is being phased out (see [design-system.md](design-system.md) for the full plan).
- **ONLY standalone components** are allowed.
- **Angular CDK** (`@angular/cdk`) is the approved foundation for overlays, drag-and-drop, and accessibility.
- Use design system components and theme variables. Custom CSS is forbidden without direct instruction.
- Components must be used semantically.

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
