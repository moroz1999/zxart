# Design System

The project follows a component-based approach for the design system.

## Core Principles

1. **CSS Variables Only**: All colors must be specified exclusively through CSS variables.
2. **Color System**: See [Colors](design-system/colors.md) for palette structure, semantic variables, and theming rules.
3. **Spacing System**: All distances (margins, paddings, gaps) must be multiples of 4 and set using variables: `--space-4`, `--space-8`, `--space-12`, `--space-16`, etc.
4. **Shadows**: See [Shadows](design-system/shadows.md) for available tokens and rules.
5. **Borders**: See [Borders](design-system/borders.md) for available tokens and rules.
6. **No Hardcoded Palette in Components**: In component styles, it is forbidden to use base palette variables like `--blue-dark-500` directly. Use semantic variables like `--text-color`, `--zx-button-bg`, `--primary-500`, etc. If a needed semantic variable is missing, define it in the theme.
7. **Themes**: The project supports Dark (class `.dark-mode`) and Light (class `.light-mode`) themes. Always use semantic variables to ensure correct theme switching.
8. **Semantic Usage**: All components must be used semantically and for their intended purpose.
9. **Material UI — Phaseout Plan**: Material UI is being phased out entirely. No new Material imports are allowed anywhere.
    - **Phase 1 (current)**: Remove Material from `shared/ui` design system primitives. Use native/custom markup with theme variables.
    - **Phase 2**: Replace `MatDialog` with a custom overlay-based dialog using CDK.
    - **Phase 3**: Replace `MatIcon` with SVG icon system (`angular-svg-icon`, already partially done).
    - **Phase 4**: Replace remaining Material components (autocomplete, tree, chips, checkbox, etc.) with custom implementations.
    - **CDK is approved**: Angular CDK (`@angular/cdk`) is the approved foundation for overlays, drag-and-drop, accessibility, and positioning. Use `CdkConnectedOverlay` for popovers/dropdowns instead of CSS absolute positioning.
    - **PrimeNG** is legacy and must be replaced during refactoring.
10. **Button Design**: Buttons use the `zx-button` component from the design system. They are styled using CSS variables defined in `_zx-button.theme.scss`.
11. **UI Components Usage**: Only components from the `shared/ui` directory (design system) should be used for building user interfaces to ensure consistency across the application. Direct usage of Material components in features is discouraged if a design system equivalent exists.
12. **Layout Rules**: All layout (spacing, alignment, positioning) must be implemented exclusively using design system components (like `zx-stack`, `zx-panel`) or approved utility directives. Manual `style` attributes for margins, paddings, and other layout properties are strictly forbidden. If elements are part of a common layout, general layout rules (flex, grid) or `zx-stack` are preferred over individual margins. **Negative margins are PROHIBITED** — never use `calc(-1 * ...)` or any negative margin to break out of a parent's padding. For edge-to-edge content (e.g., tables) inside `zx-panel`, use `[contentBleed]="true"` instead. Use `padding="none"` only when the entire panel needs zero padding (e.g., image cards with no title).
    - **No wrapper elements**: If a component only wraps `<ng-content>`, use `:host` for all styling instead of adding a wrapper `<div>`. Apply classes via `@HostBinding('class')`. Wrapper elements are only justified when additional structural markup is needed beyond plain content projection.
13. **Loading States**: Every list or data-driven component MUST implement proper loading states:
    - **Initial load**: Display `zx-skeleton` component with appropriate variant (`comment`, `card`, `row`, `text`). Never show empty containers or spinners for initial page loads.
    - **Pagination/reload**: Lock interactive controls (pagination, filters) with visual feedback (opacity reduction, spinner overlay). Content should blur slightly to indicate loading without disappearing completely.
    - **Error states**: Display user-friendly error messages with retry options.
14. **Typography System**: See [Typography](design-system/typography.md) for all directives, usage rules, and how to choose between similar styles.
15. **Overlays and Popovers**: Use CDK `CdkConnectedOverlay` for all popover/dropdown/tooltip overlays. CSS `position: absolute` within `position: relative` hosts is forbidden for overlay patterns — it stretches parent layout. CDK handles positioning, scroll-aware repositioning, and backdrop closing automatically.

## Reusable Subcomponents

1. **Legacy Components**: Legacy components and their variables must not be modified.
2. **Subcomponents**: To maintain consistency and reduce code duplication, repeating UI patterns must be extracted into standalone templates or components.
3. **Shared UI Components**: All design system components are located in `ng-zxart/src/app/shared/ui/`.

## Components

All design system components are in `ng-zxart/src/app/shared/ui/`.

- [zx-panel](design-system/zx-panel.md) — universal layout container
- [zx-stack](design-system/zx-stack.md) — flexbox layout container
- [zx-table](design-system/zx-table.md) — table wrapper with edge-to-edge rows
- [zx-button](design-system/zx-button.md) — button with colors and sizes
- [zx-button-controls](design-system/zx-button-controls.md) — wrapper for button groups
- [zx-pagination](design-system/zx-pagination.md) — page navigation
- [zx-filter-picker](design-system/zx-filter-picker.md) — popover filter with checkboxes
- [zx-user](design-system/zx-user.md) — user name with status badges
- [zx-skeleton](design-system/zx-skeleton.md) — loading placeholder
- [zx-spinner](design-system/zx-spinner.md) — compact loading spinner
