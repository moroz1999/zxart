### Angular Integration into Legacy (Smarty)

The integration of Angular components into existing legacy Smarty templates is implemented using Custom Elements (Web Components).

#### Core Principles
1. **Standalone Components**: All Angular components MUST be standalone. `AppModule` is used only for bootstrapping and registering custom elements.
2. **Custom Elements**: Angular components are registered in `AppModule` as custom elements with an `app-` prefix. This allows them to be used like standard HTML tags within `.tpl` files.
3. **Data Passing**:
    - **Attributes**: Element IDs and simple settings are passed via tag attributes (e.g., `element-id="{$element->id}"`). These attributes are received in Angular components using the `@Input()` decorator.

#### Routing and Navigation
Currently, the legacy part of the system is responsible for routing. Clicking on links results in a full browser page reload. Angular components are initialized "on the fly" during page load if the corresponding tag is present in the rendered HTML.

#### Build and Verification
After making any changes to the Angular part of the project (`ng-zxart`), including styles (SCSS) and theme files, you must:
1. Perform a project build: `npm run build` (inside the `ng-zxart` directory).
2. Ensure the build completes without errors.
3. Verify the result in a browser.

**Note: Any change to SCSS files requires a mandatory Angular build to reflect changes in the application.**

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
- All new components must use **Material UI**.
- **ONLY standalone components** are allowed.
- **PrimeNG** is legacy and must be replaced by Material.
- Custom CSS is forbidden without direct instruction. Use Material components and our theme.
- Components must be used semantically.

### Deprecated Practices
1. **PrimeNG**: The use of PrimeNG is deprecated. All new components MUST use Material UI. Existing PrimeNG components should be replaced during refactoring.
2. **Sass @import**: The `@import` rule in SCSS is deprecated in favor of `@use` and `@forward`.
3. **Legacy CSS**: Custom styles that duplicate Material functionality or legacy theme styles should be avoided.
4. **Direct CSS Overrides**: Avoid deep CSS overrides of Material components unless absolutely necessary; use Material themes and variables instead.

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
    1. **Standalone Components**: All components MUST be standalone. Explicitly specify all required imports (modules, other components, pipes) in the `imports` array of the `@Component` decorator. Modules (except `AppModule` for registration) are prohibited.
    2. **DTOs**: All interfaces and DTOs must be stored in the `models/` folder within the corresponding module/feature. Do not mix type definitions with service or component code.
    3. **File Separation**: For each component, the template (HTML), styles (SCSS), and logic (TS) must reside in separate files. Using inline templates and styles within the `@Component` decorator is prohibited.
    4. **Services**: Shared services are stored in `app/shared/services/`, while feature-specific services are stored in `features/{feature-name}/services/`.
    5. **Translations**: All user-facing text must be implemented using `ngx-translate`. Translations must be added to `src/assets/i18n/` for three languages: English (`en.json`), Russian (`ru.json`), and Spanish (`es.json`). Hardcoding strings in templates or components is forbidden.