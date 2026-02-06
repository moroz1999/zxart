## Design System
The project follows a component-based approach for the design system. 

### Core Principles
1. **CSS Variables Only**: All colors must be specified exclusively through CSS variables.
2. **Color System**:
    - **Base Ramps**: Colors are organized into ramps: `--blue-dark-*`, `--red-dark-*`, `--yellow-dark-*`, `--neutral-dark-*` (and their light equivalents).
    - **Semantic Ramps**: Use semantic variables: `--primary-*`, `--secondary-*`, `--danger-*`, `--warning-*`.
    - **Indices**: Indices range from 50 to 950. In the dark theme, 50 is the darkest and 950 is the lightest. In the light theme, the mapping is inverted to maintain visual logic.
    - **Fixed Palette**: Adding new colors or shades to the global palette is strictly prohibited. Use existing variables.
3. **Spacing System**: All distances (margins, paddings, gaps) must be multiples of 4 and set using variables: `--space-4`, `--space-8`, `--space-12`, `--space-16`, etc.
4. **Shadows**: Standard shadow variants:
    - `--shadow-thin`: For subtle elevations (e.g., cards, small buttons).
    - `--zx-panel-shadow`: Specific shadow for `zx-panel` (varies by theme).
5. **Borders**: Standard border tokens for inputs and containers:
    - `--border-width-thin`: 1px (тонкий)
    - `--border-width-thick`: 2px (толстый)
    - `--border-secondary`: Default gray border (used in `flat` panels).
    - `--border-primary`: Blue primary border.
    - `--border-focused`: High-contrast border for focused states.
6. **No Hardcoded Palette in Components**: In component styles, it is forbidden to use base palette variables like `--blue-dark-500` directly. Instead, use semantic variables like `--text-color`, `--zx-button-bg`, `--primary-500`, etc. If a needed semantic variable is missing, define it in the theme.
7. **Themes**: The project supports Dark (class `.dark-mode`) and Light (class `.light-mode`) themes. Always use semantic variables to ensure correct theme switching.
8. **Semantic Usage**: All components must be used semantically and for their intended purpose.
9. **Material UI**: The project uses Angular Material as the primary UI library. All new components must use Material components. PrimeNG is considered legacy and must be replaced during refactoring.
10. **Button Design**: Buttons must use Angular Material directives (`mat-button`, `mat-flat-button`, etc.). They are globally styled to match the design system using CSS variables defined in `_zx-button.theme.scss`. Use the `color` attribute to switch between primary, accent, and warn states.
11. **UI Components Usage**: Only components from the `shared/ui` directory (design system) should be used for building user interfaces to ensure consistency across the application. Direct usage of Material components in features is discouraged if a design system equivalent exists.
12. **Layout Rules**: All layout (spacing, alignment, positioning) must be implemented exclusively using design system components (like `zx-stack`, `zx-panel`) or approved utility directives. Manual `style` attributes for margins, paddings, and other layout properties are strictly forbidden. If elements are part of a common layout, general layout rules (flex, grid) or `zx-stack` are preferred over individual margins. **Negative margins are PROHIBITED** — never use `calc(-1 * ...)` or any negative margin to break out of a parent's padding. Use `padding="none"` on the parent and apply padding to child elements individually.
    - **No wrapper elements**: If a component only wraps `<ng-content>`, use `:host` for all styling instead of adding a wrapper `<div>`. Apply classes via `@HostBinding('class')`. Wrapper elements are only justified when additional structural markup is needed beyond plain content projection.
13. **Loading States**: Every list or data-driven component MUST implement proper loading states:
    - **Initial load**: Display `zx-skeleton` component with appropriate variant (`comment`, `card`, `row`, `text`). Never show empty containers or spinners for initial page loads.
    - **Pagination/reload**: Lock interactive controls (pagination, filters) with visual feedback (opacity reduction, spinner overlay). Content should blur slightly to indicate loading without disappearing completely.
    - **Error states**: Display user-friendly error messages with retry options.
14. **Typography System**: Strictly limited to a set of directives and CSS variables.
    - **Allowed styles**: `heading-1`, `heading-2`, `heading-3`, `body`, `body-strong`, `caption`, `link`, `link-alt`.
    - **Angular directives**: `zxHeading1`, `zxHeading2`, `zxHeading3`, `zxBody`, `zxBodyStrong`, `zxCaption`, `zxLink`, `zxLinkAlt`.
    - **Prohibition**: Direct use of `--font-*` variables in components is prohibited. Use typography directives or variables from `_typography.theme.scss`. Custom variants or Display-styles are forbidden.

### Reusable Subcomponents
1. **Legacy Components**: Legacy components and their variables must not be modified.
2. **Subcomponents**: To maintain consistency and reduce code duplication, repeating UI patterns must be extracted into standalone templates or components.
3. **Shared UI Components**: All design system components are located in `ng-zxart/src/app/shared/ui/`.

#### Available Components:
- `zx-button`: Versatile button component with multiple sizes (xs, sm, md), colors, and square mode (removes horizontal padding, makes width equal to height).
  - **Colors and usage**:
    - `primary` - Main call-to-action. Use for the primary action on a page (Submit, Save, Confirm).
    - `secondary` - Secondary call-to-action. Use for important but not primary actions.
    - `outlined` - Transparent background with gray border. Use for secondary actions like filters, Cancel buttons, or actions that shouldn't draw too much attention.
    - `transparent` - No background or border. Use when many buttons appear in a row (pagination, letter navigation).
    - `danger` - Destructive actions only (Delete, Remove, Revoke).
- `zx-panel`: Universal layout container with configurable variant (elevated, flat), border radius (sm, md, lg, xl) and padding (none, sm, md, lg). Use `padding="none"` when child content needs to go edge-to-edge (e.g., tables).
- `zx-stack`: Flexbox-based layout container with configurable spacing (md, lg, xl) and direction (column, row).
- `zx-user`: Component for displaying user name with status icons (badges). Uses "icon name" layout.
- `zx-skeleton`: Loading placeholder component for lists and content. Variants:
  - `comment` - For comment lists (image + header + text lines)
  - `card` - For card grids (image + title + text)
  - `row` - For simple list rows
  - `text` - For text blocks
  - Props: `variant`, `count` (number of skeleton items), `animated` (shimmer effect, default true)
- `zx-table`: Table wrapper inside a flat panel. Props: `title` (optional heading above the table). Uses `padding="none"` on the panel so that table rows go edge-to-edge; the title gets its own internal padding.
- `zx-pagination`: Page navigation component with loading state support.
  - Props: `currentPage`, `pagesAmount`, `urlBase`, `loading` (shows spinner overlay and disables controls), `visibleAmount`
  - Event: `pageChange` - emits new page number on click

