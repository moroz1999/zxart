# Styles

## New styles code should be written in SCSS.

**CRITICAL: After any changes to SCSS or theme files, you MUST rebuild the Angular project using `composer run build` from the project root directory.**

- **No vendor prefixes**: Do not use `-webkit-`, `-moz-`, `-ms-` or any other vendor prefixes. Use only standard unprefixed properties.
- **Comments**: All comments in CSS/SCSS files must be written in English only.
- **Styles and Variables**:
    - NO hardcoded hex/rgb or raw spacing.
    - NO SCSS variables (deprecated). Use CSS `var()`.
    - All colors and sizes must be formatted as CSS variables.
    - Adding or modifying variables in `_base.theme.scss` is allowed ONLY after direct user permission.
    - Reuse of variables from `_legacy.theme.scss` (or any other files with the `_legacy` prefix) is prohibited.
    - Link color variables (`--link-color`, `--link-alt-color`) are considered legacy and must not be used in new components. Use `--primary-*`, `--zx-link-*` or `zxLinkAlt` directive instead.
    - Legacy components and their variables must not be touched. CSS variables serve as a link between legacy and Angular.
- **Architecture**:
    - **Base variables** (`--space-*`, `--font-*`, `--radius-*`, etc.) define the design system's common foundation. They are stored in `src/app/shared/theme/_base.theme.scss`.
    - **Typography**: Strictly limited to a set of directives and CSS variables.
        - Allowed styles: `heading-1`, `heading-2`, `heading-3`, `body`, `body-strong`, `caption`, `link`, `link-alt`.
        - Use Angular directives: `zxHeading1`, `zxHeading2`, `zxHeading3`, `zxBody`, `zxBodyStrong`, `zxCaption`, `zxLink`, `zxLinkAlt`.
        - Direct use of `--font-*` variables in components is prohibited. Use typography directives or variables from `_typography.theme.scss`.
        - Custom variants or Display-styles are forbidden.
    - **Theme colors** define the color palette for different modes.
        - `_dark.theme.scss`: Dark theme colors (activated by `.dark-mode` class).
        - `_light.theme.scss`: Light theme colors (activated by `.light-mode` class) and inverted semantic mappings.
    - **Component variables** (e.g., `--zx-button-bg`, `--zx-user-badge-color`) must be defined in separate files (one per component, e.g., `_zx-button.theme.scss`). These variables should use semantic variables (`--primary-*`, `--secondary-*`, etc.) or base variables.
    - **Theme files contain ONLY variables** (`:root` and `.dark-mode` blocks). CSS rules (selectors with properties) must be placed in component SCSS files, not in theme files. Theme files exist solely to define reusable CSS custom properties that can be shared between Angular components and legacy code.
    - **Usage**: Components (Angular or Legacy) MUST use component variables ONLY. Direct use of base palette or base variables in components is FORBIDDEN. Component-specific CSS variables MUST NOT be used outside of the component they belong to.
    - **Layout**: If elements are part of a common layout, general layout rules (flexbox, grid, or layout components like `zx-stack`) are strictly preferred over individual margins. Use `zx-stack` to manage spacing between related elements.
    - **Negative margins are PROHIBITED**. Never use `calc(-1 * ...)` or any negative margin trick to break out of a parent's padding. For edge-to-edge content (e.g., tables) inside a `zx-panel`, use `[contentBleed]="true"` — this removes body padding while keeping the title consistently padded. Use `padding="none"` only when the entire panel genuinely needs zero padding (e.g., image cards with no title).
    - **No wrapper elements**: Do not add a wrapper `<div>` in a component template just to hold classes. Use `:host` with `@HostBinding('class')` instead. Wrappers are only justified when additional structural markup is needed beyond `<ng-content>`.
    - **Enforcement**: This is a mandatory rule. Always check if you are using base variables directly and replace them with component variables. Also, ensure you are not leaking or using variables from other components.
    - If a component-level variable is missing, create it in a component-specific theme file.
    - If a property or variable is no longer needed or used only in one place and has a "default" value (transparent, 0, etc.) to hide it, REMOVE it entirely along with its declaration. Do not use default/transparent values to "disable" unused styles.
    - NO `em` or `rem` in components and theme. Use font-size variables only.
    - Font sizes must be rounded to whole pixels.
    - Spacing (`--space-*`) must be multiples of 4px.
    - If a specific color/size is missing in theme, use the closest existing one.
    - All theme management is centralized in component-specific theme files, which are imported into the main theme.

## Material Component Theming

Angular Material is globally configured with a **dark theme** (`m2-define-dark-theme` in `styles.scss`). This means all Material MDC component tokens (text colors, backgrounds, etc.) default to light-on-dark values (e.g., white text).

When using Material components, you **must override** the relevant `--mdc-*` and `--mat-*` CSS custom properties to use our semantic variables. Otherwise, text will appear white in light mode.

Example overrides for common Material tokens:
- Dialog: `--mdc-dialog-container-color`, `--mdc-dialog-subhead-color`, `--mdc-dialog-supporting-text-color`
- Checkbox labels: `--mat-checkbox-label-text-color`

These overrides are placed in component-specific theme files (e.g., `_zx-dialog.theme.scss`) scoped to the component's `panelClass` or host selector.

## Legacy LESS
- Replace all hardcoded legacy property values with CSS variables. Add missing variables according to same rules as for SCSS. 